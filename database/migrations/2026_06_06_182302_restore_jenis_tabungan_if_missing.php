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
        if (!Schema::hasColumn('tabungan', 'jenis_tabungan')) {
            Schema::table('tabungan', function (Blueprint $table) {
                $table->string('jenis_tabungan')->default('reguler')->after('nasabah_id');
            });
        }
    }

    public function down(): void
    {
        // Don't drop it in down() to be safe
    }
};
