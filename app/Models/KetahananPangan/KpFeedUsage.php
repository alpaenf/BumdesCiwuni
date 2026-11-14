<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpFeedUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_feed_usages';

    protected $fillable = [
        'hari_ke',
        'hari',
        'tanggal',
        'umur_minggu_ke',
        'umur_minggu_hari_ke',
        'stok_awal_kg',
        'kebutuhan_per_ekor_gram',
        'populasi_ayam',
        'total_kebutuhan_kg',
        'sisa_pakan_kg',
        'harga_per_kg',
        'total_biaya_pemakaian',
        'keterangan',
    ];
}

