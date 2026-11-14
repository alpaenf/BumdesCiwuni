<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buku Kas Masuk & Kas Keluar Unit
        Schema::create('pb_kas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori_layanan')->default('Laku Pandai'); // Laku Pandai, Samsat Budiman, PBB, Transaksi Umum, Operasional
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->decimal('nominal', 15, 2);
            $table->enum('tipe_kas', ['Tunai', 'Rekening'])->default('Tunai');
            $table->string('nama_admin');
            $table->text('keterangan');
            $table->string('bukti_transaksi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Biaya Operasional Unit
        Schema::create('pb_biaya_operasional', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kategori_biaya'); // Administrasi, ATK, Honor, Listrik & Internet, Lain-lain
            $table->string('uraian');
            $table->decimal('nominal', 15, 2);
            $table->string('nama_admin');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_biaya_operasional');
        Schema::dropIfExists('pb_kas');
    }
};

