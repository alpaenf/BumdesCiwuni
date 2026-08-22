<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait ComputesWifiStatus
 *
 * Provides a centralised helper to derive the real-time billing status of a
 * WiFi customer, including automatic ISOLIR after the billing wave deadline.
 *
 * Rules:
 *  - If a payment record exists for the current period/gelombang → use that status.
 *  - If NO payment exists:
 *      Gelombang 1 (tgl 1-15):
 *        - Today <= 15  → BELUM_BAYAR (still within payment window)
 *        - Today >= 16  → ISOLIR (deadline passed, auto-isolir)
 *      Gelombang 2 (tgl 16-akhir):
 *        - Today <= lastDay  → BELUM_BAYAR (still within payment window)
 *        - Next month started → ISOLIR (deadline passed, auto-isolir)
 */
trait ComputesWifiStatus
{
    /**
     * Derive the real-time billing status for a single customer.
     *
     * @param  \App\Models\PelangganWifi  $pelanggan
     * @param  \App\Models\PembayaranWifi|null  $payRecord  Payment record for the relevant period
     * @param  Carbon|null  $referenceDate  Date to evaluate against (defaults to now())
     * @return string  One of: LUNAS | TUNGGAKAN | ISOLIR | BELUM_BAYAR
     */
    protected function computeCurrentStatus($pelanggan, $payRecord, ?Carbon $referenceDate = null): string
    {
        // If there's an actual payment record, trust that status.
        if ($payRecord) {
            return $payRecord->status ?? 'LUNAS';
        }

        $today    = $referenceDate ?? now();
        $gelombang = $pelanggan->gelombang ?? '1_15';

        if ($gelombang === '1_15') {
            // Gelombang 1: payment window = 1st to 15th of the month
            // After the 15th (day >= 16), customer is ISOLIR
            if ($today->day >= 16) {
                return 'ISOLIR';
            }
            // Still within payment window
            return 'BELUM_BAYAR';
        } else {
            // Gelombang 2: payment window = 16th to last day of the month
            // After month ends (first day of next month), customer is ISOLIR
            $lastDayOfMonth = $today->copy()->endOfMonth()->day;
            $isNewMonth     = $today->day < 16;

            if ($isNewMonth) {
                // We've crossed into next month's Gelombang 1 period.
                // Gelombang 2 deadline has passed → ISOLIR
                return 'ISOLIR';
            }
            // Still within payment window (day 16 - last day)
            return 'BELUM_BAYAR';
        }
    }
}
