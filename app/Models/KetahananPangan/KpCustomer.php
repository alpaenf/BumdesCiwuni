<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_customers';

    protected $fillable = [
        'tanggal',
        'nama',
        'customer_type',
        'no_hp',
        'alamat',
        'keterangan',
    ];

    public function eggSales()
    {
        return $this->hasMany(KpEggSale::class, 'customer_id');
    }
}

