<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpOtherIncome extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_other_incomes';

    protected $fillable = [
        'unit_kategori',
        'tanggal',
        'uraian',
        'kategori',
        'nominal',
        'keterangan',
    ];
}

