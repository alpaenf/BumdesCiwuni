<?php

namespace Database\Seeders;

use App\Models\PelangganWifi;
use App\Models\ProviderWifi;
use Illuminate\Database\Seeder;

class PelangganWifiSeeder extends Seeder
{
    public function run(): void
    {
        // Create 2 Sample Providers
        $providerA = ProviderWifi::firstOrCreate(
            ['nama_provider' => 'PT Fiber Desa (9%)'],
            [
                'tipe_bagi_hasil'  => 'PERSENTASE',
                'nilai_bagi_hasil' => 9.00,
                'penanggung_jawab' => 'Bpk. Hendra (PIC ISP)',
                'no_telepon'       => '081234567890',
                'keterangan'       => 'Kerjasama skema persentase 9% BUMDes',
            ]
        );

        $providerB = ProviderWifi::firstOrCreate(
            ['nama_provider' => 'Indihome / Telkom (Flat Admin)'],
            [
                'tipe_bagi_hasil'  => 'FLAT_ADMIN',
                'nilai_bagi_hasil' => 15000.00,
                'penanggung_jawab' => 'Ibu Maria (Account Mgr)',
                'no_telepon'       => '085711223344',
                'keterangan'       => 'Kerjasama komisi admin Rp 15.000 / pelanggan',
            ]
        );

        $data = [
            [
                'provider_wifi_id'            => $providerA->id,
                'no'                          => 1,
                'nama'                        => 'Budi Santoso',
                'tanggal_daftar'              => '2026-01-10',
                'paket'                       => '10 Mbps',
                'nik'                         => '3302010101900001',
                'alamat'                      => 'Dusun Karanganyar',
                'rt'                          => '02',
                'rw'                          => '04',
                'no_id_pel'                   => 'WF-2026-001',
                'no_wa'                       => '081298765001',
                'total_dasar_tarikan_non_ppn' => 150000,
                'ppn_dan_pph'                 => 0,
                'ppn_pph'                     => 0,
                'total_tarikan'               => 150000,
                'bagi_hasil_bumdes'           => 13500, // 9%
                'hasil_bumdes'                => 13500,
                'nota_bayar_provider'         => 136500,
                'total_provider'              => 136500,
                'status_1_15'                 => 'LUNAS',
                'status_16_30'                => 'LUNAS',
                'gps_lat'                     => -7.3305694,
                'gps_long'                    => 109.2296654,
            ],
            [
                'provider_wifi_id'            => $providerB->id,
                'no'                          => 2,
                'nama'                        => 'Siti Rahayu',
                'tanggal_daftar'              => '2026-02-15',
                'paket'                       => '20 Mbps',
                'nik'                         => '3302010202910002',
                'alamat'                      => 'Dusun Ciwuni Tengah',
                'rt'                          => '01',
                'rw'                          => '01',
                'no_id_pel'                   => 'WF-2026-002',
                'no_wa'                       => '085322110002',
                'total_dasar_tarikan_non_ppn' => 165000,
                'ppn_dan_pph'                 => 0,
                'ppn_pph'                     => 0,
                'total_tarikan'               => 165000,
                'bagi_hasil_bumdes'           => 15000, // Flat Rp 15.000
                'hasil_bumdes'                => 15000,
                'nota_bayar_provider'         => 150000,
                'total_provider'              => 150000,
                'status_1_15'                 => 'LUNAS',
                'status_16_30'                => 'TUNGGAKAN',
                'gps_lat'                     => -7.3312450,
                'gps_long'                    => 109.2305210,
            ],
            [
                'provider_wifi_id'            => $providerA->id,
                'no'                          => 3,
                'nama'                        => 'Warung Kopi Digital Pak Darmo',
                'tanggal_daftar'              => '2026-03-01',
                'paket'                       => '50 Mbps',
                'nik'                         => '3302010303850003',
                'alamat'                      => 'Dusun Karanganyar',
                'rt'                          => '03',
                'rw'                          => '04',
                'no_id_pel'                   => 'WF-2026-003',
                'no_wa'                       => '082188880003',
                'total_dasar_tarikan_non_ppn' => 450000,
                'ppn_dan_pph'                 => 0,
                'ppn_pph'                     => 0,
                'total_tarikan'               => 450000,
                'bagi_hasil_bumdes'           => 40500, // 9%
                'hasil_bumdes'                => 40500,
                'nota_bayar_provider'         => 409500,
                'total_provider'              => 409500,
                'status_1_15'                 => 'LUNAS',
                'status_16_30'                => 'LUNAS',
                'gps_lat'                     => -7.3298120,
                'gps_long'                    => 109.2289340,
            ],
            [
                'provider_wifi_id'            => $providerB->id,
                'no'                          => 4,
                'nama'                        => 'Supardi Warsono',
                'tanggal_daftar'              => '2026-04-20',
                'paket'                       => '10 Mbps',
                'nik'                         => '3302010404880004',
                'alamat'                      => 'Dusun Wanasari',
                'rt'                          => '04',
                'rw'                          => '02',
                'no_id_pel'                   => 'WF-2026-004',
                'no_wa'                       => '087766550004',
                'total_dasar_tarikan_non_ppn' => 150000,
                'ppn_dan_pph'                 => 0,
                'ppn_pph'                     => 0,
                'total_tarikan'               => 150000,
                'bagi_hasil_bumdes'           => 15000, // Flat Rp 15.000
                'hasil_bumdes'                => 15000,
                'nota_bayar_provider'         => 135000,
                'total_provider'              => 135000,
                'status_1_15'                 => 'TUNGGAKAN',
                'status_16_30'                => 'ISOLIR',
                'gps_lat'                     => -7.3320890,
                'gps_long'                    => 109.2310450,
            ],
            [
                'provider_wifi_id'            => $providerA->id,
                'no'                          => 5,
                'nama'                        => 'SDN 1 Ciwuni',
                'tanggal_daftar'              => '2026-05-12',
                'paket'                       => '100 Mbps',
                'nik'                         => null,
                'alamat'                      => 'Dusun Ciwuni Tengah',
                'rt'                          => '01',
                'rw'                          => '02',
                'no_id_pel'                   => 'WF-2026-005',
                'no_wa'                       => '081122330005',
                'total_dasar_tarikan_non_ppn' => 450000,
                'ppn_dan_pph'                 => 0,
                'ppn_pph'                     => 0,
                'total_tarikan'               => 450000,
                'bagi_hasil_bumdes'           => 40500, // 9%
                'hasil_bumdes'                => 40500,
                'nota_bayar_provider'         => 409500,
                'total_provider'              => 409500,
                'status_1_15'                 => 'LUNAS',
                'status_16_30'                => 'LUNAS',
                'gps_lat'                     => -7.3308760,
                'gps_long'                    => 109.2301870,
            ],
        ];

        foreach ($data as $row) {
            PelangganWifi::updateOrCreate(
                ['no_id_pel' => $row['no_id_pel']],
                $row
            );
        }

        $this->command->info('Seeded 2 sample providers and 5 dummy pelanggan with provider relations.');
    }
}
