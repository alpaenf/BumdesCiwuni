<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'           => $request->user()->id,
                    'nama'         => $request->user()->nama,
                    'username'     => $request->user()->username,
                    'role'         => $request->user()->role,
                    'isSuperAdmin' => $request->user()->isSuperAdmin(),
                ] : null,
            ],
            'flash' => [
                'success'  => $request->session()->get('success'),
                'error'    => $request->session()->get('error'),
                'struk_url' => $request->session()->get('struk_url'),
            ],
        ];
    }
}
