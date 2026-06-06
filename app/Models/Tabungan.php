<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tabungan extends Model
{
    public const JENIS_REGULER = 'reguler';
    public const JENIS_SEMBAKO = 'sembako';

    protected $table = 'tabungan';

    protected $fillable = [
        'nasabah_id',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiTabungan::class);
    }
}
