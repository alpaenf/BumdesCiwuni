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
        Schema::create('kp_partner_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_rekanan'); // Nama rekanan / pembeli rekanan
            $table->string('no_hp')->nullable();
            $table->decimal('jumlah_kg', 12, 2)->default(0); // Berapa kg dari luar (TIDAK masuk ke stok internal)
            $table->decimal('harga_per_kg', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->string('status_pembayaran')->default('Lunas'); // Lunas / Belum Lunas
            $table->string('metode_pembayaran')->default('Tunai'); // Tunai / Transfer
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kp_partner_purchases');
    }
};
