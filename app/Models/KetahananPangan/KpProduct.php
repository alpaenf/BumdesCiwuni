<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpProduct extends Model
{
    use HasFactory;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_products';

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'harga_default',
        'stok_tersedia',
        'keterangan',
    ];
}

