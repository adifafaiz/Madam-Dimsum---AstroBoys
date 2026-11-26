<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DapurTest extends TestCase
{
    use RefreshDatabase;

    private User $dapurUser;
    private User $ownerUser;
    private User $kasirUser;
    private Product $siomay;
    private Product $hakau;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dapurUser = User::create([
            'name' => 'Agus Dapur',
            'username' => 'agus',
            'email' => 'agus@madamdimsum.com',
            'role' => 'dapur',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $this->ownerUser = User::create([
            'name' => 'Madam Owner',
            'username' => 'owner',
            'email' => 'owner@madamdimsum.com',
            'role' => 'owner',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $this->kasirUser = User::create([
            'name' => 'Rina Kasir',
            'username' => 'rina',
            'email' => 'rina@madamdimsum.com',
            'role' => 'kasir',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        $supplier = Supplier::create([
            'nama' => 'Pemasok Dapur Utama',
            'kontak' => '08123456789',
        ]);

        $this->siomay = Product::create([
            'nama' => 'Siomay Ayam',
            'kategori' => 'Dimsum Kukus',
            'satuan' => 'Porsi',
            'harga' => 15000.00,
            'stok' => 45,
            'stok_minimum' => 15,
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);

        $this->hakau = Product::create([
            'nama' => 'Hakau Udang',
            'kategori' => 'Dimsum Kukus',
            'satuan' => 'Porsi',
            'harga' => 18000.00,
            'stok' => 4, // Menipis
            'stok_minimum' => 10,
            'supplier_id' => $supplier->id,
            'is_active' => true,
        ]);
    }

    /**
     * FR-02: Non-dapur user tidak boleh mengakses modul dapur (403 Forbidden)
     */
    public function test_non_dapur_cannot_access_dapur_module(): void
    {
        $response = $this->actingAs($this->kasirUser)->get('/dapur/antrian');
        $response->assertStatus(403);

        $responseOwner = $this->actingAs($this->ownerUser)->get('/dapur/antrian');
        $responseOwner->assertStatus(403);
    }

    /**
     * FR-05: Dapur dapat melihat antrian pesanan masuk
     */
    public function test_dapur_can_access_antrian_pesanan(): void
    {
        // Buat order contoh dengan status baru
        $order = Order::create([
            'kode' => 'TRX-0065',
            'waktu' => now(),
            'user_id' => $this->kasirUser->id,
            'total' => 30000,
            'dibayar' => 50000,
            'kembalian' => 20000,
            'status' => 'baru',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->siomay->id,
            'qty' => 2,
            'harga_satuan' => 15000,
            'subtotal' => 30000,
        ]);

        $response = $this->actingAs($this->dapurUser)->get('/dapur/antrian');
        $response->assertStatus(200);
        $response->assertSee('Antrian Pesanan');
        $response->assertSee('TRX-0065');
        $response->assertSee('Siomay Ayam');
        $response->assertSee('Mulai masak');
    }

    /**
     * FR-06, FR-07, BR-05: Dapur dapat mengubah status pesanan dari baru -> diproses -> siap -> selesai
     */
    public function test_dapur_can_update_status_pesanan(): void
    {
        $order = Order::create([
            'kode' => 'TRX-0066',
            'waktu' => now(),
            'user_id' => $this->kasirUser->id,
            'total' => 18000,
            'dibayar' => 20000,
            'kembalian' => 2000,
            'status' => 'baru',
        ]);

        // 1. Ubah ke diproses
        $response1 = $this->actingAs($this->dapurUser)->post("/dapur/antrian/{$order->id}", [
            'status' => 'diproses',
        ]);
        $response1->assertRedirect(route('dapur.antrian'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'diproses']);

        // 2. Ubah ke siap
        $response2 = $this->actingAs($this->dapurUser)->post("/dapur/antrian/{$order->id}", [
            'status' => 'siap',
        ]);
        $response2->assertRedirect(route('dapur.antrian'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'siap']);

        // 3. Ubah ke selesai (diambil)
        $response3 = $this->actingAs($this->dapurUser)->post("/dapur/antrian/{$order->id}", [
            'status' => 'selesai',
        ]);
        $response3->assertRedirect(route('dapur.antrian'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'selesai']);
    }

    /**
     * FR-08, FR-09: Dapur dapat membuka halaman catat produksi
     */
    public function test_dapur_can_access_catat_produksi_page(): void
    {
        $response = $this->actingAs($this->dapurUser)->get('/dapur/catat-produksi');
        $response->assertStatus(200);
        $response->assertSee('Catat Produksi');
        $response->assertSee('Hakau Udang');
        $response->assertSee('Simpan Produksi');
    }

    /**
     * FR-08, BR-01, BR-02: Dapur dapat mencatat hasil produksi dan stok bertambah otomatis
     */
    public function test_dapur_can_store_produksi_and_update_stock(): void
    {
        $stokAwalHakau = $this->hakau->stok; // 4

        $payload = [
            'tanggal' => now()->toDateString(),
            'sesi' => 'sore',
            'catatan' => 'Produksi sore persiapan akhir pekan',
            'items' => [
                [
                    'product_id' => $this->hakau->id,
                    'qty' => 20,
                ],
            ],
        ];

        $response = $this->actingAs($this->dapurUser)->post('/dapur/catat-produksi', $payload);
        $response->assertRedirect(route('dapur.stok'));

        // Cek stok hakau bertambah 20 menjadi 24
        $this->hakau->refresh();
        $this->assertEquals($stokAwalHakau + 20, $this->hakau->stok);
        $this->assertEquals('Aman', $this->hakau->status_stok);

        // Cek tercatat di stock_movements
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->hakau->id,
            'jenis' => 'produksi',
            'qty' => 20,
            'sesi' => 'sore',
            'saldo_setelah' => 24,
        ]);
    }

    /**
     * FR-10, BR-03, BR-04: Dapur dapat melihat stok produk dan filter perlu diproduksi
     */
    public function test_dapur_can_view_stok_and_filter(): void
    {
        // 1. Tampilan biasa
        $response = $this->actingAs($this->dapurUser)->get('/dapur/stok');
        $response->assertStatus(200);
        $response->assertSee('Siomay Ayam');
        $response->assertSee('Hakau Udang');
        $response->assertSee('Aman');
        $response->assertSee('Menipis');

        // 2. Filter hanya perlu diproduksi
        $responseFiltered = $this->actingAs($this->dapurUser)->get('/dapur/stok?hanya_perlu_produksi=1');
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('Hakau Udang'); // Stok 4 < 10 (Menipis)
        $responseFiltered->assertDontSee('Siomay Ayam'); // Stok 45 >= 15 (Aman)
    }

    /**
     * FR-11: Dapur dapat melihat riwayat produksi
     */
    public function test_dapur_can_view_riwayat_produksi(): void
    {
        StockMovement::create([
            'product_id' => $this->siomay->id,
            'user_id' => $this->dapurUser->id,
            'tanggal' => now(),
            'jenis' => 'produksi',
            'qty' => 25,
            'saldo_setelah' => 70,
            'sesi' => 'pagi',
            'keterangan' => 'Produksi rutin pagi',
        ]);

        $response = $this->actingAs($this->dapurUser)->get('/dapur/riwayat-produksi');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Produksi');
        $response->assertSee('Siomay Ayam');
        $response->assertSee('+25 porsi');
        $response->assertSee('Pagi');
        $response->assertSee('Produksi rutin pagi');
    }
}
