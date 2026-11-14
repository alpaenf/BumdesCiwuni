<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_expenses';

    protected $fillable = [
        'unit_kategori',
        'tanggal',
        'uraian',
        'jenis_biaya',
        'kategori_biaya',
        'nominal',
        'referensi_tipe',
        'referensi_id',
        'keterangan',
    ];
}

