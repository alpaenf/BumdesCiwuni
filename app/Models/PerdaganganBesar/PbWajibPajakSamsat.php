<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbWajibPajakSamsat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_wajib_pajak_samsat';

    protected $fillable = [
        'nama_wajib_pajak',
        'nik',
        'alamat_lengkap',
        'nomor_hp',
        'jenis_kendaraan',
        'nomor_plat',
        'merek_tipe',
        'tahun_kendaraan',
        'tanggal_jatuh_tempo',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date:Y-m-d',
    ];

    public function transaksi()
    {
        return $this->hasMany(PbTransaksiSamsat::class, 'wajib_pajak_id');
    }
}

