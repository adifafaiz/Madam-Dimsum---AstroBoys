<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Keamanan API: token Sanctum, middleware peran, pesan login umum,
 * throttle login, dan endpoint demo-accounts (CHECKLIST 2.5–2.11, 7.7, 7.11, 7.12, 8.3, 8.7, UAT-03).
 */
class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_LOGIN_ERROR = [
        'success' => false,
        'message' => 'Username atau password salah.',
        'data' => null,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            ['Madam Owner', 'owner', 'owner'],
            ['Rina Kasir', 'rina', 'kasir'],
            ['Agus Dapur', 'agus', 'dapur'],
        ] as [$name, $username, $role]) {
            User::create([
                'name' => $name,
                'username' => $username,
                'role' => $role,
                'password' => 'password123',
                'is_active' => true,
            ]);
        }
    }

    /** Login lewat API dan kembalikan token Bearer. */
    private function loginToken(string $username, string $role): string
    {
        $response = $this->postJson('/api/login', [
            'username' => $username,
            'password' => 'password123',
            'role' => $role,
        ]);
        $response->assertOk();

        return $response->json('data.token');
    }

    /** Request API dengan token; guard di-reset supaya user dari request sebelumnya tidak terbawa. */
    private function api(string $method, string $uri, ?string $token = null, array $data = []): TestResponse
    {
        $this->app['auth']->forgetGuards();
        $headers = $token ? ['Authorization' => "Bearer {$token}"] : [];

        return $this->json($method, $uri, $data, $headers);
    }

    public function test_login_api_mengembalikan_token_yang_bisa_dipakai(): void
    {
        $token = $this->loginToken('rina', 'kasir');

        $this->api('GET', '/api/user', $token)
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Data pengguna yang sedang login.',
                'data' => [
                    'id' => User::where('username', 'rina')->value('id'),
                    'name' => 'Rina Kasir',
                    'username' => 'rina',
                    'role' => 'kasir',
                ],
            ]);
    }

    public function test_pesan_gagal_login_sama_untuk_username_salah_peran_salah_dan_password_salah(): void
    {
        $kasus = [
            ['username' => 'tidakada', 'password' => 'password123', 'role' => 'kasir'],
            ['username' => 'rina', 'password' => 'password123', 'role' => 'owner'],
            ['username' => 'rina', 'password' => 'salah', 'role' => 'kasir'],
        ];

        foreach ($kasus as $input) {
            $this->postJson('/api/login', $input)
                ->assertStatus(401)
                ->assertExactJson(self::GENERIC_LOGIN_ERROR);
        }

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_akun_nonaktif_dengan_password_salah_tetap_mendapat_pesan_umum(): void
    {
        User::where('username', 'rina')->update(['is_active' => false]);

        $this->postJson('/api/login', ['username' => 'rina', 'password' => 'salah', 'role' => 'kasir'])
            ->assertStatus(401)
            ->assertExactJson(self::GENERIC_LOGIN_ERROR);
    }

    public function test_uat_03_kasir_memanggil_api_owner_ditolak_403(): void
    {
        $token = $this->loginToken('rina', 'kasir');
        $forbidden = [
            'success' => false,
            'message' => 'Anda tidak memiliki akses ke fitur ini.',
            'data' => null,
        ];

        $this->api('GET', '/api/owner/dashboard', $token)->assertStatus(403)->assertExactJson($forbidden);
        $this->api('GET', '/api/owner/produk', $token)->assertStatus(403)->assertExactJson($forbidden);
        $this->api('POST', '/api/owner/produk', $token, [
            'nama' => 'Produk Ilegal',
            'satuan' => 'porsi',
            'harga' => 10000,
            'stok' => 10,
            'stok_minimum' => 5,
            'kategori' => 'Dimsum Kukus',
        ])->assertStatus(403)->assertExactJson($forbidden);

        $this->assertDatabaseMissing('products', ['nama' => 'Produk Ilegal']);
        $this->assertSame(0, Product::count());
    }

    public static function matriksPeran(): array
    {
        $endpoint = [
            'owner' => '/api/owner/dashboard',
            'kasir' => '/api/kasir/cek-stok',
            'dapur' => '/api/dapur/stok',
        ];
        $akun = ['owner' => 'owner', 'kasir' => 'rina', 'dapur' => 'agus'];

        $cases = [];
        foreach ($akun as $peran => $username) {
            foreach ($endpoint as $grup => $uri) {
                $cases["{$peran} -> {$grup}"] = [$username, $peran, $uri, $peran === $grup ? 200 : 403];
            }
        }

        return $cases;
    }

    #[DataProvider('matriksPeran')]
    public function test_matriks_akses_api_lintas_peran(string $username, string $peran, string $uri, int $expected): void
    {
        $token = $this->loginToken($username, $peran);

        $response = $this->api('GET', $uri, $token)->assertStatus($expected);
        $this->assertSame($expected === 200, $response->json('success'));
    }

    public function test_tamu_tanpa_token_mendapat_401_json_bukan_redirect(): void
    {
        foreach (['/api/owner/dashboard', '/api/kasir/cek-stok', '/api/dapur/stok', '/api/user'] as $uri) {
            // Tanpa header Accept: application/json pun tetap JSON
            $this->app['auth']->forgetGuards();
            $this->get($uri)
                ->assertStatus(401)
                ->assertExactJson([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.',
                    'data' => null,
                ]);
        }

        $this->api('POST', '/api/logout')->assertStatus(401);
    }

    public function test_logout_api_mencabut_token(): void
    {
        $token = $this->loginToken('agus', 'dapur');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->api('POST', '/api/logout', $token)
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Berhasil logout.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->api('GET', '/api/dapur/stok', $token)->assertStatus(401);
    }

    public function test_token_akun_yang_dinonaktifkan_langsung_ditolak(): void
    {
        $token = $this->loginToken('rina', 'kasir');
        $this->api('GET', '/api/kasir/cek-stok', $token)->assertOk();

        User::where('username', 'rina')->update(['is_active' => false]);

        $this->api('GET', '/api/kasir/cek-stok', $token)
            ->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Owner.']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_session_web_akun_nonaktif_dikeluarkan(): void
    {
        $rina = User::where('username', 'rina')->first();
        $rina->update(['is_active' => false]);

        $this->actingAs($rina)->get('/kasir/transaksi')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_dibatasi_5_percobaan_per_menit(): void
    {
        $input = ['username' => 'rina', 'password' => 'salah', 'role' => 'kasir'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', $input)->assertStatus(401);
        }

        $this->postJson('/api/login', $input)
            ->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
            ]);

        // Password benar pun ditolak selama masih dibatasi
        $this->postJson('/api/login', ['username' => 'rina', 'password' => 'password123', 'role' => 'kasir'])
            ->assertStatus(429);
    }

    public function test_demo_accounts_tidak_tersedia_di_luar_lingkungan_local(): void
    {
        $this->assertFalse(app()->environment('local'));

        $this->getJson('/api/demo-accounts')
            ->assertStatus(404)
            ->assertJson(['success' => false])
            ->assertJsonMissing(['password' => 'password123']);
    }
}
