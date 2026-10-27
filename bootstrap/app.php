<?php

use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\WebProtectiveFirewall;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
        // Percayai semua reverse proxy Vercel untuk HTTPS detection
        $middleware->trustProxies(at: '*');

        // Daftarkan Web Application Firewall (WAF) secara global untuk melindungi semua request
        $middleware->append(WebProtectiveFirewall::class);

        // Alias Middleware untuk Keamanan
        $middleware->alias([
            'api.auth' => EnsureApiAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Lempar ke 401 dan tampilkan peringatan jika ada yang mengubah-ubah link URL web
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            // Biarkan browser aset statis jika tidak sengaja 404
            if ($request->is(['build/*', 'css/*', 'js/*', 'images/*', 'vendor/*', 'storage/*', '*.ico', '*.png', '*.jpg', '*.jpeg', '*.webp', '*.svg', '*.css', '*.js', '*.map'])) {
                return null;
            }

            return WebProtectiveFirewall::renderTamperedUrlResponse(
                $request,
                'Alamat tautan atau parameter ID yang Anda masukkan tidak terdaftar di sistem atau telah diubah-ubah.'
            );
        });

        // Tangkap upaya manipulasi lintas entitas (IDOR)
        $exceptions->render(function (AccessDeniedHttpException|AuthorizationException $e, Request $request) {
            return WebProtectiveFirewall::renderTamperedUrlResponse(
                $request,
                'Aksi manipulasi lintas data atau hak akses pada sistem diblokir.'
            );
        });
    })->create();
