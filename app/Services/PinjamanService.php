<?php

namespace App\Services;

use App\Models\Nasabah;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;

class PinjamanService
{
    public function __construct(private NomorService $nomorService) {}

    /**
     * Buat pinjaman baru dengan kalkulasi otomatis.
     */
    public function buat(Nasabah $nasabah, array $data): Pinjaman
    {
        return DB::transaction(function () use ($nasabah, $data) {
            $pokok         = (float) $data['pinjaman_pokok'];
            $bunga         = (float) ($data['bunga'] ?? 10); // default 10%
            $nominalSetor  = (float) $data['nominal_setoran'];

            $totalTagihan    = $pokok + ($pokok * $bunga / 100);
            $jumlahAngsuran  = (int) ceil($totalTagihan / max(1, $nominalSetor));

            $foto_perjanjian = null;
            if (isset($data['foto_perjanjian']) && $data['foto_perjanjian'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['foto_perjanjian'];
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/pinjaman'), $filename);
                $foto_perjanjian = $filename;
            }

            return Pinjaman::create([
                'nasabah_id'      => $nasabah->id,
                'nomor_transaksi' => $this->nomorService->generateNomorPinjaman(),
                'tanggal_akad'    => $data['tanggal_akad'],
                'pinjaman_pokok'  => $pokok,
                'bunga'           => $bunga,
                'total_tagihan'   => $totalTagihan,
                'nominal_setoran' => $nominalSetor,
                'biaya_tambahan'  => (float) ($data['biaya_tambahan'] ?? 0),
                'jumlah_angsuran' => $jumlahAngsuran,
                'sisa_pinjaman'   => $totalTagihan,
                'status'          => 'aktif',
                'foto_perjanjian' => $foto_perjanjian,
            ]);
        });
    }

    /**
     * Hitung preview angsuran sebelum disimpan.
     */
    public function kalkulasi(float $pokok, float $bunga, float $nominalSetor): array
    {
        $totalBunga     = $pokok * $bunga / 100;
        $totalTagihan   = $pokok + $totalBunga;
        $jumlahAngsuran = $nominalSetor > 0 ? (int) ceil($totalTagihan / $nominalSetor) : 0;
        
        $porsiPokok = $totalTagihan > 0 ? ($pokok / $totalTagihan) * $nominalSetor : 0;
        $porsiBunga = $totalTagihan > 0 ? ($totalBunga / $totalTagihan) * $nominalSetor : 0;

        return [
            'pinjaman_pokok'  => $pokok,
            'bunga'           => $bunga,
            'total_bunga'     => $totalBunga,
            'total_tagihan'   => $totalTagihan,
            'nominal_setoran' => $nominalSetor,
            'porsi_pokok'     => $porsiPokok,
            'porsi_bunga'     => $porsiBunga,
            'jumlah_angsuran' => $jumlahAngsuran,
        ];
    }
}
