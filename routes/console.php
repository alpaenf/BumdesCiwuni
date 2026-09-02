<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan backup database setiap hari jam 2 pagi
Schedule::command('db:backup')->dailyAt('02:00');

Artisan::command('wifi:sync-dasar-non-ppn', function () {
    $this->info('Memulai sinkronisasi Dasar Tarikan Non PPN pelanggan WiFi...');

    $pelangganList = \App\Models\PelangganWifi::where(function ($q) {
        $q->where('total_tarikan', 165000)
          ->orWhere('paket', 'like', '%11%');
    })->get();

    $count = $pelangganList->count();
    $this->info("Ditemukan {$count} pelanggan untuk diperbarui.");

    $updated = 0;
    foreach ($pelangganList as $p) {
        $p->total_provider              = 148500;
        $p->total_dasar_tarikan_non_ppn = 148500;
        $p->bagi_hasil_bumdes           = 9.00;
        $p->hasil_bumdes                = 13365;
        $p->save();
        $updated++;
    }

    $this->info("Berhasil memperbarui {$updated} data pelanggan WiFi.");
})->purpose('Singkronisasi nilai Dasar Tarikan Non PPN (148.500) dan Hak BUMDes 9% (13.365) untuk pelanggan WiFi paket 11 Mbps');
