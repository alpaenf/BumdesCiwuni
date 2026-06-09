<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'app' => 'unit',
        ]);
    }

    public function createPortal(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'app' => 'portal',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        $isPortalLogin = $request->routeIs('portal.login');
        if (!$isPortalLogin) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        
        if ($request->is('portal/login')) {
            if ($user->role === 'admin') {
                return redirect(route('portal.cms.dashboard', absolute: false));
            } elseif ($user->role === 'manager_pusat' || $user->role === 'manager') {
                return redirect(route('portal.exec.dashboard', absolute: false));
            } else {
                Auth::logout();
                return redirect()->back()->withErrors(['email' => 'Akun Anda tidak memiliki akses ke Portal Terintegrasi. Silakan masuk melalui portal unit masing-masing.']);
            }
        }

        // Login dari unit Simpan Pinjam
        if ($user->role === 'admin' || $user->role === 'admin_unit' || $user->role === 'manager' || $user->role === 'manager_pusat') {
            return redirect(route('dashboard', absolute: false));
        }

        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $refererPath = parse_url($request->headers->get('referer') ?? '', PHP_URL_PATH) ?? '';
        if ($refererPath !== '' && preg_match('#^/unit/([^/]+)#', $refererPath, $matches) === 1) {
            return redirect('/unit/' . $matches[1]);
        }

        return redirect('/');
    }
}
