<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua error di /api/* dikirim sebagai JSON, bukan halaman HTML / redirect
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );

        $jsonError = fn (string $message, int $status) => response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($jsonError) {
            if ($request->is('api/*')) {
                return $jsonError('Silakan login terlebih dahulu.', 401);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($jsonError) {
            if ($request->is('api/*')) {
                return $jsonError('Anda tidak memiliki akses ke fitur ini.', 403);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($jsonError) {
            if ($request->is('api/*')) {
                return $jsonError('Data atau alamat yang diminta tidak ditemukan.', 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($jsonError) {
            if ($request->is('api/*')) {
                return $jsonError('Terlalu banyak percobaan. Silakan coba lagi sebentar lagi.', 429);
            }
        });
    })->create();
