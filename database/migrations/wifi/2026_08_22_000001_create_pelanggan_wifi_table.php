<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan_wifi', function (Blueprint $table) {
            $table->id();
            $table->integer('no')->nullable()->comment('Nomor urut');
            $table->string('nama');
            $table->date('tanggal_daftar')->nullable();
            $table->string('paket')->nullable();
            $table->string('nik')->nullable()->comment('Nomor Induk Kependudukan (simpan sebagai string)');
            $table->text('alamat')->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('no_id_pel')->unique()->nullable()->comment('Nomor ID Pelanggan, unik');
            $table->string('no_wa')->nullable()->comment('Nomor WhatsApp (simpan sebagai string)');
            $table->decimal('total_dasar_tarikan_non_ppn', 15, 2)->nullable()->default(0);
            $table->decimal('ppn_dan_pph', 15, 2)->nullable()->default(0);
            $table->decimal('ppn_pph', 15, 2)->nullable()->default(0);
            $table->decimal('total_tarikan', 15, 2)->nullable()->default(0);
            $table->decimal('bagi_hasil_bumdes', 15, 2)->nullable()->default(0);
            $table->decimal('hasil_bumdes', 15, 2)->nullable()->default(0);
            $table->decimal('nota_bayar_provider', 15, 2)->nullable()->default(0);
            $table->decimal('total_provider', 15, 2)->nullable()->default(0);
            $table->enum('status_1_15', ['LUNAS', 'TUNGGAKAN', 'ISOLIR'])->nullable();
            $table->enum('status_16_30', ['LUNAS', 'TUNGGAKAN', 'ISOLIR'])->nullable();
            $table->decimal('gps_long', 11, 8)->nullable()->comment('Longitude GPS');
            $table->decimal('gps_lat', 10, 8)->nullable()->comment('Latitude GPS');
            $table->string('foto_rumah')->nullable()->comment('Path foto rumah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan_wifi');
    }
};
