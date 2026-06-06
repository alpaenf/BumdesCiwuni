<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriUnit extends Model
{
    protected $table = 'galeri_unit';

    protected $fillable = [
        'unit',
        'foto',
        'keterangan',
        'urutan',
    ];
}
