<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login Blade.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard', ['role' => Auth::user()->role]);
        }

        return view('auth.login');
    }

    /**
     * Proses login (mendukung request form Web dan API JSON).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|string|in:owner,kasir,dapur,Owner,Kasir,Dapur',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'role.required'     => 'Peran (Role) wajib dipilih.',
            'role.in'           => 'Pilihan peran tidak valid.',
        ]);

        $selectedRole = strtolower($validated['role']);
        $username     = trim($validated['username']);
        $password     = $validated['password'];
        $remember     = $request->boolean('remember');
        $isApi        = $request->wantsJson() || $request->is('api/*');

        $user = User::where('username', $username)->first();

        // Username tidak ada, password salah, atau peran tidak cocok -> satu pesan & satu kode
        // yang sama, supaya keberadaan username maupun peran akun tidak bocor.
        if (! $user
            || ! Hash::check($password, $user->password)
            || strtolower($user->role) !== $selectedRole) {
            return $this->loginGagal($request, $isApi, 'Username atau password salah.', 401);
        }

        // Status aktif baru dicek setelah password terbukti benar (BR-10, FR-29, UAT-10),
        // sehingga pesan ini hanya terlihat oleh pemilik akun.
        if (! $user->is_active) {
            return $this->loginGagal($request, $isApi, 'Akun Anda telah dinonaktifkan. Silakan hubungi Owner.', 403);
        }

        $role = strtolower($user->role);
        $redirectUrl = route('dashboard', ['role' => $role]);
        $userData = [
            'id'       => $user->id,
            'name'     => $user->name,
            'username' => $user->username,
            'role'     => $user->role,
        ];

        // API (frontend standalone): kembalikan token Sanctum, tanpa session
        if ($request->is('api/*')) {
            $token = $user->createToken('api-login', [$role])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang, ' . $user->name,
                'data'    => [
                    'token'        => $token,
                    'token_type'   => 'Bearer',
                    'user'         => $userData,
                    'redirect_url' => $redirectUrl,
                ],
            ]);
        }

        // Web (Blade): login ke session
        Auth::login($user, $remember);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($isApi) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang, ' . $user->name,
                'data'    => [
                    'user'         => $userData,
                    'redirect_url' => $redirectUrl,
                ],
            ]);
        }

        return redirect()->intended($redirectUrl)->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * Respons login gagal (JSON untuk API, kembali ke form untuk web).
     */
    private function loginGagal(Request $request, bool $isApi, string $msg, int $status)
    {
        if ($isApi) {
            return response()->json([
                'success' => false,
                'message' => $msg,
                'data'    => null,
            ], $status);
        }

        return back()->withInput($request->only('username', 'role'))->withErrors([
            'username' => $msg,
        ]);
    }

    /**
     * Dashboard preview peran yang sedang login.
     */
    public function dashboard(Request $request, $role)
    {
        $user = Auth::user();

        // FR-02: Menu dan halaman dibatasi sesuai peran; akses ke halaman lain menampilkan Akses Ditolak
        if ($user && strtolower($user->role) !== strtolower($role)) {
            return response()->view('errors.akses-ditolak', [
                'userRole' => strtolower($user->role),
                'attemptedRole' => strtolower($role),
            ], 403);
        }

        return view('dashboard', [
            'role' => $role,
            'user' => $user,
        ]);
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        // API: cabut token Sanctum yang sedang dipakai
        if ($request->is('api/*')) {
            $token = $request->user()?->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout.',
                'data'    => null,
            ]);
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout.',
                'data'    => null,
            ]);
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}

