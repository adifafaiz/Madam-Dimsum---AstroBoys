<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'name' => 'Agus Dapur',
            'username' => 'agus',
            'email' => 'agus@madamdimsum.com',
            'role' => 'dapur',
            'password' => bcrypt('password123'),
        ]);

        User::create([
            'name' => 'Madam Owner',
            'username' => 'owner',
            'email' => 'owner@madamdimsum.com',
            'role' => 'owner',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Madam Dimsum');
        $response->assertSee('Produksi tertata, stok terjaga');
    }

    public function test_user_can_login_with_correct_role_via_api(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'agus',
            'password' => 'password123',
            'role' => 'dapur',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'username' => 'agus',
                'role' => 'dapur',
            ],
        ]);
    }

    public function test_login_fails_when_role_mismatches(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'agus',
            'password' => 'password123',
            'role' => 'kasir',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'agus',
            'password' => 'wrongpassword',
            'role' => 'dapur',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_user_can_login_via_web_form_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'agus',
            'password' => 'password123',
            'role' => 'dapur',
        ]);

        $response->assertRedirect('/dashboard/dapur');
        $this->assertAuthenticated();
    }

    public function test_deactivated_user_cannot_login_uat_10(): void
    {
        User::create([
            'name' => 'Dani Kasir',
            'username' => 'dani',
            'email' => 'dani@madamdimsum.com',
            'role' => 'kasir',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'dani',
            'password' => 'password123',
            'role' => 'kasir',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Owner.',
        ]);
    }

    public function test_cross_role_access_shows_akses_ditolak_fr_02(): void
    {
        $kasir = User::where('username', 'agus')->first(); // Agus is dapur
        $response = $this->actingAs($kasir)->get('/dashboard/owner');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Kembali ke Halaman Dapur');
    }
}
