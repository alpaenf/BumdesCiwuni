<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_wifi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_wifi_id')->constrained('pelanggan_wifi')->onDelete('cascade');
            $table->string('no_transaksi')->unique()->comment('Nomor unik transaksi, misal TRX-WF-202608-0001');
            $table->unsignedTinyInteger('periode_bulan')->comment('Bulan tagihan 1-12');
            $table->unsignedSmallInteger('periode_tahun')->comment('Tahun tagihan misal 2026');
            $table->enum('gelombang', ['1_15', '16_30'])->default('1_15')->comment('Gelombang pembayaran');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['TUNAI', 'TRANSFER'])->default('TUNAI');
            $table->enum('status', ['LUNAS', 'TUNGGAKAN', 'ISOLIR'])->default('LUNAS');
            $table->text('catatan')->nullable();
            $table->foreignId('kasir_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexing for performance
            $table->index(['periode_tahun', 'periode_bulan', 'gelombang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_wifi');
    }
};
