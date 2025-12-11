<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Cek Role (Pastikan kolom 'role' ada di tabel users Anda)
        if (Auth::user()->role !== $role) {
            abort(403, 'Akses Ditolak: Anda bukan ' . ucfirst($role));
        }

        return $next($request);
    }
}
