<?php

namespace App\Models\PerdaganganBesar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PbTransaksiLakuPandai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pb_transaksi_laku_pandai';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'nama_admin',
        'nasabah_id',
        'nama_nasabah',
        'jenis_transaksi',
        'rekening_masuk',
        'rekening_keluar',
        'cash_masuk',
        'cash_keluar',
        'harga_agen',
        'harga_konsumen',
        'pendapatan_jasa_cash',
        'pendapatan_jasa_rekening',
        'status_pembayaran',
        'jenis_pembayaran',
        'saldo_rekening',
        'saldo_cash',
        'total_saldo',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'rekening_masuk' => 'decimal:2',
        'rekening_keluar' => 'decimal:2',
        'cash_masuk' => 'decimal:2',
        'cash_keluar' => 'decimal:2',
        'harga_agen' => 'decimal:2',
        'harga_konsumen' => 'decimal:2',
        'pendapatan_jasa_cash' => 'decimal:2',
        'pendapatan_jasa_rekening' => 'decimal:2',
        'saldo_rekening' => 'decimal:2',
        'saldo_cash' => 'decimal:2',
        'total_saldo' => 'decimal:2',
    ];

    public function nasabah()
    {
        return $this->belongsTo(PbNasabahLakuPandai::class, 'nasabah_id');
    }

    /**
     * Total Pendapatan Jasa = Pendapatan Jasa Cash + Pendapatan Jasa Rekening
     */
    public function getTotalPendapatanJasaAttribute(): float
    {
        return (float)$this->pendapatan_jasa_cash + (float)$this->pendapatan_jasa_rekening;
    }

    /**
     * Sinkronisasi dan Rekalkulasi Saldo Berjalan (Rekening, Cash, dan Total)
     * Menjamin integritas saldo kapan pun ada create, update, delete, atau restore transaksi.
     */
    public static function syncAndRecalculate(): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $transactions = self::orderBy('tanggal')
                ->orderBy('id')
                ->get();

            $runningRek = 0;
            $runningCash = 0;

            foreach ($transactions as $trx) {
                $runningRek += ((float)$trx->rekening_masuk - (float)$trx->rekening_keluar);
                $runningCash += ((float)$trx->cash_masuk - (float)$trx->cash_keluar);
                $runningTotal = $runningRek + $runningCash;

                $newRek = round($runningRek, 2);
                $newCash = round($runningCash, 2);
                $newTotal = round($runningTotal, 2);

                if (
                    (float)$trx->saldo_rekening !== $newRek ||
                    (float)$trx->saldo_cash !== $newCash ||
                    (float)$trx->total_saldo !== $newTotal
                ) {
                    $trx->updateQuietly([
                        'saldo_rekening' => $newRek,
                        'saldo_cash' => $newCash,
                        'total_saldo' => $newTotal,
                    ]);
                }
            }

            return [
                'saldo_rekening' => round($runningRek, 2),
                'saldo_cash' => round($runningCash, 2),
                'total_saldo' => round($runningRek + $runningCash, 2),
            ];
        });
    }
}

