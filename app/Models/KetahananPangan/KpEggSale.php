<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpEggSale extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_egg_sales';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'customer_id',
        'nama_pelanggan',
        'customer_type',
        'jumlah_kg',
        'harga_per_kg',
        'total_harga',
        'jumlah_pembayaran',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
        'keterangan',
    ];

    public function customer()
    {
        return $this->belongsTo(KpCustomer::class, 'customer_id');
    }
}

