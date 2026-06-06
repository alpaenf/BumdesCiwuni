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
        // 1. Create periode_tabungan table
        Schema::create('periode_tabungan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_tutup')->nullable();
            $table->enum('status', ['aktif', 'tutup'])->default('aktif');
            $table->timestamps();
        });

        // 2. Data Migration: Merge tabungan sembako to reguler
        // DB::transaction is safer
        \Illuminate\Support\Facades\DB::transaction(function () {
            // First, find all nasabahs
            $nasabahs = \Illuminate\Support\Facades\DB::table('nasabah')->get();
            foreach ($nasabahs as $nasabah) {
                // Get reguler and sembako tabungans
                $reguler = \Illuminate\Support\Facades\DB::table('tabungan')->where('nasabah_id', $nasabah->id)->where('jenis_tabungan', 'reguler')->first();
                $sembako = \Illuminate\Support\Facades\DB::table('tabungan')->where('nasabah_id', $nasabah->id)->where('jenis_tabungan', 'sembako')->first();

                if ($reguler && $sembako) {
                    // Map transactions from sembako to reguler
                    $transactions = \Illuminate\Support\Facades\DB::table('transaksi_tabungan')->where('tabungan_id', $sembako->id)->get();
                    foreach ($transactions as $trx) {
                        $newType = $trx->jenis_transaksi;
                        if ($trx->jenis_transaksi === 'tarik') {
                            $newType = 'tarik_sembako';
                        }
                        \Illuminate\Support\Facades\DB::table('transaksi_tabungan')
                            ->where('id', $trx->id)
                            ->update([
                                'tabungan_id' => $reguler->id,
                                'jenis_transaksi' => $newType,
                            ]);
                    }

                    // Also convert reguler 'tarik' to 'tarik_tunai'
                    \Illuminate\Support\Facades\DB::table('transaksi_tabungan')
                        ->where('tabungan_id', $reguler->id)
                        ->where('jenis_transaksi', 'tarik')
                        ->update(['jenis_transaksi' => 'tarik_tunai']);

                    // Update reguler balance (just the sum of both)
                    \Illuminate\Support\Facades\DB::table('tabungan')
                        ->where('id', $reguler->id)
                        ->update([
                            'saldo' => $reguler->saldo + $sembako->saldo,
                        ]);

                    // Delete the sembako tabungan
                    \Illuminate\Support\Facades\DB::table('tabungan')->where('id', $sembako->id)->delete();
                } else if ($reguler) {
                     // convert reguler 'tarik' to 'tarik_tunai'
                    \Illuminate\Support\Facades\DB::table('transaksi_tabungan')
                        ->where('tabungan_id', $reguler->id)
                        ->where('jenis_transaksi', 'tarik')
                        ->update(['jenis_transaksi' => 'tarik_tunai']);
                }
            }
            
            // Delete any orphaned tabungans that somehow aren't reguler (just in case)
            \Illuminate\Support\Facades\DB::table('tabungan')->where('jenis_tabungan', '!=', 'reguler')->delete();
        });

        // 3. Drop jenis_tabungan column
        Schema::table('tabungan', function (Blueprint $table) {
            $table->dropColumn('jenis_tabungan');
        });
    }

    public function down(): void
    {
        // 1. Add back jenis_tabungan
        Schema::table('tabungan', function (Blueprint $table) {
            $table->string('jenis_tabungan')->default('reguler');
        });

        // Reverting merged balances perfectly is impossible without recalculating from history,
        // but we'll leave it as is for down().

        // 2. Drop periode_tabungan
        Schema::dropIfExists('periode_tabungan');
    }
};
