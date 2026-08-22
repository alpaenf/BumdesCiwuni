<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_wifi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_provider');
            $table->enum('tipe_bagi_hasil', ['PERSENTASE', 'FLAT_ADMIN'])->default('PERSENTASE');
            $table->decimal('nilai_bagi_hasil', 15, 2)->default(9.00); // 9% atau Rp 15.000
            $table->string('penanggung_jawab')->nullable();
            $table->string('no_telepon')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Add provider_wifi_id to pelanggan_wifi
        if (Schema::hasTable('pelanggan_wifi') && !Schema::hasColumn('pelanggan_wifi', 'provider_wifi_id')) {
            Schema::table('pelanggan_wifi', function (Blueprint $table) {
                $table->foreignId('provider_wifi_id')->nullable()->after('id')->constrained('provider_wifi')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pelanggan_wifi') && Schema::hasColumn('pelanggan_wifi', 'provider_wifi_id')) {
            Schema::table('pelanggan_wifi', function (Blueprint $table) {
                $table->dropForeign(['provider_wifi_id']);
                $table->dropColumn('provider_wifi_id');
            });
        }
        Schema::dropIfExists('provider_wifi');
    }
};
