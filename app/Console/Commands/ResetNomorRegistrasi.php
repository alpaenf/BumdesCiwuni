<?php

namespace App\Console\Commands;

use App\Models\Nasabah;
use Illuminate\Console\Command;

class ResetNomorRegistrasi extends Command
{
    protected $signature = 'reg:reset';
    protected $description = 'Reset ulang Nomor Registrasi (No. Reg) nasabah agar mulai berurutan dari angka 1';

    public function handle()
    {
        if (!$this->confirm('Yakin ingin mereset seluruh No. Reg menjadi mulai dari 1 secara berurutan?')) {
            return self::SUCCESS;
        }

        // Urutkan berdasarkan nomor rekening agar sinkron dengan urutan rekening
        $nasabahList = Nasabah::orderBy('nomor_rekening')->get();
        $counter = 1;

        foreach ($nasabahList as $nasabah) {
            $nasabah->updateQuietly(['nomor_registrasi' => (string) $counter]);
            $counter++;
        }

        $this->info("Berhasil mereset No. Reg untuk {$nasabahList->count()} nasabah.");
        return self::SUCCESS;
    }
}
