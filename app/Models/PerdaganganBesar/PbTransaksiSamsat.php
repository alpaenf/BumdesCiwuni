<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbTransaksiSamsat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_transaksi_samsat';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'wajib_pajak_id',
        'nama_wajib_pajak',
        'nomor_polisi',
        'jenis_kendaraan',
        'jenis_pembayaran',
        'nominal_pajak',
        'biaya_admin',
        'biaya_jasa_bumdes',
        'total_pembayaran',
        'status_transaksi',
        'nama_admin',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'nominal_pajak' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'biaya_jasa_bumdes' => 'decimal:2',
        'total_pembayaran' => 'decimal:2',
    ];

    public function wajibPajak()
    {
        return $this->belongsTo(PbWajibPajakSamsat::class, 'wajib_pajak_id');
    }
}

