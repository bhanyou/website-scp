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

        // Izinkan role 'bk' jika role yang diminta adalah 'guru' (guru BK merupakan bagian dari dewan guru)
        if (in_array('guru', $roles, true) && $userRole === 'bk') {
            return $next($request);
        }

        // Izinkan role 'guru' jika ia adalah guru BK dan role yang diminta adalah 'bk'
        if (in_array('bk', $roles, true) && ($userRole === 'bk' || (auth()->user()->teacher && auth()->user()->teacher->isBkTeacher()))) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}