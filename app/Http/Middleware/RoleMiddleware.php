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

        // Super admin & Manager Pusat selalu lolos dari pengecekan role
        if ($user->role === 'admin' || $user->role === 'manager_pusat') {
            return $next($request);
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Proteksi Ketat Unit ID:
        // Jika user terdaftar khusus pada unit lain (misal: Unit WiFi, Ketahanan Pangan, Perdagangan Besar),
        // batasi akses ke modul Simpan Pinjam dan alihkan ke dashboard unitnya sendiri.
        if ($user->unit_id) {
            $unit = $user->unit ?: \App\Models\Unit::find($user->unit_id);
            if ($unit && $unit->slug && $unit->slug !== 'simpan-pinjam') {
                return redirect("/unit/{$unit->slug}/dashboard")
                    ->with('warning', "Akun Anda terdaftar khusus sebagai pengelola {$unit->nama_unit}.");
            }
        }

        return $next($request);
    }
}
