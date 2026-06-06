<?php

namespace App\Services;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class TabunganService
{
    public function __construct(private NomorService $nomorService) {}

    /**
     * Setor tabungan.
     */
    public function setor(Tabungan $tabungan, array $data): TransaksiTabungan
    {
        return DB::transaction(function () use ($tabungan, $data) {
            $nominal = (float) $data['nominal'];
            $saldoBaru = $tabungan->saldo + $nominal;

            $tabungan->update(['saldo' => $saldoBaru]);

            return TransaksiTabungan::create([
                'tabungan_id'     => $tabungan->id,
                'tanggal'         => $data['tanggal'],
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksi(),
                'jenis_transaksi' => TransaksiTabungan::JENIS_SETOR,
                'keterangan'      => $data['keterangan'] ?? 'Setoran Tabungan',
                'nominal'         => $nominal,
                'administrasi'    => 0,
                'saldo_setelah'   => $saldoBaru,
            ]);
        });
    }

    /**
     * Tarik tabungan (tunai atau sembako) dengan endapan wajib Rp20.000.
     */
    public function tarik(Tabungan $tabungan, array $data): TransaksiTabungan
    {
        return DB::transaction(function () use ($tabungan, $data) {
            $nominal = (float) $data['nominal'];
            $jenisTransaksi = $data['jenis_transaksi'] ?? TransaksiTabungan::JENIS_TARIK_TUNAI;
            $unitId = auth()->check() ? auth()->user()->unit_id : null;
            $prefix = $unitId ? "unit_{$unitId}_" : "global_";
            $endapanWajib = \App\Models\Setting::get($prefix . 'endapan_wajib_tabungan', 20000);
            
            $isTutupBuku = !empty($data['is_tutup_buku']);

            if ($isTutupBuku) {
                // Saat tutup buku, tarik SEMUA saldo yang ada.
                $nominal = $tabungan->saldo;
                if ($nominal < $endapanWajib) {
                    throw new \RuntimeException('Tutup buku gagal. Saldo tidak mencukupi untuk biaya administrasi (Rp ' . number_format($endapanWajib, 0, ',', '.') . ').');
                }
                
                $administrasi = $endapanWajib;
                $nominalKeluar = $nominal - $administrasi;
                $saldoBaru = 0;
                $keterangan = 'Ambil (Tutup Buku)';
                $jenisTransaksi = 'tutup_periode'; // Marker for Buku Tabungan
            } else {
                if ($tabungan->saldo - $nominal < $endapanWajib) {
                    throw new \RuntimeException('Penarikan gagal. Saldo harus tersisa minimal Rp ' . number_format($endapanWajib, 0, ',', '.') . ' (Endapan Wajib).');
                }
                $administrasi = 0;
                $nominalKeluar = $nominal;
                $saldoBaru = $tabungan->saldo - $nominal;
                $keterangan = $data['keterangan'] ?? 'Penarikan Tunai';
            }

            $tabungan->update(['saldo' => $saldoBaru]);

            return TransaksiTabungan::create([
                'tabungan_id'     => $tabungan->id,
                'tanggal'         => $data['tanggal'],
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksi(),
                'jenis_transaksi' => $jenisTransaksi,
                'keterangan'      => $keterangan,
                'nominal'         => $nominalKeluar,
                'administrasi'    => $administrasi,
                'saldo_setelah'   => $saldoBaru,
            ]);
        });
    }

    /**
     * Ambil tabungan sembako (administrasi Rp20.000 otomatis).
     */
    public function ambilSembako(Tabungan $tabungan, array $data): TransaksiTabungan
    {
        return DB::transaction(function () use ($tabungan, $data) {
            $nominal = (float) $data['nominal'];
            $unitId = auth()->check() ? auth()->user()->unit_id : null;
            $prefix = $unitId ? "unit_{$unitId}_" : "global_";
            $administrasi = (float) \App\Models\Setting::get($prefix . 'biaya_admin_sembako', 20000);
            $totalKurang = $nominal + $administrasi;

            if ($tabungan->saldo < $totalKurang) {
                throw new \RuntimeException('Saldo tidak mencukupi.');
            }

            $saldoBaru = $tabungan->saldo - $totalKurang;
            $tabungan->update(['saldo' => $saldoBaru]);

            return TransaksiTabungan::create([
                'tabungan_id'     => $tabungan->id,
                'tanggal'         => $data['tanggal'],
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksi(),
                'jenis_transaksi' => TransaksiTabungan::JENIS_TARIK_SEMBAKO,
                'keterangan'      => 'Pengambilan Sembako' . (($data['jenis_pengambilan'] ?? '') === 'barang' ? ' (Barang)' : ' (Uang)'),
                'nominal'         => $nominal,
                'administrasi'    => $administrasi,
                'saldo_setelah'   => $saldoBaru,
            ]);
        });
    }
}
