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
}

