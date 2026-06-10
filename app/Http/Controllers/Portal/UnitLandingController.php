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

        // Retrieve global settings
        $settingsKeys = [
            'custom_logo', 'hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_link',
            'about_title', 'about_description', 'feature_1_title', 'feature_1_desc',
            'feature_2_title', 'feature_2_desc', 'feature_3_title', 'feature_3_desc',
            'contact_address', 'contact_phone', 'contact_email', 'faq_items', 'news_items',
            'org_pembina_name', 'org_pembina_image', 'org_direktur_name', 'org_direktur_image',
            'org_sekretaris_name', 'org_sekretaris_image', 'org_bendahara_name', 'org_bendahara_image',
            'org_pengawas_name', 'org_pengawas_image', 'org_unit_sp_name', 'org_unit_sp_image',
            'org_unit_sp_staff_name', 'org_unit_sp_staff_image', 'about_history'
        ];

        $settings = [];
        foreach ($settingsKeys as $key) {
            $value = \App\Models\LandingPageSetting::getByKey($key);
            if ($key === 'faq_items' || $key === 'news_items') {
                $settings[$key] = json_decode($value, true) ?: [];
            } else {
                $settings[$key] = $value;
            }
        }

        $galeri = \App\Models\GaleriUnit::where('unit', 'simpan-pinjam')
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        return Inertia::render("$folder/Welcome", [
            'unit' => $unit,
            'canLogin' => true,
            'settings' => $settings,
            'galeri' => $galeri,
        ]);
    }

    /**
     * Show the login form for a unit.
     */
    public function showLogin(string $slug)
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
