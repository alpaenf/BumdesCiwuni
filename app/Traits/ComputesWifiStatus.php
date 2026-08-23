<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait ComputesWifiStatus
 *
 * Computes real-time WiFi customer status based on deadline (10th of current month).
 * Status is strictly one of TWO categories:
 *  - AKTIF (Lunas / Active payment period tgl 1-10)
 *  - ISOLIR (Unpaid after deadline tgl 10, i.e. tgl 11 onwards)
 */
trait ComputesWifiStatus
{
    /**
     * Derive the real-time billing status for a single customer.
     *
     * @param  \App\Models\PelangganWifi  $pelanggan
     * @param  \App\Models\PembayaranWifi|null  $payRecord  Payment record for the current month
     * @param  Carbon|null  $referenceDate  Date to evaluate against (defaults to now())
     * @return string  'AKTIF' | 'ISOLIR'
     */
    protected function computeCurrentStatus($pelanggan, $payRecord, ?Carbon $referenceDate = null): string
    {
        // 1. If paid in current month -> AKTIF
        if ($payRecord && in_array(strtoupper($payRecord->status ?? ''), ['LUNAS', 'AKTIF'])) {
            return 'AKTIF';
        }

        // 2. If deadline passed (today's day >= 11) -> ISOLIR
        $today = $referenceDate ?? now();
        if ($today->day >= 11) {
            return 'ISOLIR';
        }

        // 3. Within payment window (tgl 1 - 10) -> AKTIF
        return 'AKTIF';
    }
}
