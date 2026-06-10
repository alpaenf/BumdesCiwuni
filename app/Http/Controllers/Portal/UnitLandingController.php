<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
            $unitSetting = \App\Models\LandingPageSetting::where('key', "{$slug}_{$key}")->first();
            
            if ($unitSetting) {
                $value = $unitSetting->value;
            } else {
                $value = ''; // No fallback to Simpan Pinjam, let Vue handle placeholders
            }

            if ($key === 'faq_items' || $key === 'news_items') {
                $settings[$key] = json_decode($value, true) ?: [];
            } else {
                $settings[$key] = $value;
            }
        }

        $galeri = \App\Models\GaleriUnit::where('unit', $slug)
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        if ($galeri->isEmpty()) {
            $galeri = \App\Models\GaleriUnit::where('unit', 'simpan-pinjam')
                ->orderBy('urutan')
                ->orderByDesc('id')
                ->get();
        }

        return Inertia::render("$folder/Welcome", [
            'unit' => $unit,
            'canLogin' => true,
            'settings' => $settings,
            'galeri' => $galeri,
        ]);
    }

    /**
     * Show the login form for a specific unit.
     */
    public function login(string $slug): Response|RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
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

    /**
     * Show the unit landing page settings.
     */
    public function editSettings(string $slug): Response
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized access to this unit dashboard.');
        }

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
            $unitSetting = \App\Models\LandingPageSetting::where('key', "{$slug}_{$key}")->first();
            
            if ($unitSetting) {
                $value = $unitSetting->value;
            } else {
                $value = ''; // No fallback in settings page so form starts completely empty!
            }

            if ($key === 'faq_items' || $key === 'news_items') {
                $settings[$key] = json_decode($value, true) ?: [];
            } else {
                $settings[$key] = $value;
            }
        }

        $galeri = \App\Models\GaleriUnit::where('unit', $slug)
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Portal/UnitSettings', [
            'unit' => $unit,
            'settings' => $settings,
            'galeri' => $galeri,
        ]);
    }

    /**
     * Update the unit landing page settings.
     */
    public function updateSettings(Request $request, string $slug): RedirectResponse
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->except(['_token', 'galeri', 'unit']);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            \App\Models\LandingPageSetting::setByKey("{$slug}_{$key}", $value);
        }

        return redirect()->back()->with('success', 'Pengaturan landing page berhasil disimpan.');
    }

    /**
     * Upload images for unit landing page settings.
     */
    public function uploadImage(Request $request, string $slug)
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->move(public_path("uploads/unit_{$slug}"), $filename);

        return response()->json([
            'url' => "/uploads/unit_{$slug}/" . $filename
        ]);
    }

    public function galeriStore(Request $request, string $slug)
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'foto'       => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file = $request->file('foto');
        $tempPath = $file->getRealPath();
        $info = getimagesize($tempPath);
        
        $filename = time() . '_' . uniqid() . '.webp';
        $path = 'galeri/unit_' . $slug . '_' . $filename;

        $image = null;
        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($tempPath);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($tempPath);
        elseif ($info['mime'] == 'image/webp') $image = imagecreatefromwebp($tempPath);

        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            $newWidth = $width > 1200 ? 1200 : $width;
            $newHeight = intval($height * ($newWidth / $width));

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagewebp($newImage, null, 70);
            $imageContent = ob_get_clean();
            
            Storage::disk('public')->put($path, $imageContent);

            imagedestroy($image);
            imagedestroy($newImage);
        } else {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'galeri/unit_' . $slug . '_' . $filename;
            Storage::disk('public')->put($path, file_get_contents($tempPath));
        }

        $urutan = \App\Models\GaleriUnit::where('unit', $slug)->max('urutan') + 1;

        \App\Models\GaleriUnit::create([
            'unit'       => $slug,
            'foto'       => '/storage/' . $path,
            'keterangan' => $request->keterangan,
            'urutan'     => $urutan,
        ]);

        return redirect()->back()->with('success', 'Foto berhasil diupload dan dikompres (WebP)!');
    }

    public function galeriDestroy(string $slug, int $id)
    {
        $unit = Unit::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized');
        }

        $galeri = \App\Models\GaleriUnit::where('unit', $slug)->where('id', $id)->firstOrFail();

        $path = str_replace('/storage/', '', $galeri->foto);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $galeri->delete();

        return redirect()->back()->with('success', 'Foto galeri berhasil dihapus.');
    }
}
