<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $kasir;

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

        $this->kasir = User::create([
            'name' => 'Rina Kasir',
            'username' => 'rina',
            'email' => 'rina@madamdimsum.com',
            'role' => 'kasir',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
    }

    public function test_owner_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Penjualan hari ini');
        $response->assertSee('Penjualan 7 Hari Terakhir');
        $response->assertSee('Riwayat Pemesanan');
        $response->assertSee('Transaksi Terbaru');
    }

    public function test_owner_can_view_produk_stok_page(): void
    {
        Product::create([
            'nama' => 'Siomay Ayam',
            'satuan' => 'Porsi',
            'harga' => 15000,
            'stok' => 48,
            'stok_minimum' => 10,
            'kategori' => 'Dimsum Kukus',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/produk');
        $response->assertStatus(200);
        $response->assertSee('Produk & Stok');
        $response->assertSee('Siomay Ayam');
        $response->assertSee('Tambah Produk');
        $response->assertSee('Aman');
    }

    public function test_owner_can_store_new_product_with_initial_stock_movement(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/produk', [
            'nama' => 'Lumpia Kulit Tahu',
            'satuan' => 'Porsi',
            'harga' => 18000,
            'stok' => 30,
            'stok_minimum' => 10,
            'kategori' => 'Dimsum Goreng',
        ]);

        $response->assertRedirect('/owner/produk');
        $this->assertDatabaseHas('products', [
            'nama' => 'Lumpia Kulit Tahu',
            'stok' => 30,
        ]);

        // Verifikasi BR-01 & BR-02: Pergerakan stok awal otomatis dicatat
        $product = Product::where('nama', 'Lumpia Kulit Tahu')->first();
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'jenis' => 'stok_awal',
            'qty' => 30,
            'saldo_setelah' => 30,
        ]);
    }

    public function test_owner_can_update_product_and_record_stock_adjustment(): void
    {
        $product = Product::create([
            'nama' => 'Siomay Udang Edit',
            'satuan' => 'Porsi',
            'harga' => 16000,
            'stok' => 20,
            'stok_minimum' => 10,
            'kategori' => 'Dimsum Kukus',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post("/owner/produk/{$product->id}", [
            'nama' => 'Siomay Udang Spesial',
            'satuan' => 'Porsi',
            'harga' => 18000,
            'stok' => 25, // Penyesuaian stok +5
            'stok_minimum' => 12,
            'kategori' => 'Dimsum Kukus',
            'alasan_koreksi' => 'Ditemukan stok fisik tambahan',
        ]);

        $response->assertRedirect('/owner/produk');
        
        $product->refresh();
        $this->assertEquals('Siomay Udang Spesial', $product->nama);
        $this->assertEquals(18000, $product->harga);
        $this->assertEquals(25, $product->stok);
        $this->assertEquals(12, $product->stok_minimum);

        // Verifikasi tercatat di stock_movements (jenis: penyesuaian)
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'jenis' => 'penyesuaian',
            'qty' => 5,
            'saldo_setelah' => 25,
        ]);
    }

    public function test_owner_can_delete_product(): void
    {
        $product = Product::create([
            'nama' => 'Pangsit Goreng Testing',
            'satuan' => 'Porsi',
            'harga' => 12000,
            'stok' => 10,
            'stok_minimum' => 5,
            'kategori' => 'Dimsum Goreng',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->post("/owner/produk/{$product->id}/delete");
        $response->assertRedirect('/owner/produk');

        $product->refresh();
        $this->assertFalse((bool) $product->is_active);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->owner->id,
            'aksi' => 'hapus_produk',
        ]);
    }

    public function test_non_owner_forbidden_from_owner_routes(): void
    {
        $response = $this->actingAs($this->kasir)->get('/owner/dashboard');
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->kasir)->get('/owner/produk');
        $response2->assertStatus(403);
    }
}

