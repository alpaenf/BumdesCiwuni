<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pinjaman;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pinjamans = Pinjaman::all();
        foreach ($pinjamans as $p) {
            $expected = $p->pinjaman_pokok + ($p->pinjaman_pokok * $p->bunga / 100) + $p->biaya_tambahan;
            if ($p->total_tagihan != $expected) {
                $diff = $expected - $p->total_tagihan;
                $p->total_tagihan = $expected;
                $p->sisa_pinjaman += $diff;
                $p->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
