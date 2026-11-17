<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['username' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $request->user()->hasAnyRole($roles)) {
            abort(403, 'Akses Ditolak. Anda tidak memiliki kewenangan untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
