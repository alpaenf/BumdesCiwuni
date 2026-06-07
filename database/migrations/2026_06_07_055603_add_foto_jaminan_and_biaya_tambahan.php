<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->string('foto_jaminan')->nullable()->after('jaminan');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->decimal('biaya_tambahan', 15, 2)->default(0)->after('nominal_setoran');
        });
    }

    public function down(): void
    {
        Schema::table('nasabah', function (Blueprint $table) {
            $table->dropColumn('foto_jaminan');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropColumn('biaya_tambahan');
        });
    }
};
