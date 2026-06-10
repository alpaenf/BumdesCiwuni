<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UnitLandingController extends Controller
{
    /**
     * Map slug to the view directory name.
     */
    private function getViewFolder(string $slug): string
    {
        return match ($slug) {
            'wifi' => 'Wifi',
            'ketahanan-pangan' => 'ketahanan pangan',
            'perdagangan-besar' => 'Perdagangan Besar',
            default => abort(404),
        };
    }

    /**
     * Show the public landing page for a unit.
     */
    public function welcome(string $slug): Response
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $folder = $this->getViewFolder($slug);

        return Inertia::render("$folder/Welcome", [
            'unit' => $unit,
            'canLogin' => true,
        ]);
    }

    /**
     * Show the login form for a unit.
     */
    public function showLogin(string $slug): Response
    {
        // If already logged in, redirect to dashboard if the user belongs to this unit
        if (Auth::check()) {
            $user = Auth::user();
            $unit = Unit::where('slug', $slug)->firstOrFail();
            if ($user->role === 'admin' || $user->unit_id == $unit->id) {
                return redirect()->route('unit.dashboard', ['slug' => $slug]);
            }
        }

        $unit = Unit::where('slug', $slug)->firstOrFail();
        $folder = $this->getViewFolder($slug);

        return Inertia::render("$folder/Login", [
            'unit' => $unit,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle login submission for a unit.
     */
    public function loginStore(Request $request, string $slug): RedirectResponse
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is Super Admin or Admin of this specific Unit
            if ($user->role === 'admin' || ($user->unit_id == $unit->id && in_array($user->role, ['admin_unit', 'manager']))) {
                return redirect()->intended("/unit/$slug/dashboard");
            }

            // Logout if unauthorized
            Auth::logout();
            return back()->withErrors([
                'email' => "Akun Anda tidak memiliki hak akses untuk mengelola unit " . $unit->nama_unit . ".",
            ]);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Show the unit dashboard.
     */
    public function dashboard(string $slug): Response
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        // Security check
        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized access to this unit dashboard.');
        }

        $folder = $this->getViewFolder($slug);

        return Inertia::render("$folder/Dashboard", [
            'unit' => $unit,
            'user' => $user,
        ]);
    }
}
