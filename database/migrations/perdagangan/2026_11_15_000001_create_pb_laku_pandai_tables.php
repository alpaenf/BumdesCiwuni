<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data Nasabah Laku Pandai
        Schema::create('pb_nasabah_laku_pandai', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('no_hp')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Transaksi Harian Laku Pandai (INPUT TRANS HAR)
        Schema::create('pb_transaksi_laku_pandai', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal');
            $table->string('nama_admin');
            $table->foreignId('nasabah_id')->nullable()->constrained('pb_nasabah_laku_pandai')->nullOnDelete();
            $table->string('nama_nasabah');
            $table->string('jenis_transaksi'); // Deposit, Tarik tunai, Transfer sesama, Administrasi bank, Bunga, Pulsa data, Pulsa listrik, Pajak PBB, Pajak kendaraan, PDAM, Transfer antarbank, Virtual account, Multi payment, Pelunasan hutang
            
            // Arus Rekening & Kas
            $table->decimal('rekening_masuk', 15, 2)->default(0);
            $table->decimal('rekening_keluar', 15, 2)->default(0);
            $table->decimal('cash_masuk', 15, 2)->default(0);
            $table->decimal('cash_keluar', 15, 2)->default(0);
            
            // Harga & Pendapatan
            $table->decimal('harga_agen', 15, 2)->default(0);
            $table->decimal('harga_konsumen', 15, 2)->default(0);
            $table->decimal('pendapatan_jasa_cash', 15, 2)->default(0);
            $table->decimal('pendapatan_jasa_rekening', 15, 2)->default(0);
            
            // Status & Metode
            $table->enum('status_pembayaran', ['Lunas', 'Hutang'])->default('Lunas');
            $table->enum('jenis_pembayaran', ['Cash', 'Transfer'])->default('Cash');
            
            // Saldo Berjalan (Snapshot)
            $table->decimal('saldo_rekening', 15, 2)->default(0);
            $table->decimal('saldo_cash', 15, 2)->default(0);
            $table->decimal('total_saldo', 15, 2)->default(0);
            
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_transaksi_laku_pandai');
        Schema::dropIfExists('pb_nasabah_laku_pandai');
    }
};

