<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class PortalSeeder extends Seeder
{
    public function run(): void
    {
        // === Default Units ===
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
                'nama_unit' => 'KP-SPAMS Dammar Wulan',
                'slug' => 'kp-spams',
                'deskripsi' => 'Layanan penyediaan air bersih desa terpadu untuk kesehatan dan pemenuhan kebutuhan sanitasi warga.',
                'tipe' => 'external',
                'status' => 'aktif',
                'api_url' => 'https://kp-spamsdammarwulan.com/',
                'icon' => 'water_drop',
                'urutan' => 2,
            ],
            [
                'nama_unit' => 'Toko Desa & Grosir Sembako',
                'slug' => 'toko-desa',
                'deskripsi' => 'Pusat belanja kebutuhan pokok masyarakat desa dan suplai warung kelontong dengan harga bersaing.',
                'tipe' => 'external',
                'status' => 'aktif',
                'api_url' => 'https://toko.bumdesciwuni.com',
                'icon' => 'storefront',
                'urutan' => 3,
            ],
            [
                'nama_unit' => 'Pengelolaan Sampah & Sanitasi (TPS3R)',
                'slug' => 'tps3r',
                'deskripsi' => 'Sistem pengangkutan sampah warga terpadu dan pengolahan daur ulang limbah menjadi pupuk organik.',
                'tipe' => 'external',
                'status' => 'aktif',
                'api_url' => 'https://tps3r.bumdesciwuni.com',
                'icon' => 'delete_sweep',
                'urutan' => 4,
            ],
            [
                'nama_unit' => 'Unit Desa Wisata & Kuliner',
                'slug' => 'wisata',
                'deskripsi' => 'Pengembangan potensi pariwisata alam lokal, wahana edukasi, dan sentra UMKM kuliner Desa Ciwuni.',
                'tipe' => 'external',
                'status' => 'aktif',
                'api_url' => 'https://wisata.bumdesciwuni.com',
                'icon' => 'forest',
                'urutan' => 5,
            ],
        ];

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
