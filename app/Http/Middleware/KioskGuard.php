<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KioskGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Daftar IP yang Diizinkan (Whitelist)
        // Ambil dari file .env, atau default ke localhost (127.0.0.1)
        $allowedIps = explode(',', env('KIOSK_ALLOWED_IPS', '127.0.0.1'));

        // 2. Cek IP Pengunjung
        if (!in_array($request->ip(), $allowedIps)) {
            // Jika IP tidak dikenal, tolak akses!
            abort(403, 'Akses Ditolak: Fitur ini hanya bisa diakses melalui Anjungan Mandiri Perpustakaan.');
        }

        return $next($request);
    }
}
