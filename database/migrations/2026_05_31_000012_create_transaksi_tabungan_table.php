<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_tabungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabungan_id')->constrained('tabungan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('nomor_transaksi')->unique();
            $table->string('jenis_transaksi');
            $table->string('keterangan')->nullable();
            $table->decimal('nominal', 15, 2);
            $table->decimal('administrasi', 15, 2)->default(0);
            $table->decimal('saldo_setelah', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_tabungan');
    }
};
