<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelangganWifi extends Model
{
    use HasFactory;

    protected $table = 'pelanggan_wifi';

    protected $fillable = [
        'provider_wifi_id',
        'no',
        'nama',
        'tanggal_daftar',
        'paket',
        'nik',
        'alamat',
        'rt',
        'rw',
        'no_id_pel',
        'no_wa',
        'total_dasar_tarikan_non_ppn',
        'ppn_dan_pph',
        'ppn_pph',
        'total_tarikan',
        'bagi_hasil_bumdes',
        'hasil_bumdes',
        'nota_bayar_provider',
        'total_provider',
        'gelombang',
        'status_1_15',
        'status_16_30',
        'gps_long',
        'gps_lat',
        'foto_rumah',
    ];

    protected $casts = [
        'tanggal_daftar'              => 'date',
        'total_dasar_tarikan_non_ppn' => 'float',
        'ppn_dan_pph'                 => 'float',
        'ppn_pph'                     => 'float',
        'total_tarikan'               => 'float',
        'bagi_hasil_bumdes'           => 'float',
        'hasil_bumdes'                => 'float',
        'nota_bayar_provider'         => 'float',
        'total_provider'              => 'float',
        'gps_long'                    => 'float',
        'gps_lat'                     => 'float',
    ];

    protected $appends = ['foto_rumah_url'];

    /**
     * Accessor: URL publik foto rumah.
     */
    public function getFotoRumahUrlAttribute(): ?string
    {
        if (!$this->foto_rumah) {
            return null;
        }
        // If already a full URL or starts with /
        if (str_starts_with($this->foto_rumah, 'http') || str_starts_with($this->foto_rumah, '/')) {
            return $this->foto_rumah;
        }
        return asset('uploads/pelanggan_wifi/' . $this->foto_rumah);
    }

    /**
     * Scope: filter by paket.
     */
    public function scopePaket($query, string $paket)
    {
        return $query->where('paket', $paket);
    }

    /**
     * Scope: filter by gelombang tagihan warga.
     */
    public function scopeGelombang($query, string $gelombang)
    {
        return $query->where('gelombang', $gelombang);
    }

    /**
     * Relasi ke riwayat pembayaran.
     */
    public function pembayaran()
    {
        return $this->hasMany(PembayaranWifi::class, 'pelanggan_wifi_id')->orderByDesc('id');
    }

    /**
     * Relasi ke provider wifi.
     */
    public function provider()
    {
        return $this->belongsTo(ProviderWifi::class, 'provider_wifi_id');
    }
}
