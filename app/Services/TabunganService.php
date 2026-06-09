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
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksiTabungan($tabungan->jenis_tabungan),
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
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksiTabungan($tabungan->jenis_tabungan),
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
            $unitId = auth()->check() ? auth()->user()->unit_id : null;
            $prefix = $unitId ? "unit_{$unitId}_" : "global_";
            $endapanWajib = (float) \App\Models\Setting::get($prefix . 'biaya_admin_sembako', 20000);

            $isTutupBuku = !empty($data['is_tutup_buku']);

            if ($isTutupBuku) {
                $nominal = $tabungan->saldo;
                if ($nominal < $endapanWajib) {
                    throw new \RuntimeException('Tutup buku gagal. Saldo tidak mencukupi untuk biaya administrasi (Rp ' . number_format($endapanWajib, 0, ',', '.') . ').');
                }

                $administrasi = $endapanWajib;
                $nominalKeluar = $nominal - $administrasi;
                $saldoBaru = 0;
                $keterangan = 'Ambil Sembako (Tutup Buku)';
                $jenisTransaksi = 'tutup_periode';
            } else {
                $nominal = (float) $data['nominal'];
                $administrasi = 0;
                $totalKurang = $nominal;

                if ($tabungan->saldo - $totalKurang < $endapanWajib) {
                    throw new \RuntimeException('Pengambilan gagal. Saldo harus tersisa minimal Rp ' . number_format($endapanWajib, 0, ',', '.') . ' (Endapan Wajib).');
                }

                $nominalKeluar = $nominal;
                $saldoBaru = $tabungan->saldo - $totalKurang;
                $keterangan = 'Pengambilan Sembako' . (($data['jenis_pengambilan'] ?? '') === 'barang' ? ' (Barang)' : ' (Uang)');
                $jenisTransaksi = TransaksiTabungan::JENIS_TARIK_SEMBAKO;
            }

            $tabungan->update(['saldo' => $saldoBaru]);

            $foto_barang = null;
            if (isset($data['foto_barang']) && $data['foto_barang'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['foto_barang'];
                $filename = time() . '_sembako_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/tabungan'), $filename);
                $foto_barang = $filename;
            }

            return TransaksiTabungan::create([
                'tabungan_id'     => $tabungan->id,
                'tanggal'         => $data['tanggal'],
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksiTabungan($tabungan->jenis_tabungan),
                'jenis_transaksi' => $jenisTransaksi,
                'keterangan'      => $data['keterangan'] ?? $keterangan,
                'foto_barang'     => $foto_barang,
                'nominal'         => $nominalKeluar,
                'administrasi'    => $administrasi,
                'saldo_setelah'   => $saldoBaru,
            ]);
        });
    }
}
