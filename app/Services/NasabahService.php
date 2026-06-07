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

        if (isset($data['foto_jaminan']) && $data['foto_jaminan'] instanceof \Illuminate\Http\UploadedFile) {
            $fileJaminan = $data['foto_jaminan'];
            $filenameJaminan = time() . '_jaminan_' . uniqid() . '.' . $fileJaminan->getClientOriginalExtension();
            $fileJaminan->move(public_path('uploads/jaminan'), $filenameJaminan);
            $data['foto_jaminan'] = $filenameJaminan;
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

        if (isset($data['foto_jaminan']) && $data['foto_jaminan'] instanceof \Illuminate\Http\UploadedFile) {
            if ($nasabah->foto_jaminan && file_exists(public_path('uploads/jaminan/' . $nasabah->foto_jaminan))) {
                @unlink(public_path('uploads/jaminan/' . $nasabah->foto_jaminan));
            }
            $fileJaminan = $data['foto_jaminan'];
            $filenameJaminan = time() . '_jaminan_' . uniqid() . '.' . $fileJaminan->getClientOriginalExtension();
            $fileJaminan->move(public_path('uploads/jaminan'), $filenameJaminan);
            $data['foto_jaminan'] = $filenameJaminan;
        } else {
            unset($data['foto_jaminan']);
        }

        $nasabah->update($data);

        $kategori = $data['kategori'] ?? [];

        // Sync Tabungan Reguler
        $hasReguler = $nasabah->tabungan()->where('jenis_tabungan', 'reguler')->exists();
        if (in_array('tabungan', $kategori)) {
            if (!$hasReguler) {
                $nasabah->tabungan()->create(['jenis_tabungan' => 'reguler', 'saldo' => 0]);
            }
        } else {
            if ($hasReguler) {
                $nasabah->tabungan()->where('jenis_tabungan', 'reguler')->delete();
            }
        }

        // Sync Tabungan Sembako
        $hasSembako = $nasabah->tabungan()->where('jenis_tabungan', 'sembako')->exists();
        if (in_array('sembako', $kategori)) {
            if (!$hasSembako) {
                $nasabah->tabungan()->create(['jenis_tabungan' => 'sembako', 'saldo' => 0]);
            }
        } else {
            if ($hasSembako) {
                $nasabah->tabungan()->where('jenis_tabungan', 'sembako')->delete();
            }
        }

        return $nasabah->fresh();
    }

    public function delete(Nasabah $nasabah): void
    {
        if ($nasabah->foto && file_exists(public_path('uploads/nasabah/' . $nasabah->foto))) {
            @unlink(public_path('uploads/nasabah/' . $nasabah->foto));
        }
        if ($nasabah->foto_jaminan && file_exists(public_path('uploads/jaminan/' . $nasabah->foto_jaminan))) {
            @unlink(public_path('uploads/jaminan/' . $nasabah->foto_jaminan));
        }
        $nasabah->delete();
    }
}
