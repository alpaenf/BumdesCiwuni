<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kp_egg_sales', function (Blueprint $table) {
            $table->decimal('ambil_kandang_kg', 12, 2)->default(0)->after('jumlah_kg');
            $table->decimal('ambil_rekanan_kg', 12, 2)->default(0)->after('ambil_kandang_kg');
            $table->string('sumber_stok')->default('kandang')->after('ambil_rekanan_kg'); // kandang, rekanan, gabungan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kp_egg_sales', function (Blueprint $table) {
            $table->dropColumn(['ambil_kandang_kg', 'ambil_rekanan_kg', 'sumber_stok']);
        });
    }
};
