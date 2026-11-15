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
        Schema::table('kp_egg_productions', function (Blueprint $table) {
            $table->integer('produksi_butir')->default(0)->after('hari_ke');
            $table->decimal('butir_per_kg', 8, 2)->nullable()->default(16)->after('produksi_butir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kp_egg_productions', function (Blueprint $table) {
            $table->dropColumn(['produksi_butir', 'butir_per_kg']);
        });
    }
};
