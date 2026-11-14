<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpEggProduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_egg_productions';

    protected $fillable = [
        'umur_ke',
        'hari',
        'tanggal',
        'umur_minggu',
        'hari_ke',
        'produksi_kg',
        'stok_sebelum_kg',
        'terjual_kg',
        'sisa_stok_kg',
        'harga_per_kg',
        'pendapatan_estimasi',
        'telur_retak_butir',
        'keterangan_retak',
        'telur_rusak_butir',
        'keterangan_rusak',
        'pendapatan_harian_real',
    ];
}

