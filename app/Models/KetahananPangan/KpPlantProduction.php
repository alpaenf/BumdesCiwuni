<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpPlantProduction extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'ketahanan_pangan';
    protected $table = 'kp_plant_productions';

    protected $fillable = [
        'tanggal',
        'commodity_id',
        'jumlah_panen',
        'satuan',
        'lokasi_lahan',
        'keterangan',
    ];

    public function commodity()
    {
        return $this->belongsTo(KpPlantCommodity::class, 'commodity_id');
    }
}

