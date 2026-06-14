<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya catat aksi yang mengubah data (POST, PUT, PATCH, DELETE)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && Auth::check()) {
            $user = Auth::user();
            
            // Format log
            $logMessage = sprintf(
                "AUDIT TRAIL | User: %s (Role: %s) | Action: %s | URL: %s | IP: %s | Payload: %s",
                $user->nama ?? $user->email,
                $user->role ?? 'N/A',
                $request->method(),
                $request->fullUrl(),
                $request->ip(),
                json_encode($request->except(['password', 'password_confirmation', '_token']))
            );

            // Tulis ke file log khusus audit
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/audit.log'),
            ])->info($logMessage);
        }

        return $response;
    }
}
