<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbBiayaOperasional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_biaya_operasional';

    protected $fillable = [
        'tanggal',
        'kategori_biaya', // Administrasi, ATK, Honor, Listrik & Internet, Lain-lain
        'uraian',
        'nominal',
        'nama_admin',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'nominal' => 'decimal:2',
    ];
}

