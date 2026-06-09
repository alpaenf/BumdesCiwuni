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
            $biayaTambahan = (float) ($data['biaya_tambahan'] ?? 0);

            $totalTagihan    = $pokok + ($pokok * $bunga / 100) + $biayaTambahan;
            $jumlahAngsuran  = (int) ceil($totalTagihan / max(1, $nominalSetor));

            $foto_perjanjian = null;
            if (isset($data['foto_perjanjian']) && $data['foto_perjanjian'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['foto_perjanjian'];
                $filename = time() . '_perjanjian_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/pinjaman'), $filename);
                $foto_perjanjian = $filename;
            }

            $foto_barang = null;
            if (isset($data['foto_barang']) && $data['foto_barang'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['foto_barang'];
                $filename = time() . '_barang_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/pinjaman'), $filename);
                $foto_barang = $filename;
            }

            return Pinjaman::create([
                'nasabah_id'      => $nasabah->id,
                'nomor_transaksi' => $this->nomorService->generateNomorPinjaman(),
                'tanggal_akad'    => $data['tanggal_akad'],
                'jenis_pinjaman'  => $data['jenis_pinjaman'] ?? 'Uang Tunai',
                'keterangan'      => $data['keterangan'] ?? null,
                'pinjaman_pokok'  => $pokok,
                'bunga'           => $bunga,
                'total_tagihan'   => $totalTagihan,
                'nominal_setoran' => $nominalSetor,
                'biaya_tambahan'  => $biayaTambahan,
                'jumlah_angsuran' => $jumlahAngsuran,
                'sisa_pinjaman'   => $totalTagihan,
                'status'          => 'aktif',
                'foto_perjanjian' => $foto_perjanjian,
                'foto_barang'     => $foto_barang,
            ]);
        });
    }

    /**
     * Hitung preview angsuran sebelum disimpan.
     */
    public function kalkulasi(float $pokok, float $bunga, float $nominalSetor, float $biayaTambahan = 0): array
    {
        $totalBunga     = $pokok * $bunga / 100;
        $totalTagihan   = $pokok + $totalBunga + $biayaTambahan;
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
