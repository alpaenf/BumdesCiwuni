<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengisi nilai awal Dasar Tarikan Non PPN (148.500) dan Bagi Hasil 9% (13.365)
     * untuk pelanggan paket 11 Mbps / tarif 165.000.
     */
    public function up(): void
    {
        DB::table('pelanggan_wifi')
            ->where(function ($q) {
                $q->where('total_tarikan', 165000)
                  ->orWhere('paket', 'like', '%11%');
            })
            ->update([
                'total_provider'              => 148500,
                'total_dasar_tarikan_non_ppn' => 148500,
                'bagi_hasil_bumdes'           => 9.00,
                'hasil_bumdes'                => 13365,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
