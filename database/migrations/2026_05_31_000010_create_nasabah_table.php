<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nasabah', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_registrasi')->unique();
            $table->string('nomor_rekening')->unique();
            $table->string('nama');
            $table->string('nik', 32)->unique();
            $table->text('alamat');
            $table->string('no_hp', 20);
            $table->string('pekerjaan')->nullable();
            $table->string('jaminan')->nullable();
            $table->date('tanggal_bergabung');
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
};
