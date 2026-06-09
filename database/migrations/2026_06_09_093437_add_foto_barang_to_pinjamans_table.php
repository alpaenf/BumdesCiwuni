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
        Schema::table('pinjaman', function (Blueprint $table) {
            $table->string('jenis_pinjaman')->default('Uang Tunai')->after('sisa_pinjaman');
            $table->string('keterangan')->nullable()->after('jenis_pinjaman');
            $table->string('foto_barang')->nullable()->after('foto_perjanjian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropColumn(['jenis_pinjaman', 'keterangan', 'foto_barang']);
        });
    }
};
