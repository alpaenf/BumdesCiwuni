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
        Schema::table('provider_wifi', function (Blueprint $table) {
            $table->string('header_wa')->nullable()->after('nama_provider');
            $table->json('bank_accounts')->nullable()->after('header_wa');
        });
    }

    public function down(): void
    {
        Schema::table('provider_wifi', function (Blueprint $table) {
            $table->dropColumn(['header_wa', 'bank_accounts']);
        });
    }
};
