<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbKas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_kas';

    protected $fillable = [
        'tanggal',
        'kategori_layanan',
        'jenis', // masuk, keluar
        'nominal',
        'tipe_kas', // Tunai, Rekening
        'nama_admin',
        'keterangan',
        'bukti_transaksi',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'nominal' => 'decimal:2',
    ];
}

