<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['internal', 'external'])->default('internal');
            $table->enum('status', ['aktif', 'coming_soon', 'nonaktif'])->default('coming_soon');
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('icon')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
