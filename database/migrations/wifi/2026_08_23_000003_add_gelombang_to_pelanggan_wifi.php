<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan_wifi', function (Blueprint $table) {
            // Kolom gelombang: jadwal tagihan warga (Gel.1 atau Gel.2)
            $table->enum('gelombang', ['1_15', '16_30'])->nullable()->after('no_id_pel')
                ->comment('Jadwal gelombang tagihan warga: 1_15 = Tgl 1-15, 16_30 = Tgl 16-Akhir Bulan');
        });

        // Migrate existing data: if status_16_30 is set and status_1_15 is null -> gelombang = 16_30
        // else default to 1_15 (most common)
        DB::statement("
            UPDATE pelanggan_wifi
            SET gelombang = CASE
                WHEN status_16_30 IS NOT NULL AND status_1_15 IS NULL THEN '16_30'
                ELSE '1_15'
            END
        ");
    }

    public function down(): void
    {
        Schema::table('pelanggan_wifi', function (Blueprint $table) {
            $table->dropColumn('gelombang');
        });
    }
};
