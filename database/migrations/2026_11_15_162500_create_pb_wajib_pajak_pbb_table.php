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
        // 1. Buat tabel master objek / wajib pajak PBB
        Schema::create('pb_wajib_pajak_pbb', function (Blueprint $table) {
            $table->id();
            $table->string('nama_wajib_pajak');
            $table->string('nop', 50)->unique();
            $table->string('nik', 30)->nullable();
            $table->string('nomor_hp', 25)->nullable();
            $table->text('alamat_objek_pajak')->nullable();
            $table->text('alamat_wajib_pajak')->nullable();
            $table->decimal('nominal_sppt_terakhir', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tambahkan relasi wajib_pajak_pbb_id ke tabel transaksi PBB jika belum ada
        Schema::table('pb_transaksi_pbb', function (Blueprint $table) {
            $table->foreignId('wajib_pajak_pbb_id')->nullable()->after('waktu')->constrained('pb_wajib_pajak_pbb')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pb_transaksi_pbb', function (Blueprint $table) {
            $table->dropForeign(['wajib_pajak_pbb_id']);
            $table->dropColumn('wajib_pajak_pbb_id');
        });

        Schema::dropIfExists('pb_wajib_pajak_pbb');
    }
};
