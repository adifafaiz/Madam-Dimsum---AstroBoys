<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        // Cari user di database
        $user = User::where('username', $username)->first();

        if (!$user) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username "' . $username . '" tidak ditemukan.',
                ], 404);
            }

            return back()->withInput()->withErrors([
                'username' => 'Username "' . $username . '" tidak ditemukan.',
            ]);
        }

        // Cek status keaktifan akun (BR-10, FR-29, UAT-10)
        if (!$user->is_active) {
            $msg = 'Akun Anda telah dinonaktifkan. Silakan hubungi Owner.';

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 403);
            }

            return back()->withInput()->withErrors([
                'username' => $msg,
            ]);
        }

        // Cek kecocokan role akun dengan role yang dipilih di tampilan login
        if (strtolower($user->role) !== $selectedRole) {
            $userRoleLabel = ucfirst($user->role);
            $selectedRoleLabel = ucfirst($selectedRole);
            $msg = "Akun ini terdaftar sebagai peran {$userRoleLabel}, bukan {$selectedRoleLabel}. Silakan klik tombol '{$userRoleLabel}' untuk masuk.";

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return back()->withInput()->withErrors([
                'role' => $msg,
            ]);
        }

        // Verifikasi password
        if (!Hash::check($password, $user->password)) {
            $msg = 'Password yang dimasukkan salah.';

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 401);
            }

            return back()->withInput()->withErrors([
                'password' => $msg,
            ]);
        }

        // Login ke session jika sesi tersedia (web session)
        Auth::login($user, $remember);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $role = strtolower($user->role);
        $redirectUrl = route('dashboard', ['role' => $role]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Selamat datang, ' . $user->name,
                'redirect_url' => $redirectUrl,
                'user' => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'role'     => $user->role,
                    'email'    => $user->email,
                ],
            ]);
        }

        return redirect()->intended($redirectUrl)->with('success', 'Selamat datang, ' . $user->name . '!');
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
        Auth::logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}

