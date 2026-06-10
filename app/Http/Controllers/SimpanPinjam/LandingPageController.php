<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\GaleriUnit;
use App\Models\LandingPageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    private array $settingsKeys = [
        'custom_logo',
        'hero_title',
        'hero_subtitle',
        'hero_cta_text',
        'hero_cta_link',
        'about_title',
        'about_description',
        'feature_1_title',
        'feature_1_desc',
        'feature_2_title',
        'feature_2_desc',
        'feature_3_title',
        'feature_3_desc',
        'contact_address',
        'contact_phone',
        'contact_email',
        'faq_items',
        'news_items',
        'org_pembina_name',
        'org_pembina_image',
        'org_direktur_name',
        'org_direktur_image',
        'org_sekretaris_name',
        'org_sekretaris_image',
        'org_bendahara_name',
        'org_bendahara_image',
        'org_pengawas_name',
        'org_pengawas_image',
        'org_unit_sp_name',
        'org_unit_sp_image',
        'org_unit_sp_staff_name',
        'org_unit_sp_staff_image',
        'org_unit_kpspams_name',
        'org_unit_kpspams_image',
        'org_unit_toko_name',
        'org_unit_toko_image',
        'org_unit_wisata_name',
        'org_unit_wisata_image',
        'org_unit_tps3r_name',
        'org_unit_tps3r_image',
        'about_history',
        'unit_1_name',
        'unit_1_desc',
        'unit_1_image',
        'unit_2_name',
        'unit_2_desc',
        'unit_2_image',
        'unit_3_name',
        'unit_3_desc',
        'unit_3_image',
        'unit_4_name',
        'unit_4_desc',
        'unit_4_image',
        'unit_5_name',
        'unit_5_desc',
        'unit_5_image',
    ];

    /**
     * Show the public landing page.
     */
    public function showLandingPage(): Response
    {
        $settings = [];
        foreach ($this->settingsKeys as $key) {
            $value = LandingPageSetting::getByKey($key);
            if ($key === 'faq_items' || $key === 'news_items') {
                $settings[$key] = json_decode($value, true) ?: [];
            } else {
                $settings[$key] = $value;
            }
        }

        // Gabungkan data pengurus inti dari WebsiteSetting (Portal Pusat)
        $globalKeys = [
            'org_bumdes_pembina_name', 'org_bumdes_pembina_image',
            'org_bumdes_direktur_name', 'org_bumdes_direktur_image'
        ];
        $globalSettings = \App\Models\WebsiteSetting::getMany($globalKeys);
        
        $settings['org_pembina_name'] = $globalSettings['org_bumdes_pembina_name'] ?? null;
        $settings['org_pembina_image'] = $globalSettings['org_bumdes_pembina_image'] ?? null;
        $settings['org_direktur_name'] = $globalSettings['org_bumdes_direktur_name'] ?? null;
        $settings['org_direktur_image'] = $globalSettings['org_bumdes_direktur_image'] ?? null;

        $galeri = GaleriUnit::where('unit', 'simpan-pinjam')
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('SimpanPinjam/Welcome', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
            'settings'    => $settings,
            'galeri'      => $galeri,
        ]);
    }

    /**
     * Show the editor page for admin.
     */
    public function editSettings(): Response
    {
        $settings = [];
        foreach ($this->settingsKeys as $key) {
            $value = LandingPageSetting::getByKey($key);
            if ($key === 'faq_items' || $key === 'news_items') {
                $settings[$key] = json_decode($value, true) ?: [];
            } else {
                $settings[$key] = $value;
            }
        }

        $galeri = GaleriUnit::where('unit', 'simpan-pinjam')
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('SimpanPinjam/Admin/LandingPageSettings', [
            'settings' => $settings,
            'galeri'   => $galeri,
        ]);
    }

    /**
     * Save the settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'custom_logo' => 'nullable|string',
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'hero_cta_text' => 'required|string|max:50',
            'hero_cta_link' => 'required|string|max:255',
            'about_title' => 'required|string|max:255',
            'about_description' => 'required|string',
            'feature_1_title' => 'required|string|max:100',
            'feature_1_desc' => 'required|string',
            'feature_2_title' => 'required|string|max:100',
            'feature_2_desc' => 'required|string',
            'feature_3_title' => 'required|string|max:100',
            'feature_3_desc' => 'required|string',
            'contact_address' => 'required|string',
            'contact_phone' => 'required|string|max:50',
            'contact_email' => 'required|email|max:100',
            'faq_items' => 'nullable|array',
            'news_items' => 'nullable|array',
            'org_pembina_name' => 'nullable|string|max:100',
            'org_pembina_image' => 'nullable|string',
            'org_direktur_name' => 'nullable|string|max:100',
            'org_direktur_image' => 'nullable|string',
            'org_sekretaris_name' => 'nullable|string|max:100',
            'org_sekretaris_image' => 'nullable|string',
            'org_bendahara_name' => 'nullable|string|max:100',
            'org_bendahara_image' => 'nullable|string',
            'org_pengawas_name' => 'nullable|string|max:100',
            'org_pengawas_image' => 'nullable|string',
            'org_unit_sp_name' => 'nullable|string|max:100',
            'org_unit_sp_image' => 'nullable|string',
            'org_unit_sp_staff_name' => 'nullable|string|max:100',
            'org_unit_sp_staff_image' => 'nullable|string',
            'org_unit_kpspams_name' => 'nullable|string|max:100',
            'org_unit_kpspams_image' => 'nullable|string',
            'org_unit_toko_name' => 'nullable|string|max:100',
            'org_unit_toko_image' => 'nullable|string',
            'org_unit_wisata_name' => 'nullable|string|max:100',
            'org_unit_wisata_image' => 'nullable|string',
            'org_unit_tps3r_name' => 'nullable|string|max:100',
            'org_unit_tps3r_image' => 'nullable|string',
            'about_history' => 'nullable|string',
            'unit_1_name' => 'nullable|string|max:255',
            'unit_1_desc' => 'nullable|string',
            'unit_1_image' => 'nullable|string',
            'unit_2_name' => 'nullable|string|max:255',
            'unit_2_desc' => 'nullable|string',
            'unit_2_image' => 'nullable|string',
            'unit_3_name' => 'nullable|string|max:255',
            'unit_3_desc' => 'nullable|string',
            'unit_3_image' => 'nullable|string',
            'unit_4_name' => 'nullable|string|max:255',
            'unit_4_desc' => 'nullable|string',
            'unit_4_image' => 'nullable|string',
            'unit_5_name' => 'nullable|string|max:255',
            'unit_5_desc' => 'nullable|string',
            'unit_5_image' => 'nullable|string',
        ]);

        foreach ($this->settingsKeys as $key) {
            if ($request->has($key) || $request->exists($key)) {
                $value = $request->input($key);
                if ($key === 'faq_items' || $key === 'news_items') {
                    $value = json_encode($value ?: []);
                }
                LandingPageSetting::setByKey($key, $value);
            }
        }

        return redirect()->back()->with('success', 'Konten Landing Page berhasil diperbarui.');
    }

    /**
     * Upload an image for news items.
     */
    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            // Ensure uploads/news directory exists
            $path = public_path('uploads/news');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $file->move($path, $filename);
            return response()->json([
                'url' => '/uploads/news/' . $filename
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
