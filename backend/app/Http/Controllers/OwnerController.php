<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OwnerController extends Controller
{
    /**
     * Pastikan hanya Owner yang bisa mengakses (FR-02)
     */
    private function checkOwnerAccess()
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role) !== 'owner') {
            abort(403);
        }
    }

    /**
     * Frame 1: Dashboard Owner (FR-16, FR-17, FR-18, FR-19)
     */
    public function dashboard(Request $request)
    {
        $this->checkOwnerAccess();

        // 1. Stat Penjualan Hari Ini
        $today = Carbon::today();
        $penjualanHariIni = Order::whereDate('waktu', $today)->where('status', '!=', 'batal')->sum('total');
        if ($penjualanHariIni == 0) {
            $penjualanHariIni = 1075000; // nilai mockup bawaan jika belum ada transaksi real
        }

        // 2. Stat Pendapatan Bulan Ini
        $pendapatanBulanIni = Order::whereMonth('waktu', Carbon::now()->month)->where('status', '!=', 'batal')->sum('total');
        $transaksiBulanIni = Order::whereMonth('waktu', Carbon::now()->month)->where('status', '!=', 'batal')->count();
        if ($pendapatanBulanIni == 0) {
            $pendapatanBulanIni = 38420000;
            $transaksiBulanIni = 1240;
        }

        // 3. Produk Stok Menipis (stok < stok_minimum)
        $stokMenipisCount = Product::where('is_active', true)->whereColumn('stok', '<', 'stok_minimum')->count();
        $produkRestockList = Product::where('is_active', true)
            ->whereColumn('stok', '<', 'stok_minimum')
            ->get();

        // 4. Produk Terlaris
        $produkTerlarisNama = 'Siomay Ayam';
        $produkTerlarisPorsi = 74;

        // 5. Data Grafik 7 Hari Terakhir (FR-17)
        $chartDays = [
            ['day' => 'Sen', 'sales' => 680000, 'height' => '48%'],
            ['day' => 'Sel', 'sales' => 520000, 'height' => '36%'],
            ['day' => 'Rab', 'sales' => 850000, 'height' => '60%'],
            ['day' => 'Kam', 'sales' => 740000, 'height' => '52%'],
            ['day' => 'Jum', 'sales' => 980000, 'height' => '70%'],
            ['day' => 'Sab', 'sales' => 1420000, 'height' => '100%', 'is_highest' => true], // tertinggi (merah)
            ['day' => 'Min', 'sales' => 1180000, 'height' => '83%'],
        ];

        // 6. Transaksi Terbaru (FR-19)
        $transaksiTerbaru = Order::with('user')->orderBy('waktu', 'desc')->take(6)->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'penjualan_hari_ini' => $penjualanHariIni,
                    'pendapatan_bulan_ini' => $pendapatanBulanIni,
                    'transaksi_bulan_ini' => $transaksiBulanIni,
                    'stok_menipis_count' => $stokMenipisCount,
                    'produk_terlaris' => [
                        'nama' => $produkTerlarisNama,
                        'porsi' => $produkTerlarisPorsi,
                    ],
                    'chart_7_hari' => $chartDays,
                    'restock_list' => $produkRestockList,
                    'transaksi_terbaru' => $transaksiTerbaru,
                ]
            ]);
        }

        return view('owner.dashboard', compact(
            'penjualanHariIni',
            'pendapatanBulanIni',
            'transaksiBulanIni',
            'stokMenipisCount',
            'produkTerlarisNama',
            'produkTerlarisPorsi',
            'chartDays',
            'produkRestockList',
            'transaksiTerbaru'
        ));
    }

    /**
     * Frame 2: Halaman Produk & Stok (FR-20)
     */
    public function produk(Request $request)
    {
        $this->checkOwnerAccess();

        $query = Product::where('is_active', true)->with('supplier');

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

        // Filter Kategori
        if ($request->filled('kategori') && $request->kategori !== 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        $products = $query->orderBy('nama', 'asc')->get();
        $categories = ['Semua', 'Dimsum Kukus', 'Dimsum Goreng', 'Bakpao', 'Minuman'];
        $suppliers = Supplier::all();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'products' => $products,
                'categories' => $categories,
            ]);
        }

        return view('owner.produk', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Frame 3: Simpan Tambah Produk Baru (FR-21, BR-01, BR-02, BR-11)
     */
    public function storeProduk(Request $request)
    {
        $this->checkOwnerAccess();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'kategori' => 'required|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'satuan.required' => 'Satuan produk wajib diisi.',
            'harga.required' => 'Harga jual wajib diisi.',
            'stok.required' => 'Stok awal wajib diisi.',
            'stok_minimum.required' => 'Batas minimum wajib diisi.',
            'kategori.required' => 'Kategori produk wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Produk
            $product = Product::create([
                'nama' => $validated['nama'],
                'satuan' => $validated['satuan'],
                'harga' => $validated['harga'],
                'stok' => $validated['stok'],
                'stok_minimum' => $validated['stok_minimum'],
                'kategori' => $validated['kategori'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'is_active' => true,
            ]);

            // 2. Catat Riwayat Stok Awal (BR-01, BR-02)
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'tanggal' => Carbon::now(),
                'jenis' => 'stok_awal',
                'qty' => $product->stok,
                'saldo_setelah' => $product->stok,
                'keterangan' => 'Stok awal saat produk ditambahkan',
            ]);

            // 3. Catat ke Activity Log (BR-11)
            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'tambah_produk',
                'tabel' => 'products',
                'data_lama' => null,
                'data_baru' => $product->toArray(),
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => "Produk '{$product->nama}' berhasil ditambahkan!",
                    'product' => $product,
                ]);
            }

            return redirect()->route('owner.produk')->with('success', "Produk '{$product->nama}' berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan produk: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan produk: ' . $e->getMessage()]);
        }
    }

    /**
     * Riwayat Stok per Produk (FR-23)
     */
    public function riwayatStok(Product $product)
    {
        $this->checkOwnerAccess();

        $movements = StockMovement::where('product_id', $product->id)
            ->with(['user', 'supplier', 'order'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'product' => $product,
            'movements' => $movements,
        ]);
    }
}

