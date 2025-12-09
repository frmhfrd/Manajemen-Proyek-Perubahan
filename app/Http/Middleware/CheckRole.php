<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user login?
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Cek apakah role user SAMA dengan role yang diminta?
        // Kita izinkan 'admin' untuk mengakses segalanya (Super User)
        if (Auth::user()->role == 'admin') {
            return $next($request);
        }

        // 3. Jika user bukan admin, cek apakah dia punya role yang sesuai
        if (Auth::user()->role == $role) {
            return $next($request);
        }

        // 4. Jika tidak cocok, tampilkan error 403 (Forbidden)
        abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk halaman ini.');
    }
}
