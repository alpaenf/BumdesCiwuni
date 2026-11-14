<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpFeedStock extends Model
{
    use HasFactory;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_feed_stocks';

    protected $fillable = [
        'nama_pakan',
        'stok_tersedia_kg',
        'keterangan',
    ];
}

