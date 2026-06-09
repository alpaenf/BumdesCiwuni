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

    public function generateNomorRekening(): string
    {
        $year = now()->year;
        
        // Ambil nomor rekening terakhir untuk melanjutkan urutan
        $last = Nasabah::orderByDesc('id')->value('nomor_rekening');

        $seq = 1001; // Urutan default awal

        if ($last) {
            // Cek jika nomor rekening sebelumnya menggunakan format lama (misal: 00001.2026)
            if (str_contains($last, '.')) {
                $parts = explode('.', $last);
                $oldSeq = (int) $parts[0];
                $seq = $oldSeq >= 1001 ? $oldSeq + 1 : 1001;
            } 
            // Cek jika nomor rekening sebelumnya menggunakan format baru (misal: 10012026)
            elseif (strlen($last) >= 8 && is_numeric($last)) {
                $seqStr = substr($last, 0, -4); // Ambil urutannya saja dengan membuang 4 digit terakhir (tahun)
                $seq = ((int) $seqStr) + 1;
            }
        }

        return $seq . $year;
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
