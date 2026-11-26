<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes render halaman Owner yang belum tercakup tes lain
 * (Pengguna, Pencadangan, Riwayat Transaksi + panel detail).
 */
class OwnerHalamanTest extends TestCase
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
            'role' => 'owner',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $this->kasir = User::create([
            'name' => 'Rina Kasir',
            'username' => 'rina',
            'role' => 'kasir',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
    }

    private function buatOrder(string $kode, string $status, ?string $alasan = null): Order
    {
        $produk = Product::firstOrCreate(['nama' => 'Siomay Ayam'], [
            'kategori' => 'Dimsum Kukus', 'satuan' => 'Porsi', 'harga' => 15000,
            'stok' => 40, 'stok_minimum' => 10, 'is_active' => true,
        ]);

        $order = Order::create([
            'kode' => $kode, 'waktu' => now(), 'user_id' => $this->kasir->id,
            'total' => 30000, 'dibayar' => 50000, 'kembalian' => 20000,
            'status' => $status, 'alasan_batal' => $alasan,
        ]);

        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $produk->id,
            'qty' => 2, 'harga_satuan' => 15000, 'subtotal' => 30000,
        ]);

        return $order;
    }

    public function test_halaman_pengguna_tampil(): void
    {
        $this->actingAs($this->owner)->get('/owner/pengguna')
            ->assertOk()
            ->assertSee('Daftar Akun Pengguna')
            ->assertSee('Rina Kasir')
            ->assertSee('Nonaktifkan')
            ->assertSee('Hak Akses Peran');
    }

    public function test_halaman_pencadangan_tampil(): void
    {
        $this->actingAs($this->owner)->get('/owner/pencadangan')
            ->assertOk()
            ->assertSee('Cadangkan Sekarang')
            ->assertSee('Jadwal Otomatis')
            ->assertSee('Riwayat Cadangan');
    }

    public function test_riwayat_transaksi_menampilkan_detail_dan_status_asli(): void
    {
        $this->buatOrder('TRX-0101', 'diproses');

        $this->actingAs($this->owner)->get('/owner/riwayat-transaksi?selected=TRX-0101')
            ->assertOk()
            ->assertSee('TRX-0101')
            ->assertSee('Diproses')
            ->assertSee('Rp 50.000')   // dibayar
            ->assertSee('Rp 20.000')   // kembalian
            ->assertSee('Batalkan Transaksi');
    }

    public function test_riwayat_transaksi_batal_menampilkan_alasan(): void
    {
        $this->buatOrder('TRX-0102', 'batal', 'Pelanggan membatalkan');

        $this->actingAs($this->owner)->get('/owner/riwayat-transaksi?selected=TRX-0102')
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertSee('Pelanggan membatalkan');
    }

    public function test_riwayat_transaksi_kosong_tampil_ramah(): void
    {
        $this->actingAs($this->owner)->get('/owner/riwayat-transaksi')
            ->assertOk()
            ->assertSee('Belum ada transaksi');
    }
}
