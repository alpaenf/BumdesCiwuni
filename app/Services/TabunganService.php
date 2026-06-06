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
     * Setor tabungan (reguler atau sembako).
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
     * Tarik tabungan reguler (dengan administrasi opsional).
     */
    public function tarik(Tabungan $tabungan, array $data): TransaksiTabungan
    {
        return DB::transaction(function () use ($tabungan, $data) {
            $nominal       = (float) $data['nominal'];
            $administrasi  = (float) ($data['administrasi'] ?? 0);
            $totalKurang   = $nominal + $administrasi;

            if ($tabungan->saldo < $totalKurang) {
                throw new \RuntimeException('Saldo tidak mencukupi.');
            }

            $saldoBaru = $tabungan->saldo - $totalKurang;
            $tabungan->update(['saldo' => $saldoBaru]);

            return TransaksiTabungan::create([
                'tabungan_id'     => $tabungan->id,
                'tanggal'         => $data['tanggal'],
                'nomor_transaksi' => $this->nomorService->generateNomorTransaksi(),
                'jenis_transaksi' => TransaksiTabungan::JENIS_TARIK,
                'keterangan'      => $data['keterangan'] ?? 'Penarikan Tabungan',
                'nominal'         => $nominal,
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
        $unitId = auth()->user() ? auth()->user()->unit_id : null;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";
        $data['administrasi'] = (float) Setting::get($prefix . 'biaya_admin_sembako', 20000);
        $data['keterangan']   = 'Pengambilan Sembako' . (($data['jenis_pengambilan'] ?? '') === 'barang' ? ' (Barang)' : ' (Uang)');

        return $this->tarik($tabungan, $data);
    }
}
