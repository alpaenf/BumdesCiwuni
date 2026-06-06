<?php

namespace App\Services;

use App\Models\Pinjaman;
use Carbon\Carbon;

class PinjamanStatusService
{
    public function status(Pinjaman $pinjaman, ?Carbon $today = null): string
    {
        if ($pinjaman->status === 'lunas' || $pinjaman->sisa_pinjaman <= 0) {
            return 'lunas';
        }

        $today = $today ?? now();
        $jadwalHari = (int) config('simpanpinjam.jadwal_angsuran_hari', 30);
        $macetHari = (int) config('simpanpinjam.kredit_macet_hari', 90);

        $tanggalAkad = Carbon::parse($pinjaman->tanggal_akad);
        $lastPaidAt = $pinjaman->angsuran->max('tanggal');
        $lastPaidAt = $lastPaidAt ? Carbon::parse($lastPaidAt) : null;
        $daysSinceLast = $lastPaidAt ? $lastPaidAt->diffInDays($today) : $tanggalAkad->diffInDays($today);

        if ($daysSinceLast >= $macetHari) {
            return 'kredit-macet';
        }

        $expectedCount = (int) floor($tanggalAkad->diffInDays($today) / max(1, $jadwalHari));
        $paidCount = $pinjaman->angsuran->count();
        $lateCount = max(0, $expectedCount - $paidCount);

        if ($lateCount > 2) {
            return 'menunggak';
        }

        if ($lateCount >= 1) {
            return 'perhatian-khusus';
        }

        return 'lancar';
    }
}
