<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpFeedPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_feed_purchases';

    protected $fillable = [
        'tanggal',
        'nama_pakan',
        'jumlah_kg',
        'harga_per_kg',
        'total_biaya',
        'supplier',
        'keterangan',
    ];
}

