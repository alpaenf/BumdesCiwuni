<?php

namespace App\Console\Commands;

use App\Models\PelangganWifi;
use Illuminate\Console\Command;

class SyncDasarTarikanWifi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wifi:sync-dasar-non-ppn {--force : Jalankan update tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Singkronisasi nilai Dasar Tarikan Non PPN (148.500) dan Hak BUMDes 9% (13.365) untuk pelanggan WiFi paket 11 Mbps';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi Dasar Tarikan Non PPN pelanggan WiFi...');

        $pelangganList = PelangganWifi::where(function ($q) {
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
        return 0;
    }
}
