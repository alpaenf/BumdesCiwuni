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
        Schema::table('pb_nasabah_laku_pandai', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('nama');
            // Kendaraan Samsat: [{ nomor_plat, jenis_kendaraan, merek_tipe, tahun_kendaraan, tanggal_jatuh_tempo }]
            $table->json('kendaraan_list')->nullable()->after('rekening_list');
            // Objek Pajak PBB: [{ nop, alamat_objek_pajak, nominal_sppt_terakhir }]
            $table->json('pbb_list')->nullable()->after('kendaraan_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pb_nasabah_laku_pandai', function (Blueprint $table) {
            $table->dropColumn(['nik', 'kendaraan_list', 'pbb_list']);
        });
    }
};

