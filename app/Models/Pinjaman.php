<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    protected $table = 'pinjaman';

    protected $fillable = [
        'nasabah_id',
        'tanggal_akad',
        'pinjaman_pokok',
        'bunga',
        'total_tagihan',
        'nominal_setoran',
        'biaya_tambahan',
        'jumlah_angsuran',
        'sisa_pinjaman',
        'status',
        'foto_perjanjian',
    ];

    protected $casts = [
        'tanggal_akad' => 'date',
        'pinjaman_pokok' => 'decimal:2',
        'bunga' => 'decimal:2',
        'total_tagihan' => 'decimal:2',
        'nominal_setoran' => 'decimal:2',
        'biaya_tambahan' => 'decimal:2',
        'sisa_pinjaman' => 'decimal:2',
    ];

    protected $appends = ['foto_perjanjian_url'];

    public function getFotoPerjanjianUrlAttribute(): ?string
    {
        return $this->foto_perjanjian ? asset('uploads/pinjaman/' . $this->foto_perjanjian) : null;
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function angsuran(): HasMany
    {
        return $this->hasMany(Angsuran::class);
    }
}
