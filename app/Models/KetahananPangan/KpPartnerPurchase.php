<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpPartnerPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_partner_purchases';

    protected $fillable = [
        'tanggal',
        'nama_rekanan',
        'no_hp',
        'jumlah_kg',
        'harga_per_kg',
        'total_biaya',
        'status_pembayaran',
        'metode_pembayaran',
        'keterangan',
    ];
}
