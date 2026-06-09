<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Angsuran extends Model
{
    protected $table = 'angsuran';

    protected $fillable = [
        'pinjaman_id',
        'nomor_transaksi',
        'tanggal',
        'angsuran_ke',
        'pasaran',
        'jumlah_bayar',
        'sisa_pinjaman',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_bayar' => 'decimal:2',
        'sisa_pinjaman' => 'decimal:2',
    ];

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }
}
