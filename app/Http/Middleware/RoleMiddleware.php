<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Super admin selalu lolos dari semua pengecekan role
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
