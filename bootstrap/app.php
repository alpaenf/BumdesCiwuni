<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if ($user && $request->is('login')) {
                return route('dashboard');
            }
            if ($user && $request->is('portal/login')) {
                return $user->role === 'manager_pusat'
                    ? route('portal.exec.dashboard')
                    : route('portal.cms.dashboard');
            }
            if ($user && $user->role === 'admin') {
                if (is_null($user->unit_id)) {
                    return route('portal.cms.dashboard');
                } else {
                    return route('dashboard');
                }
            } elseif ($user && $user->role === 'manager') {
                return route('portal.exec.dashboard');
            }
            return route('dashboard');
        });

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'pin' => \App\Http\Middleware\PinMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
