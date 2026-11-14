<?php

namespace App\Models\KetahananPangan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpCashFlow extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_cash_flows';

    protected $fillable = [
        'unit_kategori',
        'tanggal',
        'uraian',
        'debit',
        'kredit',
        'saldo_berjalan',
        'sumber_tipe',
        'sumber_id',
        'keterangan',
    ];
}

