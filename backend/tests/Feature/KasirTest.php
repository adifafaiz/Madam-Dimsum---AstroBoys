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

class KasirTest extends TestCase
{
    use RefreshDatabase;

    private User $kasirUser;
    private User $dapurUser;
    private User $ownerUser;
    private Product $siomay;
    private Product $hakau;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasirUser = User::create([
            'name' => 'Rina Kasir',
            'username' => 'rina',
            'email' => 'rina@madamdimsum.com',
            'role' => 'kasir',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

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

        $supplier = Supplier::create([
            'nama' => 'Pemasok Kasir Test',
            'kontak' => '08123456789',
        ]);

        $this->siomay = Product::create([
            'nama' => 'Siomay Ayam',
            'kategori' => 'Dimsum Kukus',
            'satuan' => 'Porsi',
            'harga' => 15000.00,
            'stok' => 48,
            'stok_minimum' => 10,
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
     * FR-02: Non-kasir dilarang mengakses modul kasir (403 Forbidden)
     */
    public function test_non_kasir_cannot_access_kasir_module(): void
    {
        $responseDapur = $this->actingAs($this->dapurUser)->get('/kasir/transaksi');
        $responseDapur->assertStatus(403);

        $responseOwner = $this->actingAs($this->ownerUser)->get('/kasir/transaksi');
        $responseOwner->assertStatus(403);
    }

    /**
     * FR-12: Kasir dapat membuka halaman pencatatan transaksi POS
     */
    public function test_kasir_can_access_transaksi_page(): void
    {
        $response = $this->actingAs($this->kasirUser)->get('/kasir/transaksi');
        $response->assertStatus(200);
        $response->assertSee('Pencatatan Transaksi');
        $response->assertSee('Siomay Ayam');
        $response->assertSee('Pesanan Aktif');
        $response->assertSee('Simpan Transaksi');
    }

    /**
     * FR-12, FR-13, BR-01, BR-02, BR-05: Kasir memproses transaksi berhasil
     */
    public function test_kasir_can_process_transaction_with_stock_deduction_and_movement(): void
    {
        $stokAwal = $this->siomay->stok; // 48

        $payload = [
            'dibayar' => 50000,
            'items' => [
                [
                    'product_id' => $this->siomay->id,
                    'qty' => 2, // Total: 30000, Kembalian: 20000
                ],
            ],
        ];

        $response = $this->actingAs($this->kasirUser)->post('/kasir/transaksi', $payload);
        $response->assertRedirect(route('kasir.transaksi'));
        $response->assertSessionHas('transaksi_sukses');

        // Pastikan order tersimpan dengan status 'baru' agar masuk ke dapur (BR-05)
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->kasirUser->id,
            'total' => 30000,
            'dibayar' => 50000,
            'kembalian' => 20000,
            'status' => 'baru',
        ]);

        // Cek stok terpotong otomatis (BR-01)
        $this->siomay->refresh();
        $this->assertEquals($stokAwal - 2, $this->siomay->stok);

        // Cek tercatat di stock_movements jenis 'terjual' (BR-02)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->siomay->id,
            'jenis' => 'terjual',
            'qty' => -2,
            'saldo_setelah' => 46,
        ]);
    }

    /**
     * BR-07: Validasi stok transaksi (gagal jika stok tidak cukup)
     */
    public function test_kasir_cannot_process_transaction_if_stock_insufficient(): void
    {
        $payload = [
            'dibayar' => 200000,
            'items' => [
                [
                    'product_id' => $this->hakau->id,
                    'qty' => 10, // Hakau hanya punya stok 4
                ],
            ],
        ];

        $response = $this->actingAs($this->kasirUser)->post('/kasir/transaksi', $payload);
        $response->assertSessionHasErrors('items');
        $this->assertEquals(4, $this->hakau->fresh()->stok); // Stok tidak boleh berkurang
    }

    /**
     * FR-13: Validasi pembayaran kurang
     */
    public function test_kasir_cannot_process_transaction_if_payment_insufficient(): void
    {
        $payload = [
            'dibayar' => 20000, // Total: 30000, uang kurang
            'items' => [
                [
                    'product_id' => $this->siomay->id,
                    'qty' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->kasirUser)->post('/kasir/transaksi', $payload);
        $response->assertSessionHasErrors('dibayar');
    }

    /**
     * FR-10, BR-03: Kasir dapat melihat cek stok
     */
    public function test_kasir_can_view_cek_stok(): void
    {
        $response = $this->actingAs($this->kasirUser)->get('/kasir/cek-stok');
        $response->assertStatus(200);
        $response->assertSee('Cek Stok');
        $response->assertSee('Siomay Ayam');
        $response->assertSee('Hakau Udang');
        $response->assertSee('Aman');
        $response->assertSee('Menipis');
    }

    /**
     * FR-15: Kasir dapat melihat riwayat transaksinya
     */
    public function test_kasir_can_view_riwayat_saya(): void
    {
        $order = Order::create([
            'kode' => 'TRX-0099',
            'waktu' => now(),
            'user_id' => $this->kasirUser->id,
            'total' => 15000,
            'dibayar' => 20000,
            'kembalian' => 5000,
            'status' => 'selesai',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->siomay->id,
            'qty' => 1,
            'harga_satuan' => 15000,
            'subtotal' => 15000,
        ]);

        $response = $this->actingAs($this->kasirUser)->get('/kasir/riwayat');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Saya');
        $response->assertSee('TRX-0099');
        $response->assertSee('Cetak Ulang Struk');
    }

    /**
     * FR-14, FR-15, BR-08, BR-09: Cetak struk dan cetak ulang
     */
    public function test_kasir_can_print_and_reprint_receipt(): void
    {
        $order = Order::create([
            'kode' => 'TRX-0100',
            'waktu' => now(),
            'user_id' => $this->kasirUser->id,
            'total' => 15000,
            'dibayar' => 20000,
            'kembalian' => 5000,
            'status' => 'selesai',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->siomay->id,
            'qty' => 1,
            'harga_satuan' => 15000,
            'subtotal' => 15000,
        ]);

        // 1. Cetak struk normal (FR-14, BR-08)
        $responsePrint = $this->actingAs($this->kasirUser)->get("/kasir/struk/{$order->id}");
        $responsePrint->assertStatus(200);
        $responsePrint->assertSee('MADAM DIMSUM');
        $responsePrint->assertSee('TRX-0100');
        $responsePrint->assertDontSee('[CETAK ULANG]');

        // 2. Cetak ulang struk (FR-15, BR-09)
        $responseReprint = $this->actingAs($this->kasirUser)->get("/kasir/struk/{$order->id}?ulang=1");
        $responseReprint->assertStatus(200);
        $responseReprint->assertSee('*** [CETAK ULANG] ***');
    }
}

