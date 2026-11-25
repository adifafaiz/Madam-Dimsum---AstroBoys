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

class DapurController extends Controller
{
    /**
     * Memastikan hanya user ber-role Dapur yang bisa mengakses modul ini (FR-02)
     */
    private function checkDapurAccess()
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role) !== 'dapur') {
            abort(403);
        }
    }

    /**
     * Frame 1: Antrian Pesanan (FR-05, FR-06, FR-07, BR-05)
     * Menampilkan pesanan dalam 3 status: baru, diproses, siap
     */
    public function antrian(Request $request)
    {
        $this->checkDapurAccess();

        // Ambil pesanan aktif untuk dapur: baru, diproses, siap
        $pesananBaru = Order::with('items.product')
            ->where('status', 'baru')
            ->orderBy('waktu', 'asc')
            ->get();

        $pesananDiproses = Order::with('items.product')
            ->where('status', 'diproses')
            ->orderBy('waktu', 'asc')
            ->get();

        $pesananSiap = Order::with('items.product')
            ->where('status', 'siap')
            ->orderBy('waktu', 'asc')
            ->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'baru' => $pesananBaru,
                'diproses' => $pesananDiproses,
                'siap' => $pesananSiap,
            ]);
        }

        return view('dapur.antrian', compact('pesananBaru', 'pesananDiproses', 'pesananSiap'));
    }

    /**
     * Update status pesanan di dapur (BR-05: baru -> diproses -> siap -> selesai)
     */
    public function updateStatusPesanan(Request $request, Order $order)
    {
        $this->checkDapurAccess();

        $validated = $request->validate([
            'status' => 'required|in:baru,diproses,siap,selesai',
        ]);

        $statusLama = $order->status;
        $statusBaru = $validated['status'];

        $order->status = $statusBaru;
        $order->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'update_status_pesanan',
            'tabel' => 'orders',
            'data_lama' => ['kode' => $order->kode, 'status' => $statusLama],
            'data_baru' => ['kode' => $order->kode, 'status' => $statusBaru],
            'waktu' => Carbon::now(),
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Pesanan {$order->kode} berhasil diubah menjadi " . ucfirst($statusBaru),
                'order' => $order,
            ]);
        }

        return redirect()->route('dapur.antrian')->with('success', "Status pesanan {$order->kode} berhasil diperbarui.");
    }

    /**
     * Frame 2: Tampilan Catat Produksi (FR-08, FR-09)
     */
    public function catatProduksi(Request $request)
    {
        $this->checkDapurAccess();

        $products = Product::where('is_active', true)->orderBy('nama')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'products' => $products,
            ]);
        }

        return view('dapur.catat-produksi', compact('products'));
    }

    /**
     * Frame 2: Simpan Hasil Produksi (FR-08, BR-01, BR-02)
     * Menambah stok produk dan mencatat ke stock_movements (jenis: produksi)
     */
    public function storeProduksi(Request $request)
    {
        $this->checkDapurAccess();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'sesi' => 'required|in:pagi,sore,Pagi,Sore',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:255',
        ], [
            'tanggal.required' => 'Tanggal produksi wajib diisi.',
            'sesi.required' => 'Sesi produksi (Pagi / Sore) wajib dipilih.',
            'items.required' => 'Minimal 1 produk harus dimasukkan.',
            'items.*.qty.min' => 'Jumlah produksi minimal 1 porsi.',
        ]);

        $sesi = strtolower($validated['sesi']);
        $updatedProducts = [];

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $oldStok = $product->stok;
                $newStok = $oldStok + $item['qty'];

                $product->stok = $newStok;
                $product->save();

                // Catat ke stock_movements (jenis: produksi)
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'tanggal' => Carbon::parse($validated['tanggal']),
                    'jenis' => 'produksi',
                    'qty' => $item['qty'],
                    'saldo_setelah' => $newStok,
                    'sesi' => $sesi,
                    'keterangan' => $validated['catatan'] ?: "Produksi sesi {$sesi}",
                ]);

                $updatedProducts[] = [
                    'nama' => $product->nama,
                    'old_stok' => $oldStok,
                    'new_stok' => $newStok,
                    'status_stok' => $product->status_stok,
                ];
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'catat_produksi',
                'tabel' => 'stock_movements',
                'data_lama' => null,
                'data_baru' => [
                    'sesi' => $sesi,
                    'catatan' => $validated['catatan'] ?? null,
                    'items' => $updatedProducts,
                ],
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produksi berhasil dicatat dan stok telah diperbarui!',
                    'data' => $updatedProducts,
                ]);
            }

            return redirect()->route('dapur.stok')->with('success', 'Hasil produksi berhasil dicatat dan stok telah diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencatat produksi: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal mencatat produksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Frame 3: Halaman Stok Produk Dapur (FR-10, BR-03, BR-04)
     */
    public function stok(Request $request)
    {
        $this->checkDapurAccess();

        $query = Product::where('is_active', true);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('nama', 'asc')->get();

        // Filter checkbox: Hanya tampilkan yang perlu diproduksi atau sudah habis
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

        return view('dapur.stok', compact('products'));
    }

    /**
     * Frame 4: Riwayat Produksi (FR-11)
     */
    public function riwayatProduksi(Request $request)
    {
        $this->checkDapurAccess();

        $query = StockMovement::with(['product', 'user'])
            ->where('jenis', 'produksi')
            ->orderBy('tanggal', 'desc');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $riwayat = $query->get();
        $totalPorsi = $riwayat->sum('qty');

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'riwayat' => $riwayat,
                'total_porsi' => $totalPorsi,
            ]);
        }

        return view('dapur.riwayat-produksi', compact('riwayat', 'totalPorsi'));
    }
}

