<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nasabah extends Model
{
    protected $table = 'nasabah';

    protected $fillable = [
        'nomor_registrasi',
        'nomor_rekening',
        'nama',
        'nik',
        'alamat',
        'no_hp',
        'pekerjaan',
        'jaminan',
        'foto_jaminan',
        'tanggal_bergabung',
        'status',
        'foto',
        'kategori',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'kategori' => 'array',
    ];

    protected $appends = ['foto_url', 'foto_jaminan_url'];

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('uploads/nasabah/' . $this->foto) : null;
    }

    public function getFotoJaminanUrlAttribute(): ?string
    {
        return $this->foto_jaminan ? asset('uploads/jaminan/' . $this->foto_jaminan) : null;
    }

    public function tabungan(): HasMany
    {
        return $this->hasMany(Tabungan::class);
    }

    public function pinjaman(): HasMany
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function kwitansi(): HasMany
    {
        return $this->hasMany(Kwitansi::class);
    }
}
