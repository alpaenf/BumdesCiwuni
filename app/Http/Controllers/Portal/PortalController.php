<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\Post;
use App\Models\TransaksiTabungan;
use App\Models\Unit;
use App\Models\WebsiteSetting;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    /**
     * Public homepage — BUMDes portal landing page.
     */
    public function index(): Response
    {
        $settingKeys = [
            'bumdes_name', 'bumdes_tagline', 'bumdes_logo',
            'hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_link',
            'about_title', 'about_description', 'about_history',
            'visi', 'misi',
            'contact_address', 'contact_phone', 'contact_email', 'google_maps_embed',
            'footer_text', 'social_facebook', 'social_instagram', 'social_youtube',
        ];

        $settings = WebsiteSetting::getMany($settingKeys);

        $units = Unit::where('status', '!=', 'nonaktif')
            ->orderBy('urutan')
            ->get();

        $posts = Post::published()
            ->latest('published_at')
            ->take(6)
            ->get();

        $stats = [
            'unitAktif' => Unit::aktif()->count(),
            'totalNasabah' => Nasabah::count(),
            'totalTransaksi' => TransaksiTabungan::count(),
        ];

        $orgKeys = [
            'org_pembina_name', 'org_pembina_image',
            'org_direktur_name', 'org_direktur_image',
            'org_sekretaris_name', 'org_sekretaris_image',
            'org_bendahara_name', 'org_bendahara_image',
            'org_pengawas_name', 'org_pengawas_image',
            'org_unit_sp_name', 'org_unit_sp_image',
            'org_unit_kpspams_name', 'org_unit_kpspams_image',
            'org_unit_toko_name', 'org_unit_toko_image',
            'org_unit_wisata_name', 'org_unit_wisata_image',
            'org_unit_tps3r_name', 'org_unit_tps3r_image',
        ];

        $orgSettings = [];
        foreach ($orgKeys as $key) {
            $orgSettings[$key] = \App\Models\LandingPageSetting::getByKey($key);
        }

        return Inertia::render('Portal/Welcome', [
            'settings' => $settings,
            'orgSettings' => $orgSettings,
            'units' => $units,
            'posts' => $posts,
            'stats' => $stats,
        ]);
    }

    /**
     * All news listing page.
     */
    public function beritaIndex(): Response
    {
        $posts = Post::published()
            ->latest('published_at')
            ->paginate(12);

        $settings = WebsiteSetting::getMany(['bumdes_name', 'bumdes_logo']);

        return Inertia::render('Portal/BeritaIndex', [
            'posts' => $posts,
            'settings' => $settings,
        ]);
    }

    /**
     * Individual news detail page.
     */
    public function beritaDetail(string $slug): Response
    {
        $post = Post::where('slug', $slug)->published()->firstOrFail();

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $settings = WebsiteSetting::getMany(['bumdes_name', 'bumdes_logo']);

        return Inertia::render('Portal/BeritaDetail', [
            'post' => $post,
            'related' => $related,
            'settings' => $settings,
        ]);
    }
}
