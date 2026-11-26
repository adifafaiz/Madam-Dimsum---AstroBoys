<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kelola pengguna oleh Owner: tambah, edit, nonaktifkan, reset password (FR-29, BR-10, BR-11).
 */
class PenggunaTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $dani;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::create(['name' => 'Madam Owner', 'username' => 'owner', 'role' => 'owner', 'password' => bcrypt('password123'), 'is_active' => true]);
        $this->dani = User::create(['name' => 'Dani Kasir', 'username' => 'dani', 'role' => 'kasir', 'password' => bcrypt('password123'), 'is_active' => true]);
    }

    public function test_edit_nama_username_dan_peran(): void
    {
        $this->dani->createToken('lama');

        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}", [
            'name' => 'Dani Pratama', 'username' => 'dani.p', 'role' => 'dapur',
        ])->assertRedirect('/owner/pengguna')->assertSessionHas('success');

        $dani = $this->dani->fresh();
        $this->assertSame('Dani Pratama', $dani->name);
        $this->assertSame('dani.p', $dani->username);
        $this->assertSame('dapur', $dani->role);
        $this->assertSame(0, $dani->tokens()->count(), 'token lama dicabut saat peran berubah');

        $log = ActivityLog::where('aksi', 'ubah_pengguna')->sole();
        $this->assertSame('kasir', $log->data_lama['role']);
        $this->assertSame('dapur', $log->data_baru['role']);
    }

    public function test_edit_username_harus_unik_dan_valid(): void
    {
        User::create(['name' => 'Rina', 'username' => 'rina', 'role' => 'kasir', 'password' => bcrypt('x123456'), 'is_active' => true]);

        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}", [
            'name' => 'Dani', 'username' => 'rina', 'role' => 'kasir',
        ])->assertSessionHasErrors(['username' => 'Username sudah digunakan, silakan pilih username lain.']);

        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}", [
            'name' => 'Dani', 'username' => 'dani kasir', 'role' => 'kasir',
        ])->assertSessionHasErrors('username');

        // Username milik sendiri boleh dipakai lagi
        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}", [
            'name' => 'Dani K', 'username' => 'dani', 'role' => 'kasir',
        ])->assertSessionHasNoErrors();
    }

    public function test_owner_tidak_bisa_mengubah_peran_sendiri(): void
    {
        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->owner->id}", [
            'name' => 'Madam', 'username' => 'owner', 'role' => 'kasir',
        ])->assertSessionHasErrors('role');

        $this->assertSame('owner', $this->owner->fresh()->role);
    }

    public function test_owner_terakhir_tidak_bisa_diturunkan_atau_dinonaktifkan(): void
    {
        $owner2 = User::create(['name' => 'Owner Dua', 'username' => 'owner2', 'role' => 'owner', 'password' => bcrypt('x123456'), 'is_active' => true]);
        // owner utama dinonaktifkan oleh owner2 -> owner2 jadi satu-satunya owner aktif
        $this->owner->update(['is_active' => false]);

        $this->actingAs($owner2)->post("/owner/pengguna/{$owner2->id}", [
            'name' => 'Owner Dua', 'username' => 'owner2', 'role' => 'dapur',
        ])->assertSessionHasErrors('role');
        $this->assertSame('owner', $owner2->fresh()->role);
    }

    public function test_reset_password_wajib_diisi_dan_tidak_ditampilkan(): void
    {
        $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}/reset", [])
            ->assertSessionHasErrors(['password' => 'Password wajib diisi.']);
        $this->assertTrue(Hash::check('password123', $this->dani->fresh()->password), 'tidak boleh diam-diam diisi default');

        $this->dani->createToken('lama');
        $response = $this->actingAs($this->owner)->post("/owner/pengguna/{$this->dani->id}/reset", ['password' => 'Rahasia789']);
        $response->assertSessionHas('success');
        $this->assertStringNotContainsString('Rahasia789', session('success'));

        $this->assertTrue(Hash::check('Rahasia789', $this->dani->fresh()->password));
        $this->assertSame(0, $this->dani->tokens()->count());
    }

    public function test_tambah_pengguna_validasi_password_dan_username(): void
    {
        $this->actingAs($this->owner)->post('/owner/pengguna', [
            'name' => 'Siti', 'username' => 'si', 'password' => '123', 'role' => 'kasir',
        ])->assertSessionHasErrors(['username', 'password']);

        $this->actingAs($this->owner)->post('/owner/pengguna', [
            'name' => 'Siti', 'username' => '  siti ', 'password' => 'siti12345', 'role' => 'dapur',
        ])->assertSessionHas('success');
        $this->assertSame('dapur', User::where('username', 'siti')->value('role'));
    }

    public function test_halaman_pengguna_tanpa_password_bawaan(): void
    {
        $this->actingAs($this->owner)->get('/owner/pengguna')
            ->assertOk()
            ->assertDontSee('value="password123"', false)
            ->assertSee(route('owner.pengguna.update', $this->dani), false)
            ->assertSee('Edit');
    }

    public function test_kasir_tidak_bisa_edit_pengguna(): void
    {
        $this->actingAs($this->dani)->post("/owner/pengguna/{$this->dani->id}", [
            'name' => 'Dani', 'username' => 'dani', 'role' => 'owner',
        ])->assertForbidden();
        $this->assertSame('kasir', $this->dani->fresh()->role);
    }
}
