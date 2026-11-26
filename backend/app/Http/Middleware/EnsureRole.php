<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route sesuai peran (users.role). Pemakaian: ->middleware('role:owner')
 * atau 'role:kasir,owner' untuk beberapa peran.
 *
 * Dipasang setelah middleware auth / auth:sanctum, sehingga user pasti sudah login.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $isApi = $request->is('api/*');

        if (! $user) {
            return $isApi
                ? $this->json('Silakan login terlebih dahulu.', 401)
                : redirect()->route('login');
        }

        // Akun yang dinonaktifkan owner langsung kehilangan akses (BR-10, UAT-10)
        if (! $user->is_active) {
            $token = $user->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            if ($isApi) {
                return $this->json('Akun Anda telah dinonaktifkan. Silakan hubungi Owner.', 401);
            }

            Auth::guard('web')->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Owner.',
            ]);
        }

        $allowed = array_map('strtolower', $roles);
        if (! in_array(strtolower($user->role), $allowed, true)) {
            if ($isApi) {
                return $this->json('Anda tidak memiliki akses ke fitur ini.', 403);
            }
            abort(403);
        }

        return $next($request);
    }

    private function json(string $message, int $status): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
