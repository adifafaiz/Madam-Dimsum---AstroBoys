<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dashboard & laporan Owner harus dihitung dari data (bukan angka tetap),
 * dan ekspor Excel berisi total, jumlah transaksi, serta produk terlaris (UAT-09).
 */
class LaporanDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $kasir;
    protected Product $siomay;
    protected Product $hakau;
    private int $nomor = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-26 10:00:00'));

        $this->owner = User::create(['name' => 'Madam Owner', 'username' => 'owner', 'role' => 'owner', 'password' => bcrypt('password123'), 'is_active' => true]);
        $this->kasir = User::create(['name' => 'Rina Kasir', 'username' => 'rina', 'role' => 'kasir', 'password' => bcrypt('password123'), 'is_active' => true]);

        $this->siomay = Product::create(['nama' => 'Siomay Ayam', 'kategori' => 'Dimsum Kukus', 'satuan' => 'Porsi', 'harga' => 15000, 'stok' => 40, 'stok_minimum' => 10, 'is_active' => true]);
        $this->hakau = Product::create(['nama' => 'Hakau Udang', 'kategori' => 'Dimsum Kukus', 'satuan' => 'Porsi', 'harga' => 18000, 'stok' => 40, 'stok_minimum' => 10, 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Buat transaksi dengan item [[produk, qty], ...]; total = jumlah subtotal. */
    private function transaksi(string $waktu, array $items, string $status = 'selesai'): Order
    {
        $total = collect($items)->sum(fn ($i) => $i[0]->harga * $i[1]);
        $order = Order::create([
            'kode' => sprintf('TRX-%04d', ++$this->nomor),
            'waktu' => Carbon::parse($waktu),
            'user_id' => $this->kasir->id,
            'total' => $total,
            'dibayar' => $total,
            'kembalian' => 0,
            'status' => $status,
            'alasan_batal' => $status === 'batal' ? 'Uji' : null,
        ]);
        foreach ($items as [$produk, $qty]) {
            OrderItem::create([
                'order_id' => $order->id, 'product_id' => $produk->id, 'qty' => $qty,
                'harga_satuan' => $produk->harga, 'subtotal' => $produk->harga * $qty,
            ]);
        }
        return $order;
    }

    public function test_dashboard_kosong_tidak_menampilkan_angka_palsu(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/dashboard');

        $response->assertOk();
        $this->assertEquals(0, $response->viewData('penjualanHariIni'));
        $this->assertEquals(0, $response->viewData('pendapatanBulanIni'));
        $this->assertEquals(0, $response->viewData('transaksiBulanIni'));
        $this->assertSame('-', $response->viewData('produkTerlarisNama'));
        $this->assertNull($response->viewData('persenVsKemarin'));
        $this->assertEquals(0, collect($response->viewData('chartDays'))->sum('sales'));
        $response->assertDontSee('1.075.000');
        $response->assertDontSee('38.420.000');
    }

    public function test_dashboard_dihitung_dari_transaksi(): void
    {
        // Hari ini (26 Nov): 60.000 + 18.000; batal tidak dihitung
        $this->transaksi('2026-11-26 08:00', [[$this->siomay, 4]]);
        $this->transaksi('2026-11-26 09:00', [[$this->hakau, 1]]);
        $this->transaksi('2026-11-26 09:30', [[$this->hakau, 10]], 'batal');
        // Kemarin: 60.000
        $this->transaksi('2026-11-25 12:00', [[$this->siomay, 4]]);
        // Awal bulan: 30.000
        $this->transaksi('2026-11-01 12:00', [[$this->siomay, 2]]);
        // November tahun lalu & Oktober: tidak masuk bulan ini
        $this->transaksi('2025-11-20 12:00', [[$this->siomay, 10]]);
        $this->transaksi('2026-10-31 12:00', [[$this->siomay, 10]]);

        $response = $this->actingAs($this->owner)->get('/owner/dashboard');
        $response->assertOk();

        $this->assertEquals(78000, $response->viewData('penjualanHariIni'));
        $this->assertEquals(60000, $response->viewData('penjualanKemarin'));
        $this->assertEquals(30.0, $response->viewData('persenVsKemarin'));
        $this->assertEquals(168000, $response->viewData('pendapatanBulanIni'));
        $this->assertEquals(4, $response->viewData('transaksiBulanIni'));
        $this->assertSame('Siomay Ayam', $response->viewData('produkTerlarisNama'));
        $this->assertSame(4, $response->viewData('produkTerlarisPorsi'));

        $chart = collect($response->viewData('chartDays'));
        $this->assertCount(7, $chart);
        $this->assertSame('2026-11-26', $chart->last()['tanggal']);
        $this->assertEquals(78000, $chart->last()['sales']);
        $this->assertSame('100%', $chart->last()['height']);
        $this->assertEquals(60000, $chart->firstWhere('tanggal', '2026-11-25')['sales']);

        $response->assertSee('naik 30,0%', false);
    }

    public function test_laporan_bulanan_november_dari_data(): void
    {
        $this->transaksi('2026-11-03 10:00', [[$this->siomay, 2], [$this->hakau, 1]]); // 48.000
        $this->transaksi('2026-11-20 10:00', [[$this->siomay, 3]]);                    // 45.000
        $this->transaksi('2026-11-21 10:00', [[$this->hakau, 5]], 'batal');            // diabaikan
        $this->transaksi('2026-10-15 10:00', [[$this->hakau, 4]]);                     // Oktober 72.000

        $response = $this->actingAs($this->owner)->get('/owner/laporan?periode=Bulanan&tanggal=2026-11-15');
        $response->assertOk();

        $this->assertEquals(93000, $response->viewData('totalPenjualan'));
        $this->assertEquals(2, $response->viewData('jumlahTransaksi'));
        $this->assertEquals(46500, $response->viewData('rataRataTransaksi'));
        $this->assertCount(30, $response->viewData('trendPoints'));

        $terlaris = $response->viewData('produkTerlaris');
        $this->assertSame('Siomay Ayam', $terlaris[0]['nama']);
        $this->assertSame(5, $terlaris[0]['porsi']);
        $this->assertSame('Hakau Udang', $terlaris[1]['nama']);

        $banding = $response->viewData('perbandingan');
        $this->assertEquals(72000, $banding['bulan_lalu']);
        $this->assertSame('+29,2%', $banding['persentase']);

        $response->assertSee('November 2026');
    }

    public function test_ekspor_excel_november_berisi_ringkasan_uat09(): void
    {
        $this->transaksi('2026-11-03 10:00', [[$this->siomay, 2], [$this->hakau, 1]]);
        $this->transaksi('2026-11-20 10:00', [[$this->siomay, 3]]);
        $this->transaksi('2026-12-01 10:00', [[$this->hakau, 9]]); // di luar November

        $response = $this->actingAs($this->owner)->get('/owner/laporan/export-excel?periode=Bulanan&tanggal=2026-11-01');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('November 2026', $csv);
        $this->assertStringContainsString('"Total Penjualan (Rp)",93000', $csv);
        $this->assertStringContainsString('"Jumlah Transaksi",2', $csv);
        $this->assertStringContainsString('Produk Terlaris', $csv);
        $this->assertStringContainsString('1,"Siomay Ayam",5', $csv);
        $this->assertStringContainsString('TRX-0001', $csv);
        $this->assertStringNotContainsString('TRX-0003', $csv);
    }

    public function test_laporan_periode_tidak_valid_jatuh_ke_bulanan(): void
    {
        $this->actingAs($this->owner)->get('/owner/laporan?periode=Tahunan&tanggal=bukan-tanggal')
            ->assertOk()
            ->assertViewHas('periode', 'Bulanan');
    }
}
