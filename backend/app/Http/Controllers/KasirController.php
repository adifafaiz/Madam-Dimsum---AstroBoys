<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    /**
     * Memastikan hanya user ber-role Kasir yang bisa mengakses modul ini (FR-02)
     */
    private function checkKasirAccess()
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role) !== 'kasir') {
            abort(403);
        }
    }

    /**
     * Frame 1 & 2: Halaman POS Pencatatan Transaksi (FR-12, FR-13, BR-05, BR-07)
     */
    public function transaksi(Request $request)
    {
        $this->checkKasirAccess();

        $query = Product::where('is_active', true);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kategori') && $request->kategori !== 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        $products = $query->orderBy('nama', 'asc')->get();
        $categories = ['Semua', 'Dimsum Kukus', 'Dimsum Goreng', 'Bakpao'];

        // Cek jika ada flash data transaksi sukses (Frame 2 Modal)
        $transaksiSukses = session('transaksi_sukses');

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'products' => $products,
                'categories' => $categories,
            ]);
        }

        return view('kasir.transaksi', compact('products', 'categories', 'transaksiSukses'));
    }

    /**
     * Simpan Transaksi Penjualan Kasir (FR-12, FR-13, BR-01, BR-02, BR-05, BR-07)
     */
    public function storeTransaksi(Request $request)
    {
        $this->checkKasirAccess();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'dibayar' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Keranjang pesanan masih kosong.',
            'items.*.qty.min' => 'Jumlah pesanan minimal 1.',
            'dibayar.required' => 'Jumlah uang diterima wajib diisi.',
        ]);

        DB::beginTransaction();
        try {
            $totalHarga = 0;
            $orderItemsData = [];
            $stockMovementsData = [];

            // 1. Validasi ketersediaan stok untuk semua item (BR-07)
            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->find($itemData['product_id']);

                if ($product->stok < $itemData['qty']) {
                    DB::rollBack();
                    $msg = "Stok untuk '{$product->nama}' tidak mencukupi (Tersedia: {$product->stok}, Diminta: {$itemData['qty']}).";
                    if ($request->wantsJson() || $request->is('api/*')) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return back()->withInput()->withErrors(['items' => $msg]);
                }

                $subtotal = $product->harga * $itemData['qty'];
                $totalHarga += $subtotal;

                $orderItemsData[] = [
                    'product' => $product,
                    'qty' => $itemData['qty'],
                    'harga_satuan' => $product->harga,
                    'subtotal' => $subtotal,
                ];
            }

            // 2. Validasi jumlah pembayaran (FR-13)
            $dibayar = (float) $validated['dibayar'];
            if ($dibayar < $totalHarga) {
                DB::rollBack();
                $kurang = number_format($totalHarga - $dibayar, 0, ',', '.');
                $msg = "Uang yang diterima kurang Rp {$kurang}.";
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withInput()->withErrors(['dibayar' => $msg]);
            }

            $kembalian = $dibayar - $totalHarga;

            // 3. Generate Kode Transaksi Unik (contoh: TRX-0065, TRX-0066, dst)
            $lastOrder = Order::orderBy('id', 'desc')->first();
            $nextNum = $lastOrder ? ($lastOrder->id + 60) : 60;
            $kodeOrder = 'TRX-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Buat record Order baru dengan status 'baru' agar masuk ke antrian dapur (BR-05)
            $order = Order::create([
                'kode' => $kodeOrder,
                'waktu' => Carbon::now(),
                'user_id' => Auth::id(),
                'total' => $totalHarga,
                'dibayar' => $dibayar,
                'kembalian' => $kembalian,
                'status' => 'baru',
            ]);

            // 4. Buat OrderItem & Potong Stok + Catat StockMovement jenis 'terjual' (BR-01, BR-02)
            foreach ($orderItemsData as $oi) {
                $product = $oi['product'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $oi['qty'],
                    'harga_satuan' => $oi['harga_satuan'],
                    'subtotal' => $oi['subtotal'],
                ]);

                // Kurangi stok
                $newStok = $product->stok - $oi['qty'];
                $product->stok = $newStok;
                $product->save();

                // Catat ke stock_movements
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'tanggal' => Carbon::now(),
                    'jenis' => 'terjual',
                    'qty' => -$oi['qty'], // bernilai negatif karena mengurangi stok
                    'saldo_setelah' => $newStok,
                    'keterangan' => "Penjualan pesanan {$order->kode}",
                ]);
            }

            // Log aktivitas
            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'transaksi_penjualan',
                'tabel' => 'orders',
                'data_lama' => null,
                'data_baru' => [
                    'kode' => $order->kode,
                    'total' => $order->total,
                    'dibayar' => $order->dibayar,
                    'kembalian' => $order->kembalian,
                ],
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            $transaksiSuksesData = [
                'id' => $order->id,
                'kode' => $order->kode,
                'waktu' => $order->waktu->format('d M Y, H:i'),
                'total' => $order->total,
                'dibayar' => $order->dibayar,
                'kembalian' => $order->kembalian,
            ];

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan!',
                    'data' => $transaksiSuksesData,
                ]);
            }

            return redirect()->route('kasir.transaksi')->with('transaksi_sukses', $transaksiSuksesData);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal memproses transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Frame 3: Halaman Cek Stok Kasir (FR-10, BR-03)
     */
    public function cekStok(Request $request)
    {
        $this->checkKasirAccess();

        $query = Product::where('is_active', true);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('nama', 'asc')->get();

        if ($request->boolean('hanya_perlu_produksi')) {
            $products = $products->filter(function ($item) {
                return $item->stok < $item->stok_minimum;
            })->values();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'products' => $products,
            ]);
        }

        return view('kasir.cek-stok', compact('products'));
    }

    /**
     * Frame 4: Riwayat Transaksi Saya (Kasir Ini) (FR-15)
     */
    public function riwayat(Request $request)
    {
        $this->checkKasirAccess();

        // Hanya transaksi kasir yang sedang login
        $query = Order::with(['items.product', 'user'])
            ->where('user_id', Auth::id())
            ->orderBy('waktu', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('waktu', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhereHas('items.product', function ($pq) use ($search) {
                      $pq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->get();

        // Detail transaksi yang sedang aktif dipilih di panel kanan
        $selectedKode = $request->get('selected');
        $selectedOrder = $selectedKode ? $orders->firstWhere('kode', $selectedKode) : $orders->first();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'orders' => $orders,
                'selected_order' => $selectedOrder,
            ]);
        }

        return view('kasir.riwayat', compact('orders', 'selectedOrder'));
    }

    /**
     * Cetak Struk Penjualan & Cetak Ulang (FR-14, FR-15, BR-08, BR-09)
     */
    public function cetakStruk(Order $order, Request $request)
    {
        $this->checkKasirAccess();

        $order->load(['items.product', 'user']);
        $isCetakUlang = $request->boolean('ulang', false);

        return view('kasir.struk', compact('order', 'isCetakUlang'));
    }
}

