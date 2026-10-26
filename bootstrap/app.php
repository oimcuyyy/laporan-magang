<?php

use App\Http\Middleware\EnsureApiAuthenticated;
use App\Http\Middleware\ProtectPresentationData;
use App\Http\Middleware\WebProtectiveFirewall;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan Web Application Firewall (WAF) secara global untuk melindungi semua request
        $middleware->append(WebProtectiveFirewall::class);

        // Alias Middleware untuk Keamanan
        $middleware->alias([
            'api.auth' => EnsureApiAuthenticated::class,
            'presentation.protect' => ProtectPresentationData::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
