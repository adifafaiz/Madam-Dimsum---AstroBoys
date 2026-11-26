<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\BackupService;
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

        $hariIni = Carbon::today();
        $kemarin = Carbon::yesterday();

        // Penjualan hari ini vs kemarin (transaksi batal tidak dihitung)
        $penjualanHariIni = $this->totalPenjualan($hariIni->copy()->startOfDay(), $hariIni->copy()->endOfDay());
        $penjualanKemarin = $this->totalPenjualan($kemarin->copy()->startOfDay(), $kemarin->copy()->endOfDay());
        $persenVsKemarin = $penjualanKemarin > 0
            ? round(($penjualanHariIni - $penjualanKemarin) / $penjualanKemarin * 100, 1)
            : null;

        // Pendapatan & jumlah transaksi bulan berjalan (bulan + tahun)
        $awalBulan = $hariIni->copy()->startOfMonth();
        $akhirBulan = $hariIni->copy()->endOfMonth();
        $pendapatanBulanIni = $this->totalPenjualan($awalBulan, $akhirBulan);
        $transaksiBulanIni = $this->orderAktif($awalBulan, $akhirBulan)->count();

        $stokMenipisCount = Product::where('is_active', true)->whereColumn('stok', '<', 'stok_minimum')->count();
        $produkRestockList = Product::where('is_active', true)
            ->whereColumn('stok', '<', 'stok_minimum')
            ->get();

        // Produk terlaris hari ini
        $terlaris = $this->produkTerlaris($hariIni->copy()->startOfDay(), $hariIni->copy()->endOfDay(), 1)->first();
        $produkTerlarisNama = $terlaris['nama'] ?? '-';
        $produkTerlarisPorsi = $terlaris['porsi'] ?? 0;

        // Penjualan 7 hari terakhir (termasuk hari ini)
        $harian = $this->penjualanPerHari($hariIni->copy()->subDays(6)->startOfDay(), $hariIni->copy()->endOfDay());
        $maksHarian = max(1, (float) $harian->max());
        $namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $chartDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl = $hariIni->copy()->subDays($i);
            $sales = (float) ($harian[$tgl->toDateString()] ?? 0);
            $chartDays[] = [
                'day' => $namaHari[$tgl->dayOfWeek],
                'tanggal' => $tgl->toDateString(),
                'sales' => $sales,
                'height' => round($sales / $maksHarian * 100) . '%',
                'is_highest' => $sales > 0 && $sales >= $maksHarian,
            ];
        }

        $transaksiTerbaru = Order::with('user')->orderBy('waktu', 'desc')->take(5)->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'penjualan_hari_ini' => $penjualanHariIni,
                    'penjualan_kemarin' => $penjualanKemarin,
                    'persen_vs_kemarin' => $persenVsKemarin,
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
            'penjualanKemarin',
            'persenVsKemarin',
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

    /* ---------- Helper perhitungan penjualan (dashboard, laporan, ekspor) ---------- */

    /** Query transaksi yang tidak batal dalam rentang waktu. */
    private function orderAktif(Carbon $mulai, Carbon $selesai)
    {
        return Order::where('status', '!=', 'batal')->whereBetween('waktu', [$mulai, $selesai]);
    }

    private function totalPenjualan(Carbon $mulai, Carbon $selesai): float
    {
        return (float) $this->orderAktif($mulai, $selesai)->sum('total');
    }

    /** Total penjualan per tanggal: koleksi 'Y-m-d' => total. */
    private function penjualanPerHari(Carbon $mulai, Carbon $selesai)
    {
        return $this->orderAktif($mulai, $selesai)
            ->get(['waktu', 'total'])
            ->groupBy(fn ($o) => $o->waktu->toDateString())
            ->map(fn ($grup) => (float) $grup->sum('total'));
    }

    /** Produk terlaris menurut porsi terjual: [nama, porsi, pct relatif terhadap peringkat 1]. */
    private function produkTerlaris(Carbon $mulai, Carbon $selesai, int $limit = 5)
    {
        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', '!=', 'batal')
            ->whereBetween('orders.waktu', [$mulai, $selesai])
            ->groupBy('order_items.product_id', 'products.nama')
            ->selectRaw('products.nama as nama, SUM(order_items.qty) as porsi')
            ->orderByDesc('porsi')
            ->orderBy('products.nama')
            ->limit($limit)
            ->get();

        $maks = max(1, (int) ($rows->first()->porsi ?? 0));

        return $rows->map(fn ($r) => [
            'nama' => $r->nama,
            'porsi' => (int) $r->porsi,
            'pct' => (int) round($r->porsi / $maks * 100),
        ])->values();
    }

    /**
     * Rentang laporan dari ?periode=Harian|Mingguan|Bulanan&tanggal=Y-m-d (default Bulanan, hari ini),
     * beserta rentang periode sebelumnya untuk perbandingan.
     */
    private function rentangPeriode(Request $request): array
    {
        $periode = in_array($request->get('periode'), ['Harian', 'Mingguan', 'Bulanan'], true)
            ? $request->get('periode')
            : 'Bulanan';

        $acuan = Carbon::today();
        if ($request->filled('tanggal')) {
            try {
                $acuan = Carbon::createFromFormat('Y-m-d', (string) $request->get('tanggal'))->startOfDay();
            } catch (\Throwable $e) {
                $acuan = Carbon::today();
            }
        }

        $fmt = fn (Carbon $c, string $f) => $c->copy()->locale('id')->translatedFormat($f);

        switch ($periode) {
            case 'Harian':
                $mulai = $acuan->copy()->startOfDay();
                $selesai = $acuan->copy()->endOfDay();
                $mulaiLalu = $mulai->copy()->subDay();
                $selesaiLalu = $selesai->copy()->subDay();
                $label = $fmt($mulai, 'j F Y');
                $labelLalu = $fmt($mulaiLalu, 'j F Y');
                break;
            case 'Mingguan':
                $mulai = $acuan->copy()->startOfWeek(Carbon::MONDAY);
                $selesai = $acuan->copy()->endOfWeek(Carbon::SUNDAY);
                $mulaiLalu = $mulai->copy()->subWeek();
                $selesaiLalu = $selesai->copy()->subWeek();
                $label = $fmt($mulai, 'j M') . ' - ' . $fmt($selesai, 'j M Y');
                $labelLalu = $fmt($mulaiLalu, 'j M') . ' - ' . $fmt($selesaiLalu, 'j M Y');
                break;
            default:
                $mulai = $acuan->copy()->startOfMonth();
                $selesai = $acuan->copy()->endOfMonth();
                $mulaiLalu = $mulai->copy()->subMonthNoOverflow()->startOfMonth();
                $selesaiLalu = $mulaiLalu->copy()->endOfMonth();
                $label = $fmt($mulai, 'F Y');
                $labelLalu = $fmt($mulaiLalu, 'F Y');
        }

        return compact('periode', 'acuan', 'mulai', 'selesai', 'mulaiLalu', 'selesaiLalu', 'label', 'labelLalu');
    }

    /** Ringkasan laporan untuk satu rentang dari rentangPeriode(). */
    private function ringkasanLaporan(array $r): array
    {
        $totalPenjualan = $this->totalPenjualan($r['mulai'], $r['selesai']);
        $jumlahTransaksi = $this->orderAktif($r['mulai'], $r['selesai'])->count();
        $rataRataTransaksi = $jumlahTransaksi > 0 ? round($totalPenjualan / $jumlahTransaksi) : 0;

        // Tren: per jam untuk laporan harian, per tanggal untuk mingguan/bulanan
        $orders = $this->orderAktif($r['mulai'], $r['selesai'])->get(['waktu', 'total']);
        $trendPoints = [];
        if ($r['periode'] === 'Harian') {
            $perJam = $orders->groupBy(fn ($o) => (int) $o->waktu->format('G'))->map(fn ($g) => (float) $g->sum('total'));
            $jamAwal = min($perJam->keys()->min() ?? 8, 8);
            $jamAkhir = max($perJam->keys()->max() ?? 20, 20);
            for ($j = $jamAwal; $j <= $jamAkhir; $j++) {
                $trendPoints[] = ['x' => sprintf('%02d:00', $j), 'val' => (float) ($perJam[$j] ?? 0)];
            }
        } else {
            $perHari = $orders->groupBy(fn ($o) => $o->waktu->toDateString())->map(fn ($g) => (float) $g->sum('total'));
            for ($d = $r['mulai']->copy(); $d->lte($r['selesai']); $d->addDay()) {
                $trendPoints[] = [
                    'x' => $d->copy()->locale('id')->translatedFormat('j M'),
                    'val' => (float) ($perHari[$d->toDateString()] ?? 0),
                ];
            }
        }

        $produkTerlaris = $this->produkTerlaris($r['mulai'], $r['selesai'], 5)->all();

        $penjualanLalu = $this->totalPenjualan($r['mulaiLalu'], $r['selesaiLalu']);
        if ($penjualanLalu > 0) {
            $persen = ($totalPenjualan - $penjualanLalu) / $penjualanLalu * 100;
            $persentase = ($persen >= 0 ? '+' : '') . number_format($persen, 1, ',', '.') . '%';
            $keterangan = $persen > 5 ? 'penjualan naik dibanding periode sebelumnya'
                : ($persen < -5 ? 'penjualan turun dibanding periode sebelumnya' : 'penjualan relatif stabil');
        } else {
            $persentase = '-';
            $keterangan = 'belum ada penjualan pada periode sebelumnya';
        }

        $perbandingan = [
            'bulan_ini' => $totalPenjualan,
            'bulan_lalu' => $penjualanLalu,
            'label_ini' => $r['label'],
            'label_lalu' => $r['labelLalu'],
            'persentase' => $persentase,
            'keterangan' => $keterangan,
        ];

        return compact('totalPenjualan', 'jumlahTransaksi', 'rataRataTransaksi', 'trendPoints', 'produkTerlaris', 'perbandingan');
    }

    /**
     * Frame 2: Halaman Produk & Stok (FR-20)
     */
    public function produk(Request $request)
    {
        $this->checkOwnerAccess();

        $query = Product::where('is_active', true)->with('supplier');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

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

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'tanggal' => Carbon::now(),
                'jenis' => 'stok_awal',
                'qty' => $product->stok,
                'saldo_setelah' => $product->stok,
                'keterangan' => 'Stok awal saat produk ditambahkan',
            ]);

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
     * Edit Produk & Koreksi Stok (FR-22, BR-02, BR-11)
     */
    public function updateProduk(Request $request, Product $product)
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
            'alasan_koreksi' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'satuan.required' => 'Satuan produk wajib diisi.',
            'harga.required' => 'Harga jual wajib diisi.',
            'stok.required' => 'Stok produk wajib diisi.',
            'stok_minimum.required' => 'Batas minimum wajib diisi.',
            'kategori.required' => 'Kategori produk wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $dataLama = $product->toArray();
            $oldStok = $product->stok;
            $newStok = (int) $validated['stok'];
            $diffStok = $newStok - $oldStok;

            $product->update([
                'nama' => $validated['nama'],
                'satuan' => $validated['satuan'],
                'harga' => $validated['harga'],
                'stok' => $newStok,
                'stok_minimum' => $validated['stok_minimum'],
                'kategori' => $validated['kategori'],
                'supplier_id' => $validated['supplier_id'] ?? $product->supplier_id,
            ]);

            // Jika stok berubah, catat penyesuaian stok di stock_movements (BR-02 & FR-22)
            if ($diffStok !== 0) {
                $alasan = $validated['alasan_koreksi'] ?: 'Koreksi stok oleh Owner';
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'tanggal' => Carbon::now(),
                    'jenis' => 'penyesuaian',
                    'qty' => $diffStok,
                    'saldo_setelah' => $newStok,
                    'keterangan' => "Penyesuaian stok dari {$oldStok} menjadi {$newStok}: {$alasan}",
                ]);
            }

            // Catat ke activity logs
            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'edit_produk',
                'tabel' => 'products',
                'data_lama' => $dataLama,
                'data_baru' => $product->fresh()->toArray(),
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => "Produk '{$product->nama}' berhasil diperbarui!",
                    'product' => $product->fresh(),
                ]);
            }

            return redirect()->route('owner.produk')->with('success', "Data produk '{$product->nama}' berhasil diperbarui!");
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui produk: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus / Nonaktifkan Produk (Soft Delete - BR-11)
     */
    public function destroyProduk(Request $request, Product $product)
    {
        $this->checkOwnerAccess();

        $productName = $product->nama;
        $product->is_active = false;
        $product->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'hapus_produk',
            'tabel' => 'products',
            'data_lama' => ['id' => $product->id, 'nama' => $productName, 'is_active' => true],
            'data_baru' => ['id' => $product->id, 'nama' => $productName, 'is_active' => false],
            'waktu' => Carbon::now(),
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Produk '{$productName}' berhasil dihapus!",
            ]);
        }

        return redirect()->route('owner.produk')->with('success', "Produk '{$productName}' berhasil dihapus dari katalog!");
    }

    /**
     * Frame 4: Tampilan Catat Stok Masuk (FR-24, NFR-02)
     */
    public function stokMasuk(Request $request)
    {
        $this->checkOwnerAccess();

        $suppliers = Supplier::all();
        $products = Product::where('is_active', true)->orderBy('nama')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'suppliers' => $suppliers,
                'products' => $products,
            ]);
        }

        return view('owner.stok-masuk', compact('suppliers', 'products'));
    }

    /**
     * Frame 4: Simpan Catat Stok Masuk (FR-24, BR-01, BR-02, BR-11)
     */
    public function storeStokMasuk(Request $request)
    {
        $this->checkOwnerAccess();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:255',
        ], [
            'tanggal.required' => 'Tanggal stok masuk wajib diisi.',
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'items.required' => 'Minimal satu item produk harus dimasukkan.',
            'items.*.qty.min' => 'Jumlah produk harus lebih dari 0.',
        ]);

        DB::beginTransaction();
        try {
            $updatedProducts = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                $oldStok = $product->stok;
                $newStok = $oldStok + $item['qty'];

                $product->stok = $newStok;
                $product->save();

                // Catat di stock_movements (jenis = masuk)
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'supplier_id' => $validated['supplier_id'],
                    'tanggal' => Carbon::parse($validated['tanggal']),
                    'jenis' => 'masuk',
                    'qty' => $item['qty'],
                    'saldo_setelah' => $newStok,
                    'keterangan' => $validated['catatan'] ?: 'Stok masuk dari pemasok',
                ]);

                $updatedProducts[] = [
                    'nama' => $product->nama,
                    'old_stok' => $oldStok,
                    'new_stok' => $newStok,
                    'status' => $product->status_stok,
                ];
            }

            // Catat activity log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'stok_masuk',
                'tabel' => 'stock_movements',
                'data_lama' => null,
                'data_baru' => $updatedProducts,
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok masuk berhasil disimpan dan stok otomatis diperbarui!',
                    'data' => $updatedProducts,
                ]);
            }

            return redirect()->route('owner.produk')->with('success', 'Stok masuk berhasil dicatat! Seluruh stok terkait telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencatat stok masuk: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withInput()->withErrors(['error' => 'Gagal mencatat stok masuk: ' . $e->getMessage()]);
        }
    }

    /**
     * Frame 5: Halaman Pemasok & Kebutuhan Stok (FR-25, FR-26)
     */
    public function pemasok(Request $request)
    {
        $this->checkOwnerAccess();

        // Data pemasok beserta produk yang dipasok & kiriman terakhir
        $suppliers = Supplier::with(['products', 'stockMovements' => function ($q) {
            $q->where('jenis', 'masuk')->orderBy('tanggal', 'desc');
        }])->get();

        // Daftar produk yang menipis (Kebutuhan Stok - FR-26, BR-04)
        $kebutuhanStok = Product::where('is_active', true)
            ->whereColumn('stok', '<', 'stok_minimum')
            ->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'suppliers' => $suppliers,
                'kebutuhan_stok' => $kebutuhanStok,
            ]);
        }

        return view('owner.pemasok', compact('suppliers', 'kebutuhanStok'));
    }

    /**
     * Frame 5: Tambah Pemasok Baru (FR-25)
     */
    public function storePemasok(Request $request)
    {
        $this->checkOwnerAccess();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kontak' => 'required|string|max:50',
        ], [
            'nama.required' => 'Nama pemasok wajib diisi.',
            'kontak.required' => 'Nomor kontak pemasok wajib diisi.',
        ]);

        $supplier = Supplier::create($validated);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Pemasok '{$supplier->nama}' berhasil ditambahkan!",
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('owner.pemasok')->with('success', "Pemasok '{$supplier->nama}' berhasil ditambahkan!");
    }

    /**
     * Frame 6: Halaman Laporan Penjualan (FR-27)
     */
    public function laporan(Request $request)
    {
        $this->checkOwnerAccess();

        $r = $this->rentangPeriode($request);
        $periode = $r['periode'];
        $range = $r['label'];
        $tanggalAcuan = $r['acuan']->toDateString();

        [
            'totalPenjualan' => $totalPenjualan,
            'jumlahTransaksi' => $jumlahTransaksi,
            'rataRataTransaksi' => $rataRataTransaksi,
            'trendPoints' => $trendPoints,
            'produkTerlaris' => $produkTerlaris,
            'perbandingan' => $perbandingan,
        ] = $this->ringkasanLaporan($r);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => compact('periode', 'range', 'totalPenjualan', 'jumlahTransaksi', 'rataRataTransaksi', 'trendPoints', 'produkTerlaris', 'perbandingan'),
            ]);
        }

        return view('owner.laporan', compact(
            'periode',
            'range',
            'tanggalAcuan',
            'totalPenjualan',
            'jumlahTransaksi',
            'rataRataTransaksi',
            'trendPoints',
            'produkTerlaris',
            'perbandingan'
        ));
    }

    /**
     * Ekspor Laporan Excel / CSV (FR-27, UAT-09)
     * Mengikuti periode yang dipilih: ringkasan (total, jumlah transaksi, rata-rata),
     * produk terlaris, lalu daftar transaksi periode tersebut.
     */
    public function exportExcel(Request $request)
    {
        $this->checkOwnerAccess();

        $r = $this->rentangPeriode($request);
        $ringkasan = $this->ringkasanLaporan($r);
        $orders = Order::with('user')
            ->whereBetween('waktu', [$r['mulai'], $r['selesai']])
            ->orderBy('waktu', 'asc')
            ->get();

        $filename = 'Laporan_Penjualan_Madam_Dimsum_' . $r['periode'] . '_' . $r['mulai']->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($r, $ringkasan, $orders) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8

            fputcsv($handle, ['Laporan Penjualan Madam Dimsum']);
            fputcsv($handle, ['Periode', $r['periode'] . ' - ' . $r['label']]);
            fputcsv($handle, ['Total Penjualan (Rp)', $ringkasan['totalPenjualan']]);
            fputcsv($handle, ['Jumlah Transaksi', $ringkasan['jumlahTransaksi']]);
            fputcsv($handle, ['Rata-rata per Transaksi (Rp)', $ringkasan['rataRataTransaksi']]);
            fputcsv($handle, ['Dibanding ' . $r['labelLalu'], $ringkasan['perbandingan']['persentase']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Produk Terlaris']);
            fputcsv($handle, ['Peringkat', 'Produk', 'Porsi Terjual']);
            foreach ($ringkasan['produkTerlaris'] as $i => $p) {
                fputcsv($handle, [$i + 1, $p['nama'], $p['porsi']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['Daftar Transaksi']);
            fputcsv($handle, ['No. Transaksi', 'Waktu', 'Kasir', 'Total (Rp)', 'Dibayar (Rp)', 'Kembalian (Rp)', 'Status']);
            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->kode,
                    $o->waktu ? $o->waktu->format('Y-m-d H:i') : '-',
                    $o->user->name ?? 'Kasir',
                    $o->total,
                    $o->dibayar,
                    $o->kembalian,
                    $o->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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

    /**
     * Frame 7: Riwayat Transaksi (FR-28, BR-06)
     */
    public function riwayatTransaksi(Request $request)
    {
        $this->checkOwnerAccess();

        $query = Order::with(['user', 'items.product'])->orderBy('waktu', 'desc');

        // Filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('waktu', $request->tanggal);
        }

        // Filter pencarian kode transaksi atau produk
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

        // Default detail transaksi terpilih (contoh: TRX-0064 sesuai mockup)
        $selectedKode = $request->get('selected', 'TRX-0064');
        $selectedOrder = $orders->firstWhere('kode', $selectedKode) ?? $orders->first();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'orders' => $orders,
                'selected_order' => $selectedOrder,
            ]);
        }

        return view('owner.riwayat-transaksi', compact('orders', 'selectedOrder'));
    }

    /**
     * Frame 7: Batalkan Transaksi (BR-06)
     */
    public function batalkanTransaksi(Request $request, Order $order)
    {
        $this->checkOwnerAccess();

        $validated = $request->validate([
            'alasan_batal' => 'required|string|max:255',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi (BR-06).',
        ]);

        if ($order->status === 'batal') {
            return back()->withErrors(['error' => 'Transaksi ini sudah dibatalkan sebelumnya.']);
        }

        DB::beginTransaction();
        try {
            // Ubah status order
            $order->status = 'batal';
            $order->alasan_batal = $validated['alasan_batal'];
            $order->save();

            // Kembalikan stok lewat pergerakan penyesuaian (BR-06 & BR-02)
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $newStok = $product->stok + $item->qty;
                    $product->stok = $newStok;
                    $product->save();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'tanggal' => Carbon::now(),
                        'jenis' => 'penyesuaian',
                        'qty' => $item->qty, // positif mengembalikan stok
                        'saldo_setelah' => $newStok,
                        'keterangan' => "Pengembalian stok dari pembatalan {$order->kode}: {$validated['alasan_batal']}",
                    ]);
                }
            }

            // Catat activity log
            ActivityLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'batal_transaksi',
                'tabel' => 'orders',
                'data_lama' => ['kode' => $order->kode, 'status' => 'selesai'],
                'data_baru' => ['kode' => $order->kode, 'status' => 'batal', 'alasan' => $validated['alasan_batal']],
                'waktu' => Carbon::now(),
            ]);

            DB::commit();

            return redirect()->route('owner.riwayat-transaksi')->with('success', "Transaksi {$order->kode} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Frame 8: Pengguna & Hak Akses (FR-29, BR-10)
     */
    public function pengguna(Request $request)
    {
        $this->checkOwnerAccess();

        $users = \App\Models\User::orderBy('role', 'asc')->orderBy('name', 'asc')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'users' => $users,
            ]);
        }

        return view('owner.pengguna', compact('users'));
    }

    /**
     * Frame 8: Tambah Pengguna Baru (FR-29)
     */
    public function storePengguna(Request $request)
    {
        $this->checkOwnerAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,kasir,dapur',
        ], [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Peran (Role) wajib dipilih.',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'username' => trim($validated['username']),
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'tambah_pengguna',
            'tabel' => 'users',
            'data_lama' => null,
            'data_baru' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
            'waktu' => Carbon::now(),
        ]);

        return redirect()->route('owner.pengguna')->with('success', "Pengguna '{$user->name}' berhasil ditambahkan!");
    }

    /**
     * Frame 8: Nonaktifkan / Aktifkan Pengguna (BR-10, UAT-10)
     */
    public function toggleStatusPengguna(\App\Models\User $user)
    {
        $this->checkOwnerAccess();

        // Cegah owner menonaktifkan akunnya sendiri
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan (UAT-10)';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'ubah_status_pengguna',
            'tabel' => 'users',
            'data_lama' => ['is_active' => !$user->is_active],
            'data_baru' => ['is_active' => $user->is_active],
            'waktu' => Carbon::now(),
        ]);

        return redirect()->route('owner.pengguna')->with('success', "Akun '{$user->name}' berhasil {$statusText}!");
    }

    /**
     * Frame 8: Reset Password Pengguna (FR-04, FR-29)
     */
    public function resetPasswordPengguna(Request $request, \App\Models\User $user)
    {
        $this->checkOwnerAccess();

        $newPassword = $request->input('password', 'password123');
        $user->password = bcrypt($newPassword);
        $user->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'reset_password',
            'tabel' => 'users',
            'data_lama' => null,
            'data_baru' => ['username' => $user->username],
            'waktu' => Carbon::now(),
        ]);

        return redirect()->route('owner.pengguna')->with('success', "Password akun '{$user->name}' berhasil di-reset menjadi '{$newPassword}'.");
    }

    /**
     * Frame 9: Pencadangan Data (FR-30)
     */
    public function pencadangan(Request $request, BackupService $service)
    {
        $this->checkOwnerAccess();

        $backups = Backup::orderBy('waktu', 'desc')->orderBy('id', 'desc')->get();
        [$frekuensi, $jam] = $service->jadwal();
        $terakhir = $backups->firstWhere('status', 'berhasil');
        $jadwalBerikutnya = $service->jadwalBerikutnya();
        $fileTersedia = $backups->mapWithKeys(fn ($b) => [$b->id => $service->fileAda($b)]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'backups' => $backups,
                'frekuensi' => $frekuensi,
                'jam' => $jam,
                'jadwal_berikutnya' => $jadwalBerikutnya->toIso8601String(),
            ]);
        }

        return view('owner.pencadangan', compact('backups', 'frekuensi', 'jam', 'terakhir', 'jadwalBerikutnya', 'fileTersedia'));
    }

    /**
     * Frame 9: Cadangkan Sekarang (Manual Backup - FR-30)
     */
    public function createBackup(Request $request, BackupService $service)
    {
        $this->checkOwnerAccess();

        $backup = $service->buat('manual');

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'buat_cadangan',
            'tabel' => 'backups',
            'data_lama' => null,
            'data_baru' => $backup->toArray(),
            'waktu' => Carbon::now(),
        ]);

        if ($backup->status !== 'berhasil') {
            return redirect()->route('owner.pencadangan')
                ->withErrors(['backup' => 'Pencadangan gagal. Coba lagi atau periksa ruang penyimpanan server.']);
        }

        return redirect()->route('owner.pencadangan')->with('success', 'Cadangan data berhasil dibuat (' . $backup->ukuran_kb . ' KB).');
    }

    /**
     * Frame 9: Unduh file cadangan (FR-30)
     */
    public function downloadBackup(Backup $backup, BackupService $service)
    {
        $this->checkOwnerAccess();

        if (! $service->fileAda($backup)) {
            return redirect()->route('owner.pencadangan')->withErrors(['backup' => 'File cadangan tidak ditemukan atau cadangan ini gagal dibuat.']);
        }

        return response()->download($service->pathAbsolut($backup), basename($backup->file_path), [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Frame 9: Pulihkan data dari cadangan (FR-30, BR-11)
     */
    public function restoreBackup(Request $request, Backup $backup, BackupService $service)
    {
        $this->checkOwnerAccess();

        $request->validate([
            'konfirmasi' => ['required', 'in:PULIHKAN'],
        ], [
            'konfirmasi.required' => 'Ketik PULIHKAN untuk mengonfirmasi pemulihan.',
            'konfirmasi.in' => 'Ketik PULIHKAN (huruf besar) untuk mengonfirmasi pemulihan.',
        ]);

        try {
            $pengaman = $service->pulihkan($backup, Auth::id());
        } catch (\RuntimeException $e) {
            return redirect()->route('owner.pencadangan')->withErrors(['backup' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('owner.pencadangan')->withErrors(['backup' => 'Pemulihan gagal. Data tidak diubah.']);
        }

        $tanggal = $backup->waktu ? $backup->waktu->format('d M Y H:i') : '';
        return redirect()->route('owner.pencadangan')->with(
            'success',
            "Data berhasil dipulihkan ke kondisi {$tanggal}. Kondisi sebelumnya disimpan sebagai cadangan " . $pengaman->waktu->format('d M Y H:i') . '.'
        );
    }

    /**
     * Frame 9: Perbarui Jadwal Otomatis (FR-30)
     */
    public function updateBackupSchedule(Request $request)
    {
        $this->checkOwnerAccess();

        $data = $request->validate([
            'frekuensi' => ['required', 'in:' . implode(',', BackupService::FREKUENSI)],
            'jam' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
        ], [
            'frekuensi.required' => 'Frekuensi wajib dipilih.',
            'frekuensi.in' => 'Frekuensi harus Harian, Mingguan, atau Bulanan.',
            'jam.required' => 'Jam wajib dipilih.',
            'jam.regex' => 'Format jam tidak valid.',
        ]);

        \App\Models\Setting::updateOrCreate(['kunci' => 'backup_frekuensi'], ['nilai' => $data['frekuensi']]);
        \App\Models\Setting::updateOrCreate(['kunci' => 'backup_jam'], ['nilai' => $data['jam']]);

        return redirect()->route('owner.pencadangan')->with('success', 'Jadwal pencadangan otomatis berhasil diperbarui!');
    }
}
