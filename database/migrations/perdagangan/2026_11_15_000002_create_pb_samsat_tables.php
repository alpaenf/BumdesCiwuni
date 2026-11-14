<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Database Wajib Pajak & Kendaraan (SAMSAT BUDIMAN)
        Schema::create('pb_wajib_pajak_samsat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_wajib_pajak');
            $table->string('nik')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->string('jenis_kendaraan'); // Sepeda Motor, Mobil, Truk, dll
            $table->string('nomor_plat'); // Plat Nopol
            $table->string('merek_tipe')->nullable(); // Honda Vario 125, Toyota Avanza
            $table->string('tahun_kendaraan', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Transaksi Samsat Budiman
        Schema::create('pb_transaksi_samsat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->date('tanggal');
            $table->foreignId('wajib_pajak_id')->nullable()->constrained('pb_wajib_pajak_samsat')->nullOnDelete();
            $table->string('nama_wajib_pajak');
            $table->string('nomor_polisi');
            $table->string('jenis_kendaraan');
            $table->enum('jenis_pembayaran', ['Cash', 'Transfer'])->default('Cash');
            
            // Komponen Biaya
            $table->decimal('nominal_pajak', 15, 2)->default(0);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('biaya_jasa_bumdes', 15, 2)->default(0);
            $table->decimal('total_pembayaran', 15, 2)->default(0);
            
            $table->enum('status_transaksi', ['SUCCES', 'PENDING', 'CANCEL'])->default('SUCCES');
            $table->string('nama_admin');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_transaksi_samsat');
        Schema::dropIfExists('pb_wajib_pajak_samsat');
    }
};

