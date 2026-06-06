<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->date('tanggal_akad');
            $table->decimal('pinjaman_pokok', 15, 2);
            $table->decimal('bunga', 5, 2)->default(10);
            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('nominal_setoran', 15, 2);
            $table->unsignedInteger('jumlah_angsuran');
            $table->decimal('sisa_pinjaman', 15, 2);
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
