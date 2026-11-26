<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman & CRUD pemasok tanpa data palsu (FR-25, FR-26, BR-04).
 */
class PemasokTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::create(['name' => 'Madam Owner', 'username' => 'owner', 'role' => 'owner', 'password' => bcrypt('password123'), 'is_active' => true]);
    }

    private function produk(string $nama, int $stok, int $min, ?Supplier $sup = null): Product
    {
        return Product::create(['nama' => $nama, 'kategori' => 'Dimsum', 'satuan' => 'Porsi', 'harga' => 10000, 'stok' => $stok, 'stok_minimum' => $min, 'supplier_id' => $sup?->id, 'is_active' => true]);
    }

    public function test_tanpa_produk_menipis_tidak_ada_daftar_contoh(): void
    {
        Supplier::create(['nama' => 'Pemasok A', 'kontak' => '0813-9999-0000']);

        $this->actingAs($this->owner)->get('/owner/pemasok')
            ->assertOk()
            ->assertSee('Stok aman semua')
            ->assertDontSee('Hakau Udang')
            ->assertDontSee('24 Nov')
            ->assertDontSee('6281234567890')
            ->assertSee('Belum ada produk')
            ->assertSee('Belum pernah');
    }

    public function test_kebutuhan_dikelompokkan_per_pemasok_dengan_wa_asli(): void
    {
        $a = Supplier::create(['nama' => 'Pemasok A', 'kontak' => '0812-3456-7890']);
        $b = Supplier::create(['nama' => 'Pemasok B', 'kontak' => null]);
        $this->produk('Hakau Udang', 4, 10, $a);
        $this->produk('Siomay Ayam', 50, 10, $a);   // aman, tidak masuk
        $this->produk('Lumpia Udang', 0, 8, $b);
        $this->produk('Mantau', 1, 5);              // tanpa pemasok

        $response = $this->actingAs($this->owner)->get('/owner/pemasok');
        $response->assertOk();

        $grup = $response->viewData('kebutuhanPerPemasok');
        $this->assertSame(['Pemasok A', 'Pemasok B', null], $grup->map(fn ($g) => $g['pemasok']?->nama)->all());

        $html = $response->getContent();
        // WA ke nomor pemasok A (0812 -> 62812) dengan isi pesanan Hakau 20 porsi
        $this->assertStringContainsString('https://wa.me/6281234567890?text=', $html);
        $this->assertStringContainsString(rawurlencode('- Hakau Udang: 20 porsi'), $html);
        $this->assertStringNotContainsString('Siomay Ayam: 20', $html);
        // Pemasok B tanpa kontak: tidak ada tombol WA, ada petunjuk
        $response->assertSee('Tambahkan nomor kontak Pemasok B');
        $response->assertSee('Belum ada pemasok');
        $response->assertSee('- Lumpia Udang: 16 porsi', false);
    }

    public function test_kiriman_terakhir_dari_stok_masuk(): void
    {
        $a = Supplier::create(['nama' => 'Pemasok A', 'kontak' => '0812-3456-7890']);
        $p = $this->produk('Bakpao Coklat', 30, 10, $a);
        StockMovement::create(['product_id' => $p->id, 'user_id' => $this->owner->id, 'supplier_id' => $a->id, 'tanggal' => '2026-11-20', 'jenis' => 'masuk', 'qty' => 24, 'saldo_setelah' => 30]);

        $this->actingAs($this->owner)->get('/owner/pemasok')->assertSee('20 Nov 2026');
    }

    public function test_edit_pemasok(): void
    {
        $a = Supplier::create(['nama' => 'Pemasok A', 'kontak' => '0812-3456-7890']);

        $this->actingAs($this->owner)->post("/owner/pemasok/{$a->id}", ['nama' => 'Pemasok A Jaya', 'kontak' => '+62 813 1111 2222'])
            ->assertRedirect('/owner/pemasok')->assertSessionHas('success');

        $this->assertSame('Pemasok A Jaya', $a->fresh()->nama);
        $this->assertSame('+62 813 1111 2222', $a->fresh()->kontak);
        $this->assertTrue(ActivityLog::where('aksi', 'ubah_pemasok')->exists());

        $this->actingAs($this->owner)->post("/owner/pemasok/{$a->id}", ['nama' => '', 'kontak' => 'telepon saya'])
            ->assertSessionHasErrors(['nama', 'kontak']);
    }

    public function test_kontak_boleh_kosong(): void
    {
        $this->actingAs($this->owner)->post('/owner/pemasok', ['nama' => 'Pasar Induk'])
            ->assertSessionHasNoErrors();
        $this->assertNull(Supplier::where('nama', 'Pasar Induk')->value('kontak'));
    }

    public function test_hapus_pemasok_yang_belum_dipakai(): void
    {
        $a = Supplier::create(['nama' => 'Pemasok Lama', 'kontak' => null]);

        $this->actingAs($this->owner)->post("/owner/pemasok/{$a->id}/hapus")->assertSessionHas('success');
        $this->assertNull(Supplier::find($a->id));
    }

    public function test_pemasok_yang_dipakai_tidak_bisa_dihapus(): void
    {
        $a = Supplier::create(['nama' => 'Pemasok A', 'kontak' => null]);
        $this->produk('Hakau Udang', 4, 10, $a);

        $this->actingAs($this->owner)->post("/owner/pemasok/{$a->id}/hapus")->assertSessionHasErrors('pemasok');
        $this->assertNotNull(Supplier::find($a->id));
    }

    public function test_kasir_tidak_bisa_ubah_pemasok(): void
    {
        $kasir = User::create(['name' => 'Rina', 'username' => 'rina', 'role' => 'kasir', 'password' => bcrypt('password123'), 'is_active' => true]);
        $a = Supplier::create(['nama' => 'Pemasok A', 'kontak' => null]);

        $this->actingAs($kasir)->post("/owner/pemasok/{$a->id}", ['nama' => 'X'])->assertForbidden();
        $this->actingAs($kasir)->post("/owner/pemasok/{$a->id}/hapus")->assertForbidden();
    }
}
