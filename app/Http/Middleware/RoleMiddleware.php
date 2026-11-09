<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Izinkan role 'bk' dan 'kesiswaan' secara saling menggantikan (alias) serta akses dewan guru
        if (in_array($userRole, ['bk', 'kesiswaan'], true)) {
            if (in_array('bk', $roles, true) || in_array('kesiswaan', $roles, true) || in_array('guru', $roles, true)) {
                return $next($request);
            }
        }

        // Izinkan dewan guru jika ia adalah guru BK/Kesiswaan dan role yang diminta adalah 'bk' atau 'kesiswaan'
        if ((in_array('bk', $roles, true) || in_array('kesiswaan', $roles, true)) && ($userRole === 'bk' || $userRole === 'kesiswaan' || (auth()->user()->teacher && auth()->user()->teacher->isBkTeacher()))) {
            return $next($request);
        }

        // Izinkan dewan guru umum
        if (in_array('guru', $roles, true) && in_array($userRole, ['bk', 'kesiswaan'], true)) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}