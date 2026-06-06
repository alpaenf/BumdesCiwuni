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
            return '934';
        }

        $next = ((int) $last) + 1;

        return (string) $next;
    }

    /**
     * Generate nomor rekening baru.
     * Format: 00001.2026
     */
    public function generateNomorRekening(): string
    {
        $year = now()->year;
        $last = Nasabah::where('nomor_rekening', 'like', "%.{$year}")
            ->orderByDesc('id')
            ->value('nomor_rekening');

        if (!$last) {
            $seq = 1;
        } else {
            $parts = explode('.', $last);
            $seq = ((int) $parts[0]) + 1;
        }

        return str_pad($seq, 5, '0', STR_PAD_LEFT) . ".{$year}";
    }

    /**
     * Generate nomor transaksi tabungan.
     * Format: TRX-20260531-0001
     */
    public function generateNomorTransaksi(): string
    {
        $today = now()->format('Ymd');
        $prefix = "TRX-{$today}-";

        $last = \App\Models\TransaksiTabungan::where('nomor_transaksi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_transaksi');

        if (!$last) {
            $seq = 1;
        } else {
            $parts = explode('-', $last);
            $seq = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate nomor kwitansi.
     * Format: KWT-20260531-0001
     */
    public function generateNomorKwitansi(): string
    {
        $today = now()->format('Ymd');
        $prefix = "KWT-{$today}-";

        $last = \App\Models\Kwitansi::where('nomor_kwitansi', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('nomor_kwitansi');

        if (!$last) {
            $seq = 1;
        } else {
            $parts = explode('-', $last);
            $seq = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
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
