<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\KioskGuard;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\IsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Pengecualian CSRF (Untuk Midtrans nanti)
        $middleware->validateCsrfTokens(except: [
            'api/midtrans-callback',
            'midtrans-callback',
        ]);

        // 2. Daftar Alias Middleware (Digabung jadi satu biar rapi)
        $middleware->alias([
            'kiosk.guard' => KioskGuard::class,
            'role'        => RoleMiddleware::class, // <--- Pastikan ini merujuk ke RoleMiddleware
            'admin'       =>IsAdmin::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
