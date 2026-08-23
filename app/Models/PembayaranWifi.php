<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranWifi extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_wifi';

    protected $fillable = [
        'pelanggan_wifi_id',
        'no_transaksi',
        'periode_bulan',
        'periode_tahun',
        'gelombang',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_pembayaran',
        'status',
        'catatan',
        'kasir_user_id',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar'  => 'float',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    protected $appends = ['wa_struk_link'];

    public function pelanggan()
    {
        return $this->belongsTo(PelangganWifi::class, 'pelanggan_wifi_id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_user_id');
    }

    /**
     * Generate WhatsApp Struk Link
     */
    public function getWaStrukLinkAttribute(): ?string
    {
        if (!$this->pelanggan || !$this->pelanggan->no_wa) {
            return null;
        }

        $phone = preg_replace('/\D/', '', $this->pelanggan->no_wa);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $bulanStr = $namaBulan[$this->periode_bulan] ?? $this->periode_bulan;
        $nominalStr = 'Rp ' . number_format($this->jumlah_bayar, 0, ',', '.');
        $tglBayarStr = $this->tanggal_bayar ? $this->tanggal_bayar->format('d/m/Y') : date('d/m/Y');
        $strukPublicUrl = route('wifi.pembayaran.struk', $this->id);

        $pesan = "*BUKTI PEMBAYARAN WIFI BUMDES CIWUNI*\n";
        $pesan .= "----------------------------------------\n";
        $pesan .= "No. Struk      : *{$this->no_transaksi}*\n";
        $pesan .= "Tanggal        : {$tglBayarStr}\n\n";
        $pesan .= "*DATA PELANGGAN*\n";
        $pesan .= "• Nama         : *{$this->pelanggan->nama}*\n";
        $pesan .= "• ID Pelanggan : *{$this->pelanggan->no_id_pel}*\n";
        $pesan .= "• Paket        : *{$this->pelanggan->paket}*\n";
        $pesan .= "• Periode      : *{$bulanStr} {$this->periode_tahun}*\n\n";
        $pesan .= "*RINCIAN PEMBAYARAN*\n";
        $pesan .= "• Metode Bayar : *{$this->metode_pembayaran}*\n";
        $pesan .= "• Status        : *{$this->status}* 🟢\n";
        $pesan .= "• Total Bayar  : *{$nominalStr}*\n";
        $pesan .= "----------------------------------------\n";
        $pesan .= "📄 *Cetak / Lihat Struk Digital:*\n";
        $pesan .= "{$strukPublicUrl}\n\n";
        $pesan .= "Terima kasih atas pembayaran Anda. Semoga koneksi internet Anda senantiasa lancar! 🙏😊\n\n";
        $pesan .= "_Unit Usaha WiFi BUMDes Ciwuni_";

        return 'https://wa.me/' . $phone . '?text=' . urlencode($pesan);
    }
}
