<?php

namespace App\Models\KetahananPangan;

use App\Models\LandingPageSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpCashFlow extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_cash_flows';

    protected $fillable = [
        'unit_kategori',
        'tanggal',
        'uraian',
        'debit',
        'kredit',
        'saldo_berjalan',
        'sumber_tipe',
        'sumber_id',
        'keterangan',
    ];

    /**
     * Sinkronisasi data arus kas: membersihkan orphan records jika data sumber terhapus,
     * serta merekap running balance secara kronologis akurat.
     */
    public static function syncAndRecalculate(string $unitKategori = 'hewani'): float
    {
        // 1. Bersihkan orphan cash flow yang sumber transaksinya sudah terhapus
        $activeFlows = self::where('unit_kategori', $unitKategori)->get();
        foreach ($activeFlows as $flow) {
            if ($flow->sumber_tipe === 'expense') {
                if (!KpExpense::where('id', $flow->sumber_id)->exists()) {
                    $flow->forceDelete();
                }
            } elseif ($flow->sumber_tipe === 'feed_purchase') {
                if (!KpFeedPurchase::where('id', $flow->sumber_id)->exists()) {
                    $flow->forceDelete();
                }
            } elseif ($flow->sumber_tipe === 'partner_purchase') {
                if (!KpPartnerPurchase::where('id', $flow->sumber_id)->exists()) {
                    $flow->forceDelete();
                }
            } elseif ($flow->sumber_tipe === 'egg_sale') {
                if (!KpEggSale::where('id', $flow->sumber_id)->exists()) {
                    $flow->forceDelete();
                }
            } elseif ($flow->sumber_tipe === 'other_income') {
                if (!KpOtherIncome::where('id', $flow->sumber_id)->exists()) {
                    $flow->forceDelete();
                }
            }
        }

        // 2. Ambil Modal Awal / Saldo Awal dari Setting
        $modalAwal = (float) LandingPageSetting::getByKey("kp_{$unitKategori}_modal_awal", '0');

        // 3. Urutkan secara kronologis untuk rekalkulasi running balance
        $flows = self::where('unit_kategori', $unitKategori)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $running = $modalAwal;
        foreach ($flows as $flow) {
            $running += ((float) $flow->debit - (float) $flow->kredit);
            if ((float) $flow->saldo_berjalan !== (float) $running) {
                $flow->update(['saldo_berjalan' => $running]);
            }
        }

        return $running;
    }
}
