<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Melakukan backup database MySQL secara otomatis ke dalam folder storage/app/backups';

    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql';
        $storagePath = storage_path('app/backups');

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $path = $storagePath . '/' . $filename;

        // Mengambil kredensial dari .env
        $host = env('DB_HOST');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');

        // Jika ada password, tambahkan flag -p
        $passwordStr = empty($password) ? '' : "-p{$password}";

        // Jalankan perintah mysqldump
        $command = "mysqldump -h {$host} -u {$username} {$passwordStr} {$database} > \"{$path}\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("Backup berhasil dibuat: {$filename}");
            Log::info("AUTO-BACKUP: Database berhasil dibackup ke {$filename}");
            
            // Opsional: Hapus backup yang lebih lama dari 7 hari
            $this->cleanOldBackups($storagePath);
        } else {
            $this->error("Backup gagal!");
            Log::error("AUTO-BACKUP: Gagal melakukan backup database. Pastikan 'mysqldump' bisa dijalankan di command line.");
        }
    }

    private function cleanOldBackups($storagePath)
    {
        $files = glob($storagePath . '/*.sql');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * 7) { // 7 Hari
                    unlink($file);
                }
            }
        }
    }
}
