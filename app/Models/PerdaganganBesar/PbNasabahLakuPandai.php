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
        'no_hp',
        'no_rekening',
        'nama_bank',
        'alamat',
        'keterangan',
    ];

    public function transaksi()
    {
        return $this->hasMany(PbTransaksiLakuPandai::class, 'nasabah_id');
    }
}

