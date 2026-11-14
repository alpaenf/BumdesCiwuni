<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaksi Layanan Umum & PPOB
        Schema::create('pb_transaksi_umum', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('nama_admin');
            $table->string('nama_pelanggan');
            $table->string('nomor_transaksi')->unique(); // e.g. TRX-PLS-, TRX-PLN-, TRX-PDAM-, TRX-BPJS-, TRX-WF-, etc.
            $table->string('jenis_transaksi'); // Pulsa listrik, PDAM, BPJS, WIFI, Pulsa, Pajak kendaraan, Virtual account, Deposit, Transfer sesama, Transfer antarbank, Administrasi bank, Pulsa data, Pajak PBB, Pelunasan hutang, Multi payment
            $table->string('nomor_tujuan'); // No ID / No Rekening / No Pelanggan / ID Meter / No HP
            
            // Komponen Biaya (Total bayar = Biaya pokok + Biaya admin + Biaya jasa agen)
            $table->decimal('biaya_pokok', 15, 2)->default(0);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('biaya_jasa_agen', 15, 2)->default(0);
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
        Schema::dropIfExists('pb_transaksi_umum');
    }
};

