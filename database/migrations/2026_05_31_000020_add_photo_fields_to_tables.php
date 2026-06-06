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
        Schema::table('nasabah', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->string('foto_perjanjian')->nullable()->after('status');
        });

        Schema::table('kwitansi', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('keperluan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropColumn('foto');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropColumn('foto_perjanjian');
        });

        Schema::table('kwitansi', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
