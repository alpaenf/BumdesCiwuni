<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaksi Pajak Bumi dan Bangunan (PBB)
        Schema::create('pb_transaksi_pbb', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique(); // TRX-PBB-YYYYMMDD-XXXX
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('nama_admin');
            $table->string('nama_wajib_pajak');
            $table->string('nop'); // Nomor Objek Pajak (string to preserve leading zeroes)
            $table->text('alamat_objek_pajak')->nullable();
            $table->string('tahun_pajak', 10);
            
            // Nominal
            $table->decimal('nominal_pokok', 15, 2)->default(0);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            
            $table->enum('metode_pembayaran', ['CASH', 'TRANSFER'])->default('CASH');
            $table->enum('status', ['SUCCES', 'PENDING', 'CANCEL'])->default('SUCCES');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_transaksi_pbb');
    }
};

