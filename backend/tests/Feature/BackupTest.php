<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pencadangan sungguhan: buat file, unduh, pulihkan, jadwal otomatis (FR-30, BR-11).
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Product $siomay;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->owner = User::create(['name' => 'Madam Owner', 'username' => 'owner', 'role' => 'owner', 'password' => bcrypt('password123'), 'is_active' => true]);
        $this->siomay = Product::create(['nama' => 'Siomay Ayam', 'kategori' => 'Dimsum Kukus', 'satuan' => 'Porsi', 'harga' => 15000, 'stok' => 40, 'stok_minimum' => 10, 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cadangkan_sekarang_membuat_file_sungguhan(): void
    {
        $this->actingAs($this->owner)->post('/owner/pencadangan')
            ->assertRedirect('/owner/pencadangan')
            ->assertSessionHas('success');

        $backup = Backup::sole();
        $this->assertSame('berhasil', $backup->status);
        $this->assertSame('manual', $backup->jenis);
        Storage::disk('local')->assertExists($backup->file_path);
        $this->assertGreaterThan(0, $backup->ukuran_kb);

        $isi = json_decode(Storage::disk('local')->get($backup->file_path), true);
        $this->assertSame('dimsumku-backup', $isi['format']);
        $this->assertSame('Siomay Ayam', $isi['tabel']['products'][0]['nama']);
        $this->assertArrayHasKey('password', $isi['tabel']['users'][0]); // hash ikut, supaya login bisa dipulihkan
    }

    public function test_unduh_cadangan(): void
    {
        $backup = app(BackupService::class)->buat('manual');

        $response = $this->actingAs($this->owner)->get("/owner/pencadangan/{$backup->id}/unduh");
        $response->assertOk();
        $this->assertStringContainsString(basename($backup->file_path), $response->headers->get('content-disposition'));
    }

    public function test_unduh_file_hilang_ditolak_dengan_pesan(): void
    {
        $backup = app(BackupService::class)->buat('manual');
        Storage::disk('local')->delete($backup->file_path);

        $this->actingAs($this->owner)->get("/owner/pencadangan/{$backup->id}/unduh")
            ->assertRedirect('/owner/pencadangan')
            ->assertSessionHasErrors('backup');
    }

    public function test_pulihkan_mengembalikan_data_dan_membuat_cadangan_pengaman(): void
    {
        $backup = app(BackupService::class)->buat('manual');

        // Perubahan setelah cadangan dibuat
        $this->siomay->update(['stok' => 3]);
        Product::create(['nama' => 'Produk Baru', 'kategori' => 'X', 'satuan' => 'Porsi', 'harga' => 1000, 'stok' => 5, 'stok_minimum' => 1, 'is_active' => true]);

        $this->actingAs($this->owner)->post("/owner/pencadangan/{$backup->id}/pulihkan", ['konfirmasi' => 'PULIHKAN'])
            ->assertRedirect('/owner/pencadangan')
            ->assertSessionHas('success');

        $this->assertSame(40, $this->siomay->fresh()->stok);
        $this->assertNull(Product::where('nama', 'Produk Baru')->first());

        // Cadangan pengaman berisi kondisi sebelum dipulihkan
        $pengaman = Backup::where('id', '!=', $backup->id)->sole();
        $isi = json_decode(Storage::disk('local')->get($pengaman->file_path), true);
        $this->assertCount(2, $isi['tabel']['products']);

        // BR-11
        $this->assertTrue(ActivityLog::where('aksi', 'pulihkan_cadangan')->where('user_id', $this->owner->id)->exists());
    }

    public function test_pulihkan_tanpa_konfirmasi_ditolak(): void
    {
        $backup = app(BackupService::class)->buat('manual');
        $this->siomay->update(['stok' => 3]);

        $this->actingAs($this->owner)->post("/owner/pencadangan/{$backup->id}/pulihkan", ['konfirmasi' => 'ya'])
            ->assertSessionHasErrors('konfirmasi');

        $this->assertSame(3, $this->siomay->fresh()->stok);
        $this->assertSame(1, Backup::count());
    }

    public function test_pulihkan_file_rusak_tidak_mengubah_data(): void
    {
        $backup = app(BackupService::class)->buat('manual');
        Storage::disk('local')->put($backup->file_path, '{"bukan":"cadangan"}');
        $this->siomay->update(['stok' => 3]);

        $this->actingAs($this->owner)->post("/owner/pencadangan/{$backup->id}/pulihkan", ['konfirmasi' => 'PULIHKAN'])
            ->assertSessionHasErrors('backup');

        $this->assertSame(3, $this->siomay->fresh()->stok);
    }

    public function test_kasir_tidak_bisa_unduh_atau_pulihkan(): void
    {
        $kasir = User::create(['name' => 'Rina', 'username' => 'rina', 'role' => 'kasir', 'password' => bcrypt('password123'), 'is_active' => true]);
        $backup = app(BackupService::class)->buat('manual');

        $this->actingAs($kasir)->get("/owner/pencadangan/{$backup->id}/unduh")->assertForbidden();
        $this->actingAs($kasir)->post("/owner/pencadangan/{$backup->id}/pulihkan", ['konfirmasi' => 'PULIHKAN'])->assertForbidden();
    }

    public function test_jadwal_divalidasi(): void
    {
        $this->actingAs($this->owner)->post('/owner/pencadangan/schedule', ['frekuensi' => 'Tahunan', 'jam' => '25:00'])
            ->assertSessionHasErrors(['frekuensi', 'jam']);

        $this->actingAs($this->owner)->post('/owner/pencadangan/schedule', ['frekuensi' => 'Mingguan', 'jam' => '03:00'])
            ->assertSessionHas('success');
        $this->assertSame('Mingguan', Setting::where('kunci', 'backup_frekuensi')->value('nilai'));
    }

    public function test_backup_otomatis_harian_sekali_per_jadwal(): void
    {
        Setting::create(['kunci' => 'backup_frekuensi', 'nilai' => 'Harian']);
        Setting::create(['kunci' => 'backup_jam', 'nilai' => '02:00']);
        $service = app(BackupService::class);

        Carbon::setTestNow(Carbon::parse('2026-11-26 02:00:30'));
        $this->assertNotNull($service->jalankanOtomatisBilaJatuhTempo());

        Carbon::setTestNow(Carbon::parse('2026-11-26 14:00:00'));
        $this->assertNull($service->jalankanOtomatisBilaJatuhTempo(), 'tidak boleh dobel di hari yang sama');

        Carbon::setTestNow(Carbon::parse('2026-11-27 01:59:00'));
        $this->assertNull($service->jalankanOtomatisBilaJatuhTempo(), 'belum jam 02:00');

        Carbon::setTestNow(Carbon::parse('2026-11-27 02:01:00'));
        $backup = $service->jalankanOtomatisBilaJatuhTempo();
        $this->assertSame('otomatis', $backup->jenis);
        $this->assertSame(2, Backup::where('jenis', 'otomatis')->count());

        $this->assertSame('2026-11-28 02:00', $service->jadwalBerikutnya()->format('Y-m-d H:i'));
    }

    public function test_perintah_artisan_backup_otomatis(): void
    {
        $this->artisan('backup:otomatis')->assertSuccessful();
        $this->assertSame(1, Backup::where('jenis', 'otomatis')->where('status', 'berhasil')->count());
    }

    public function test_halaman_pencadangan_tanpa_tanggal_palsu(): void
    {
        $this->actingAs($this->owner)->get('/owner/pencadangan')
            ->assertOk()
            ->assertDontSee('24 Nov 2026, 02:00')
            ->assertSee('Belum ada cadangan yang berhasil');

        $backup = app(BackupService::class)->buat('manual');
        $this->actingAs($this->owner)->get('/owner/pencadangan')
            ->assertSee(route('owner.pencadangan.unduh', $backup))
            ->assertSee(route('owner.pencadangan.pulihkan', $backup));
    }
}
