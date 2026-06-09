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
            $table->string('nomor_transaksi')->nullable()->after('id');
        });
        Schema::table('angsuran', function (Blueprint $table) {
            $table->string('nomor_transaksi')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropColumn('nomor_transaksi');
        });
        Schema::table('angsuran', function (Blueprint $table) {
            $table->dropColumn('nomor_transaksi');
        });
    }
};
