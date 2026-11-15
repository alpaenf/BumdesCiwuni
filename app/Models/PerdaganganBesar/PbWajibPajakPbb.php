<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbWajibPajakPbb extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_wajib_pajak_pbb';

    protected $fillable = [
        'nama_wajib_pajak',
        'nop',
        'nik',
        'nomor_hp',
        'alamat_objek_pajak',
        'alamat_wajib_pajak',
        'nominal_sppt_terakhir',
        'keterangan',
    ];

    protected $casts = [
        'nominal_sppt_terakhir' => 'decimal:2',
    ];

    public function transaksi()
    {
        return $this->hasMany(PbTransaksiPbb::class, 'wajib_pajak_pbb_id');
    }
}

