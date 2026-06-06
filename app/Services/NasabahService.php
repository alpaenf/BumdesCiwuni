<?php

namespace App\Services;

use App\Models\Nasabah;
use App\Models\Tabungan;

class NasabahService
{
    public function __construct(private NomorService $nomorService) {}

    public function create(array $data): Nasabah
    {
        $data['nomor_registrasi'] = $this->nomorService->generateNomorRegistrasi();
        $data['nomor_rekening']   = empty($data['nomor_rekening']) ? $this->nomorService->generateNomorRekening() : $data['nomor_rekening'];
        $data['tanggal_bergabung'] = $data['tanggal_bergabung'] ?? now()->toDateString();
        $data['status'] = $data['status'] ?? 'aktif';

        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['foto'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/nasabah'), $filename);
            $data['foto'] = $filename;
        }

        $nasabah = Nasabah::create($data);

        $kategori = $data['kategori'] ?? [];
        if (in_array('tabungan', $kategori)) {
            Tabungan::create(['nasabah_id' => $nasabah->id, 'jenis_tabungan' => 'reguler', 'saldo' => 0]);
        }
        if (in_array('sembako', $kategori)) {
            Tabungan::create(['nasabah_id' => $nasabah->id, 'jenis_tabungan' => 'sembako', 'saldo' => 0]);
        }

        return $nasabah;
    }

    public function update(Nasabah $nasabah, array $data): Nasabah
    {
        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            if ($nasabah->foto && file_exists(public_path('uploads/nasabah/' . $nasabah->foto))) {
                @unlink(public_path('uploads/nasabah/' . $nasabah->foto));
            }
            $file = $data['foto'];
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/nasabah'), $filename);
            $data['foto'] = $filename;
        } else {
            unset($data['foto']);
        }

        $nasabah->update($data);
        return $nasabah->fresh();
    }

    public function delete(Nasabah $nasabah): void
    {
        if ($nasabah->foto && file_exists(public_path('uploads/nasabah/' . $nasabah->foto))) {
            @unlink(public_path('uploads/nasabah/' . $nasabah->foto));
        }
        $nasabah->delete();
    }
}
