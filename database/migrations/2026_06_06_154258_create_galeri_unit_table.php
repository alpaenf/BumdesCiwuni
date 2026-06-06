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
        Schema::create('galeri_unit', function (Blueprint $table) {
            $table->id();
            $table->string('unit')->default('simpan-pinjam'); // untuk multi-unit di masa depan
            $table->string('foto');                           // path file gambar
            $table->string('keterangan')->nullable();         // caption opsional
            $table->unsignedInteger('urutan')->default(0);   // urutan tampil
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeri_unit');
    }
};
