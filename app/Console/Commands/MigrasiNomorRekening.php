<?php

namespace App\Console\Commands;

use App\Models\Nasabah;
use Illuminate\Console\Command;

class MigrasiNomorRekening extends Command
{
    protected $signature   = 'rekening:migrasi {--dry-run : Tampilkan preview tanpa menyimpan}';
    protected $description = 'Migrasi nomor rekening lama ke format standar (1001-9999 + tahun). Nomor custom diletakkan di akhir range.';

    public function handle(): int
    {
        $year      = now()->year;
        $suffix    = (string) $year;
        $stdLength = 8; // 4 digit seq + 4 digit tahun
        $isDryRun  = $this->option('dry-run');

        $nasabahList = Nasabah::orderBy('id')->get(['id', 'nomor_rekening', 'nama']);

        $standardOnes = [];   // format lama yang bisa dikonversi (00001.2026)
        $customOnes   = [];   // format custom (tidak bisa dikonversi otomatis)
        $alreadyOk    = [];   // sudah format standar baru

        foreach ($nasabahList as $nasabah) {
            $rek = trim($nasabah->nomor_rekening ?? '');

            // Sudah format standar baru (8 digit numerik diakhiri tahun, seq 1001-9999)
            if (is_numeric($rek) && strlen($rek) === $stdLength && str_ends_with($rek, $suffix)) {
                $seq = (int) substr($rek, 0, -4);
                if ($seq >= 1001 && $seq <= 9999) {
                    $alreadyOk[] = $nasabah;
                    continue;
                }
            }

            // Format lama: 00001.2026
            if (str_contains($rek, '.')) {
                $standardOnes[] = $nasabah;
                continue;
            }

            // Selainnya: custom
            $customOnes[] = $nasabah;
        }

        $this->info("Total nasabah    : {$nasabahList->count()}");
        $this->info("Sudah standar    : " . count($alreadyOk));
        $this->info("Format lama      : " . count($standardOnes));
        $this->info("Nomor custom     : " . count($customOnes));
        $this->newLine();

        if (empty($standardOnes) && empty($customOnes)) {
            $this->info('✅ Semua nomor rekening sudah dalam format standar.');
            return self::SUCCESS;
        }

        // Tentukan seq mulai dari mana setelah yang sudah ok
        $maxStdSeq = 1000;
        foreach ($alreadyOk as $n) {
            $seq = (int) substr($n->nomor_rekening, 0, -4);
            if ($seq > $maxStdSeq) $maxStdSeq = $seq;
        }

        $rows = [];

        // Konversi format lama dulu
        foreach ($standardOnes as $nasabah) {
            $maxStdSeq++;
            $newRek  = $maxStdSeq . $year;
            $rows[] = [
                'nasabah' => $nasabah,
                'lama'    => $nasabah->nomor_rekening,
                'baru'    => $newRek,
                'jenis'   => 'Format lama',
            ];
        }

        // Custom diletakkan di akhir range (mulai dari 9001)
        $customSeq = 9000;
        foreach ($customOnes as $nasabah) {
            $customSeq++;
            $newRek  = $customSeq . $year;
            $rows[] = [
                'nasabah' => $nasabah,
                'lama'    => $nasabah->nomor_rekening,
                'baru'    => $newRek,
                'jenis'   => 'Custom',
            ];
        }

        // Tampilkan tabel preview
        $this->table(
            ['ID', 'Nama', 'Rekening Lama', 'Rekening Baru', 'Jenis'],
            array_map(fn($r) => [
                $r['nasabah']->id,
                $r['nasabah']->nama,
                $r['lama'],
                $r['baru'],
                $r['jenis'],
            ], $rows)
        );

        if ($isDryRun) {
            $this->warn('⚠  Dry-run mode: tidak ada perubahan yang disimpan.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Lanjutkan migrasi nomor rekening?', false)) {
            $this->info('Dibatalkan.');
            return self::SUCCESS;
        }

        foreach ($rows as $row) {
            $row['nasabah']->update(['nomor_rekening' => $row['baru']]);
        }

        $this->info('✅ Migrasi selesai! ' . count($rows) . ' nomor rekening berhasil diperbarui.');
        return self::SUCCESS;
    }
}
