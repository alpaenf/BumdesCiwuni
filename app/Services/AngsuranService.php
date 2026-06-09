<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;

class AngsuranService
{
    public function __construct(private NomorService $nomorService) {}

    /**
     * Bayar angsuran pinjaman.
     */
    public function bayar(Pinjaman $pinjaman, array $data): Angsuran
    {
        return DB::transaction(function () use ($pinjaman, $data) {
            $jumlahBayar   = (float) $data['jumlah_bayar'];
            $angsuranKe    = $pinjaman->angsuran()->count() + 1;
            $sisaBaru      = max(0, $pinjaman->sisa_pinjaman - $jumlahBayar);

            $angsuran = Angsuran::create([
                'pinjaman_id'     => $pinjaman->id,
                'nomor_transaksi' => $this->nomorService->generateNomorAngsuran(),
                'tanggal'         => $data['tanggal'],
                'angsuran_ke'    => $angsuranKe,
                'pasaran'        => $data['pasaran'],
                'jumlah_bayar'   => $jumlahBayar,
                'sisa_pinjaman'  => $sisaBaru,
            ]);

            $pinjaman->update([
                'sisa_pinjaman' => $sisaBaru,
                'status'        => $sisaBaru <= 0 ? 'lunas' : 'aktif',
            ]);

            return $angsuran;
        });
    }
}
