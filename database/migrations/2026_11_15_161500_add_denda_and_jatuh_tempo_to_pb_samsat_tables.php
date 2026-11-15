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
        Schema::table('pb_wajib_pajak_samsat', function (Blueprint $table) {
            $table->date('tanggal_jatuh_tempo')->nullable()->after('tahun_kendaraan');
        });

        Schema::table('pb_transaksi_samsat', function (Blueprint $table) {
            $table->date('tanggal_jatuh_tempo')->nullable()->after('jenis_pembayaran');
            $table->decimal('denda_pajak', 15, 2)->default(0)->after('nominal_pajak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pb_wajib_pajak_samsat', function (Blueprint $table) {
            $table->dropColumn('tanggal_jatuh_tempo');
        });

        Schema::table('pb_transaksi_samsat', function (Blueprint $table) {
            $table->dropColumn(['tanggal_jatuh_tempo', 'denda_pajak']);
        });
    }
};

