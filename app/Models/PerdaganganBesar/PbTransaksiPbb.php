<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbTransaksiPbb extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_transaksi_pbb';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'waktu',
        'wajib_pajak_pbb_id',
        'nama_admin',
        'nama_wajib_pajak',
        'nop',
        'alamat_objek_pajak',
        'tahun_pajak',
        'nominal_pokok',
        'biaya_admin',
        'total_bayar',
        'metode_pembayaran',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'nominal_pokok' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'total_bayar' => 'decimal:2',
    ];

    public function wajibPajak()
    {
        return $this->belongsTo(PbWajibPajakPbb::class, 'wajib_pajak_pbb_id');
    }
}

