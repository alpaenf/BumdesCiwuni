<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_unit',
        'slug',
        'logo',
        'thumbnail',
        'deskripsi',
        'tipe',
        'status',
        'api_url',
        'api_key',
        'icon',
        'urutan',
    ];

    /**
     * Scope: only active units.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope: only internal units.
     */
    public function scopeInternal($query)
    {
        return $query->where('tipe', 'internal');
    }

    /**
     * Scope: only external units.
     */
    public function scopeExternal($query)
    {
        return $query->where('tipe', 'external');
    }

    /**
     * Check if unit is active.
     */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Check if unit is external.
     */
    public function isExternal(): bool
    {
        return $this->tipe === 'external';
    }
}
