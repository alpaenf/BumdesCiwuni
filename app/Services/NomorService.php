<?php

namespace App\Services;

use App\Models\Nasabah;

class NomorService
{
    /**
     * Generate nomor registrasi baru.
     * Format: angka urut mulai dari 934
     */
    public function generateNomorRegistrasi(): string
    {
        $last = Nasabah::orderByDesc('id')->value('nomor_registrasi');

        if (!$last) {
            return '1';
        }

        $next = ((int) $last) + 1;

        return (string) $next;
    }

    public function generateNomorRekening(): string
    {
        $year = now()->year;
        $suffix = (string) $year; // "2026"
        $sufLen = strlen($suffix); // 4

        // Format standar = tepat 8 karakter: 4 digit seq (1001-9999) + 4 digit tahun
        // Nomor custom (lebih dari 8 karakter atau format lain) TIDAK ikut dihitung,
        // sehingga urutan auto selalu bersih dari 1001 ke atas.
        $standardLength = 4 + $sufLen; // 8 karakter

        $maxSeq = 1000; // default, akan jadi 1001 saat di-increment

        Nasabah::whereNotNull('nomor_rekening')->pluck('nomor_rekening')->each(function ($rekening) use (&$maxSeq, $suffix, $sufLen, $standardLength) {
            $rekening = trim($rekening);

            // Hanya proses format standar: tepat 8 digit numerik, diakhiri tahun ini
            if (
                is_numeric($rekening) &&
                strlen($rekening) === $standardLength &&
                str_ends_with($rekening, $suffix)
            ) {
                $seq = (int) substr($rekening, 0, -$sufLen);
                if ($seq >= 1001 && $seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }

            // Kompatibilitas format lama: 00001.2026 (seq maks 5 digit sebelum titik)
            if (str_contains($rekening, '.')) {
                $parts = explode('.', $rekening);
                $seq = (int) $parts[0];
                // geser ke rentang 1001+ agar tidak bertabrakan
                $seq = $seq < 1001 ? $seq + 1000 : $seq;
                // hanya ikut dihitung jika masih dalam range standar (1001-9999)
                if ($seq >= 1001 && $seq <= 9999 && $seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        });

        return ($maxSeq + 1) . $year;
    }

    /**
     * Generate nomor transaksi tabungan.
     * Format: T06260001 (Reguler) atau TS06260001 (Sembako)
     */
    public function generateNomorTransaksiTabungan(string $jenisTabungan = 'reguler'): string
    {
        $ym = now()->format('my');
        $code = $jenisTabungan === 'sembako' ? 'TS' : 'T';
        $prefix = "{$code}{$ym}";

        $last = \App\Models\TransaksiTabungan::where('nomor_transaksi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_transaksi');

        if (!$last) {
            $seq = 1;
        } else {
            $prefixLen = strlen($prefix);
            $seqStr = substr($last, $prefixLen);
            $seq = ((int) $seqStr) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor pinjaman.
     * Format: P06260001
     */
    public function generateNomorPinjaman(): string
    {
        $ym = now()->format('my');
        $prefix = "P{$ym}";

        $last = \App\Models\Pinjaman::where('nomor_transaksi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_transaksi');

        if (!$last) {
            $seq = 1;
        } else {
            $seqStr = substr($last, 5);
            $seq = ((int) $seqStr) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor angsuran.
     * Format: A06260001
     */
    public function generateNomorAngsuran(): string
    {
        $ym = now()->format('my');
        $prefix = "A{$ym}";

        $last = \App\Models\Angsuran::where('nomor_transaksi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_transaksi');

        if (!$last) {
            $seq = 1;
        } else {
            $seqStr = substr($last, 5);
            $seq = ((int) $seqStr) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor kwitansi.
     * Format: K260531001 (10 karakter)
     */
    public function generateNomorKwitansi(): string
    {
        $today = now()->format('ymd');
        $prefix = "K{$today}";

        $last = \App\Models\Kwitansi::where('nomor_kwitansi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_kwitansi');

        if (!$last) {
            $seq = 1;
        } else {
            $seqStr = substr($last, 7);
            $seq = ((int) $seqStr) + 1;
        }

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Konversi angka ke terbilang Bahasa Indonesia.
     */
    public function terbilang(float $angka): string
    {
        $angka = (int) round(abs($angka));
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
            'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas',
            'tujuh belas', 'delapan belas', 'sembilan belas'];

        if ($angka < 20) {
            return $bilangan[$angka];
        }

        if ($angka < 100) {
            return $bilangan[(int) floor($angka / 10)] . ' puluh' . ($angka % 10 ? ' ' . $bilangan[$angka % 10] : '');
        }

        if ($angka < 200) {
            return 'seratus' . ($angka % 100 ? ' ' . $this->terbilang($angka % 100) : '');
        }

        if ($angka < 1000) {
            return $bilangan[(int) floor($angka / 100)] . ' ratus' . ($angka % 100 ? ' ' . $this->terbilang($angka % 100) : '');
        }

        if ($angka < 2000) {
            return 'seribu' . ($angka % 1000 ? ' ' . $this->terbilang($angka % 1000) : '');
        }

        if ($angka < 1_000_000) {
            return $this->terbilang((int) floor($angka / 1000)) . ' ribu' . ($angka % 1000 ? ' ' . $this->terbilang($angka % 1000) : '');
        }

        if ($angka < 1_000_000_000) {
            return $this->terbilang((int) floor($angka / 1_000_000)) . ' juta' . ($angka % 1_000_000 ? ' ' . $this->terbilang($angka % 1_000_000) : '');
        }

        return $this->terbilang((int) floor($angka / 1_000_000_000)) . ' miliar' . ($angka % 1_000_000_000 ? ' ' . $this->terbilang($angka % 1_000_000_000) : '');
    }
}
