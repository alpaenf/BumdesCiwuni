<?php

namespace Database\Seeders;

use App\Models\LandingPageSetting;
use Illuminate\Database\Seeder;

class LandingPageSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'hero_title' => 'Membangun Perekonomian Desa Ciwuni Mandiri',
            'hero_subtitle' => 'Portal resmi BUMDesa Dammar Wulan Desa Ciwuni. Mengelola berbagai unit usaha mulai dari simpan pinjam, perdagangan sembako, penyediaan air bersih (KP-SPAMS), hingga desa wisata secara profesional dan transparan.',
            'hero_cta_text' => 'Hubungi Kami',
            'hero_cta_link' => '#kontak',
            
            'about_title' => 'Tentang BUMDes Dammar Wulan',
            'about_description' => 'Unit Simpan Pinjam BUMDes Dammar Wulan didirikan untuk mendorong roda perekonomian masyarakat Desa Ciwuni. Melalui layanan keuangan mikro yang inklusif, kami berkomitmen membantu warga desa mengelola tabungan dan mendapatkan akses permodalan usaha yang mudah.',
            
            'feature_1_title' => 'Tabungan Reguler',
            'feature_1_desc' => 'Simpanan harian yang aman dengan pencatatan digital terintegrasi untuk kebutuhan finansial masa depan Anda.',
            
            'feature_2_title' => 'Tabungan Sembako',
            'feature_2_desc' => 'Program simpanan khusus yang direncanakan untuk membantu pemenuhan kebutuhan sembako menjelang hari raya.',
            
            'feature_3_title' => 'Pinjaman Modal Usaha',
            'feature_3_desc' => 'Akses pembiayaan modal kerja bagi pelaku UMKM desa dengan bunga yang kompetitif dan angsuran ringan.',
            
            'contact_address' => 'Jalan Raya Ciwuni No. 12, Kecamatan Kesugihan, Kabupaten Cilacap, Jawa Tengah',
            'contact_phone' => '+62 812-3456-7890',
            'contact_email' => 'bumdes.dammarwulan@ciwuni.desa.id',
            
            'faq_items' => json_encode([
                [
                    'question' => 'Bagaimana cara menjadi nasabah Simpan Pinjam BUMDes?',
                    'answer' => 'Anda cukup mendatangi kantor BUMDes Dammar Wulan dengan membawa fotokopi KTP Desa Ciwuni, mengisi formulir pendaftaran, dan melakukan setoran awal minimal Rp 10.000.'
                ],
                [
                    'question' => 'Apa perbedaan Tabungan Reguler dan Tabungan Sembako?',
                    'answer' => 'Tabungan Reguler dapat ditarik kapan saja untuk kebutuhan finansial harian Anda. Sedangkan Tabungan Sembako dikhususkan untuk simpanan persiapan hari raya dan dapat ditarik menjelang lebaran.'
                ],
                [
                    'question' => 'Bagaimana syarat mengajukan Pinjaman Modal UMKM?',
                    'answer' => 'Syarat utama adalah warga Desa Ciwuni yang memiliki usaha aktif. Dokumen yang diperlukan meliputi fotokopi KTP, KK, serta Surat Keterangan Usaha (SKU) dari RT/RW setempat.'
                ]
            ]),
            
            'news_items' => json_encode([
                [
                    'title' => 'Penyaluran Paket Sembako Lebaran 2026 Sukses Dilaksanakan',
                    'date' => '2026-05-15',
                    'content' => 'Unit Simpan Pinjam BUMDes Dammar Wulan telah menyalurkan lebih dari 350 paket sembako kepada seluruh anggota aktif Tabungan Sembako Desa Ciwuni secara tertib.'
                ],
                [
                    'title' => 'Peningkatan Modal Usaha untuk 20 UMKM Baru Desa Ciwuni',
                    'date' => '2026-05-10',
                    'content' => 'BUMDes mengalokasikan dana stimulus pinjaman modal usaha tambahan guna mendorong percepatan pertumbuhan unit usaha kecil mikro warga lokal.'
                ]
            ]),
        ];

        foreach ($defaults as $key => $value) {
            LandingPageSetting::setByKey($key, $value);
        }
    }
}
