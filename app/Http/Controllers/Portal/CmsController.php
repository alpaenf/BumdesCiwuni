<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Unit;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CmsController extends Controller
{
    /**
     * CMS Dashboard — portal content management overview.
     */
    public function dashboard(): Response
    {
        return Inertia::render('Portal/Cms/Dashboard', [
            'stats' => [
                'totalUnits' => Unit::count(),
                'unitAktif' => Unit::aktif()->count(),
                'totalPosts' => Post::count(),
                'publishedPosts' => Post::published()->count(),
            ],
            'recentPosts' => Post::latest()->take(5)->get(),
        ]);
    }

    /**
     * Edit BUMDes profile settings.
     */
    public function editProfil(): Response
    {
        $settingKeys = [
            'bumdes_name', 'bumdes_tagline', 'bumdes_logo',
            'about_title', 'about_description', 'about_history',
            'visi', 'misi',
            'org_bumdes_direktur_name', 'org_bumdes_direktur_image',
            'org_bumdes_sekretaris_name', 'org_bumdes_sekretaris_image',
            'org_bumdes_bendahara_name', 'org_bumdes_bendahara_image',
            'contact_address', 'contact_phone', 'contact_email', 'google_maps_embed',
            'footer_text', 'social_facebook', 'social_instagram', 'social_youtube',
        ];

        return Inertia::render('Portal/Cms/ProfilBumdes', [
            'settings' => WebsiteSetting::getMany($settingKeys),
        ]);
    }

    /**
     * Update BUMDes profile settings.
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bumdes_name' => 'required|string|max:255',
            'bumdes_tagline' => 'nullable|string|max:500',
            'bumdes_logo' => 'nullable',
            'about_title' => 'nullable|string|max:255',
            'about_description' => 'nullable|string',
            'about_history' => 'nullable|string',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'org_bumdes_direktur_name' => 'nullable|string|max:255',
            'org_bumdes_direktur_image' => 'nullable|string',
            'org_bumdes_sekretaris_name' => 'nullable|string|max:255',
            'org_bumdes_sekretaris_image' => 'nullable|string',
            'org_bumdes_bendahara_name' => 'nullable|string|max:255',
            'org_bumdes_bendahara_image' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'google_maps_embed' => 'nullable|string',
            'footer_text' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
        ]);
        if ($request->hasFile('bumdes_logo')) {
            $path = $request->file('bumdes_logo')->store('portal/logos', 'public');
            $validated['bumdes_logo'] = '/storage/' . $path;
        } else {
            // Keep existing if no file uploaded and no clear action
            if ($request->has('bumdes_logo') && $request->bumdes_logo === null) {
                $validated['bumdes_logo'] = null;
            } else {
                unset($validated['bumdes_logo']);
            }
        }

        WebsiteSetting::setMany($validated);

        return redirect()->back()->with('success', 'Profil BUMDes berhasil diperbarui.');
    }

    /**
     * Edit banner / hero settings.
     */
    public function editBanner(): Response
    {
        $settingKeys = ['hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_link'];

        return Inertia::render('Portal/Cms/Banner', [
            'settings' => WebsiteSetting::getMany($settingKeys),
        ]);
    }

    /**
     * Update banner / hero settings.
     */
    public function updateBanner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string',
            'hero_cta_text' => 'required|string|max:50',
            'hero_cta_link' => 'required|string|max:255',
        ]);

        WebsiteSetting::setMany($validated);

        return redirect()->back()->with('success', 'Banner berhasil diperbarui.');
    }

    /**
     * Upload an image for CMS use.
     */
    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/portal');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $file->move($path, $filename);
            return response()->json([
                'url' => '/uploads/portal/' . $filename
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
