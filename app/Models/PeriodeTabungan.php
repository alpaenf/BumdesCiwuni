<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeTabungan extends Model
{
    protected $table = 'periode_tabungan';
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_tutup', 'status'];
}
