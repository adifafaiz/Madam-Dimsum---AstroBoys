<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Madam Owner',
            'username' => 'owner',
            'email' => 'owner@madamdimsum.com',
            'role' => 'owner',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'nama' => 'Pemasok A',
            'kontak' => '0812-3456-7890',
        ]);

        $this->product = Product::create([
            'nama' => 'Hakau Udang',
            'satuan' => 'Porsi',
            'harga' => 18000,
            'stok' => 4,
            'stok_minimum' => 10,
            'kategori' => 'Dimsum Kukus',
            'supplier_id' => $this->supplier->id,
            'is_active' => true,
        ]);
    }

    public function test_owner_can_view_catat_stok_masuk_page(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/stok-masuk');
        $response->assertStatus(200);
        $response->assertSee('Catat Stok Masuk');
        $response->assertSee('Stok Setelah Disimpan');
        $response->assertSee('Hakau Udang');
    }

    public function test_owner_can_store_stok_masuk_and_update_inventory(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/stok-masuk', [
            'tanggal' => '2026-11-24',
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 20,
                ],
            ],
            'catatan' => 'Kiriman rutin mingguan',
        ]);

        $response->assertRedirect('/owner/produk');

        // Stok 4 + 20 = 24
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stok' => 24,
        ]);

        // Verifikasi stock_movement tercatat dengan jenis masuk (BR-02)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'jenis' => 'masuk',
            'qty' => 20,
            'saldo_setelah' => 24,
        ]);
    }

    public function test_owner_can_view_pemasok_and_kebutuhan_restock(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/pemasok');
        $response->assertStatus(200);
        $response->assertSee('Daftar Pemasok');
        $response->assertSee('Pemasok A');
        $response->assertSee('Kebutuhan Stok');
        $response->assertSee('Salin Daftar Pesanan');
        $response->assertSee('Kirim via WhatsApp');
    }

    public function test_owner_can_store_new_pemasok(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/pemasok', [
            'nama' => 'Pemasok D Mantau',
            'kontak' => '0877-1122-3344',
        ]);

        $response->assertRedirect('/owner/pemasok');
        $this->assertDatabaseHas('suppliers', [
            'nama' => 'Pemasok D Mantau',
            'kontak' => '0877-1122-3344',
        ]);
    }

    public function test_owner_can_view_laporan_penjualan(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/laporan');
        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Total Penjualan');
        $response->assertSee('Tren Penjualan');
        $response->assertSee('Produk Terlaris');
        $response->assertSee('Perbandingan Periode');
    }

    public function test_owner_can_export_laporan_excel(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/laporan/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}

