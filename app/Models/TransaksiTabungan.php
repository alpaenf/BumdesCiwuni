<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiTabungan extends Model
{
    public const JENIS_SETOR = 'setor';
    public const JENIS_TARIK = 'tarik';

    protected $table = 'transaksi_tabungan';

    protected $fillable = [
        'tabungan_id',
        'tanggal',
        'nomor_transaksi',
        'jenis_transaksi',
        'keterangan',
        'nominal',
        'administrasi',
        'saldo_setelah',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'administrasi' => 'decimal:2',
        'saldo_setelah' => 'decimal:2',
    ];

    public function tabungan(): BelongsTo
    {
        return $this->belongsTo(Tabungan::class);
    }
}
