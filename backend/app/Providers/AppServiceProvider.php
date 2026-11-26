<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batasi percobaan login: 5 kali per menit per kombinasi username + IP (NFR keamanan)
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('username')).'|'.$request->ip();

            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                $message = 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.';

                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'data' => null,
                    ], 429, $headers);
                }

                return back()->withInput($request->only('username', 'role'))
                    ->withErrors(['username' => $message]);
            });
        });
    }
}
