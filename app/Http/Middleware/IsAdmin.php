<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Jika user belum login atau BUKAN admin, lempar keluar
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'AKSES DITOLAK: Halaman ini khusus Kepala Perpustakaan.');
        }

        return $next($request);
    }
}
