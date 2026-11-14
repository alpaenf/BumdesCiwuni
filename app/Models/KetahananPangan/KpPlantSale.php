<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpPlantSale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_plant_sales';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'nama_pelanggan',
        'commodity_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'total_harga',
        'jumlah_pembayaran',
        'metode_pembayaran',
        'status_pembayaran',
        'keterangan',
    ];

    public function commodity()
    {
        return $this->belongsTo(KpPlantCommodity::class, 'commodity_id');
    }
}

