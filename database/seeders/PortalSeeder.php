<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class PortalSeeder extends Seeder
{
    public function run(): void
    {
        // === Default Units (4 Unit Utama Sesuai Ketentuan) ===
        $units = [
            [
                'nama_unit' => 'Unit Simpan Pinjam',
                'slug' => 'simpan-pinjam',
                'deskripsi' => 'Layanan tabungan reguler, tabungan sembako hari raya, dan pinjaman modal usaha mikro terintegrasi.',
                'tipe' => 'internal',
                'status' => 'aktif',
                'icon' => 'payments',
                'urutan' => 1,
            ],
            [
                'nama_unit' => 'Bumdes Ciwuni Unit Wifi',
                'slug' => 'wifi',
                'deskripsi' => 'Layanan internet desa murah dan cepat untuk seluruh masyarakat Desa Ciwuni.',
                'tipe' => 'internal',
                'status' => 'aktif',
                'icon' => 'wifi',
                'urutan' => 2,
            ],
            [
                'nama_unit' => 'Bumdes Ciwuni Unit Ketahanan Pangan',
                'slug' => 'ketahanan-pangan',
                'deskripsi' => 'Pengelolaan budidaya ayam ras petelur hewani serta produksi pertanian nabati desa.',
                'tipe' => 'internal',
                'status' => 'aktif',
                'icon' => 'agriculture',
                'urutan' => 3,
            ],
            [
                'nama_unit' => 'Bumdes Ciwuni Unit Perdagangan Besar',
                'slug' => 'perdagangan-besar',
                'deskripsi' => 'Penyedia dan distributor komoditas skala besar hasil pertanian dan industri kreatif Desa Ciwuni.',
                'tipe' => 'internal',
                'status' => 'aktif',
                'icon' => 'local_shipping',
                'urutan' => 4,
            ],
        ];

        // Nonaktifkan unit di luar 4 unit utama
        Unit::whereNotIn('slug', ['simpan-pinjam', 'wifi', 'ketahanan-pangan', 'perdagangan-besar'])->update(['status' => 'nonaktif']);

        foreach ($units as $unit) {
            Unit::updateOrCreate(['slug' => $unit['slug']], $unit);
        }

        // === Default Website Settings ===
        $settings = [
            'bumdes_name' => 'BUMDesa Dammar Wulan',
            'bumdes_tagline' => 'Portal Resmi BUMDesa Dammar Wulan Desa Ciwuni',
            'bumdes_logo' => null,
            'hero_title' => 'Portal Resmi BUMDesa Dammar Wulan Desa Ciwuni',
            'hero_subtitle' => 'Lembaga ekonomi desa yang berdedikasi mengelola berbagai unit bisnis dan layanan masyarakat mulai dari keuangan mikro, perdagangan sembako, pengelolaan air bersih, hingga pengembangan pariwisata.',
            'hero_cta_text' => 'Lihat Unit Usaha',
            'hero_cta_link' => '#unit-usaha',
            'about_title' => 'Mengenal BUMDesa Dammar Wulan',
            'about_description' => 'BUMDesa Dammar Wulan adalah badan usaha milik desa yang didirikan dengan semangat membangun perekonomian masyarakat Desa Ciwuni, Kecamatan Wanareja, Kabupaten Cilacap secara mandiri dan berkelanjutan.',
            'about_history' => 'Didirikan pada tahun 2019, BUMDesa Dammar Wulan tumbuh dari kebutuhan warga akan lembaga ekonomi desa yang profesional dan transparan.',
            'visi' => 'Menjadi lembaga ekonomi desa terdepan yang mandiri, transparan, dan berdaya saing dalam memajukan kesejahteraan masyarakat Desa Ciwuni.',
            'misi' => "1. Mengembangkan unit usaha yang beragam dan berdampak.\n2. Memberikan pelayanan prima kepada masyarakat.\n3. Mengelola aset desa secara profesional dan transparan.\n4. Meningkatkan pendapatan asli desa.",
            'contact_address' => 'Desa Ciwuni, Kec. Wanareja, Kab. Cilacap, Jawa Tengah',
            'contact_phone' => '0812-0000-0000',
            'contact_email' => 'bumdes@ciwuni.desa.id',
            'google_maps_embed' => '',
            'footer_text' => '© 2024 BUMDesa Dammar Wulan. Hak Cipta Dilindungi.',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
        ];

        foreach ($settings as $key => $value) {
            WebsiteSetting::setValue($key, $value);
        }
    }
}
