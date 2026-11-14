<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpPlantCommodity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_plant_commodities';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga_default',
        'stok_tersedia',
        'keterangan',
    ];

    public function productions()
    {
        return $this->hasMany(KpPlantProduction::class, 'commodity_id');
    }

    public function sales()
    {
        return $this->hasMany(KpPlantSale::class, 'commodity_id');
    }
}

