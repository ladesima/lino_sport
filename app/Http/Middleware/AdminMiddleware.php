<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware {
    public function handle(Request $request, Closure $next) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isAdmin()) {
            // Jika login sebagai kasir, arahkan ke portal kasir
            if (auth()->user()->isCashier()) {
                return redirect()->route('kasir.dashboard')->with('info', 'Anda dialihkan ke Portal Kasir.');
            }
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Administrator.');
        }

        return $next($request);
    }
}
