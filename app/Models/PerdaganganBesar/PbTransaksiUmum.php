<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbTransaksiUmum extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_transaksi_umum';

    protected $fillable = [
        'tanggal',
        'waktu',
        'nama_admin',
        'nama_pelanggan',
        'nomor_transaksi',
        'jenis_transaksi',
        'nomor_tujuan',
        'biaya_pokok',
        'biaya_admin',
        'biaya_jasa_agen',
        'total_bayar',
        'metode_pembayaran',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'biaya_pokok' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'biaya_jasa_agen' => 'decimal:2',
        'total_bayar' => 'decimal:2',
    ];
}

