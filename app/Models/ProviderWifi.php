<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderWifi extends Model
{
    use HasFactory;

    protected $table = 'provider_wifi';

    protected $fillable = [
        'nama_provider',
        'tipe_bagi_hasil',
        'nilai_bagi_hasil',
        'penanggung_jawab',
        'no_telepon',
        'keterangan',
    ];

    protected $casts = [
        'nilai_bagi_hasil' => 'float',
    ];

    public function pelanggan()
    {
        return $this->hasMany(PelangganWifi::class, 'provider_wifi_id');
    }
}
