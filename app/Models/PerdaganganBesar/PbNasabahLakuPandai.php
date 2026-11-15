<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbNasabahLakuPandai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_nasabah_laku_pandai';

    protected $fillable = [
        'nama',
        'nik',
        'no_hp',
        'no_rekening',
        'nama_bank',
        'rekening_list',
        'kendaraan_list',
        'pbb_list',
        'alamat',
        'keterangan',
    ];

    protected $casts = [
        'rekening_list' => 'array',
        'kendaraan_list' => 'array',
        'pbb_list' => 'array',
    ];

    public function transaksi()
    {
        return $this->hasMany(PbTransaksiLakuPandai::class, 'nasabah_id');
    }
}
