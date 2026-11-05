<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CashierMiddleware {
    public function handle(Request $request, Closure $next) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Boleh diakses oleh Kasir maupun Administrator
        if (!auth()->user()->isCashier() && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini khusus untuk Petugas Kasir Resepsionis.');
        }

        return $next($request);
    }
}
