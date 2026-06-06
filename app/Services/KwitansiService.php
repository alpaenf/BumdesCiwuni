<?php

namespace App\Services;

use App\Models\Kwitansi;
use App\Models\Nasabah;

class KwitansiService
{
    public function __construct(private NomorService $nomorService) {}

    public function buat(Nasabah $nasabah, array $data): Kwitansi
    {
        $foto = null;
        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['foto'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/kwitansi'), $filename);
            $foto = $filename;
        }

        return Kwitansi::create([
            'nomor_kwitansi' => $this->nomorService->generateNomorKwitansi(),
            'nasabah_id'     => $nasabah->id,
            'nominal'        => (float) $data['nominal'],
            'keperluan'      => $data['keperluan'],
            'tanggal'        => $data['tanggal'],
            'foto'           => $foto,
        ]);
    }
}
