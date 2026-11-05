<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Menu ini hanya dapat diakses oleh Admin Utama.');
        }

        return $next($request);
    }
}
