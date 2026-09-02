<?php

namespace App\Exports;

use App\Models\PelangganWifi;
use App\Models\ProviderWifi;
use App\Traits\ComputesWifiStatus;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class WifiLaporanExport implements FromView, ShouldAutoSize, WithDrawings, WithTitle
{
    use WithLogo, ComputesWifiStatus;

    public function title(): string
    {
        return 'Laporan WiFi';
    }

    private $pelangganList;
    private $rekapPerProvider;
    private $filters;
    private $stats;
    private $bulan;
    private $tahun;

    public function __construct(Request $request)
    {
        $this->bulan      = (int) $request->input('bulan', now()->month);
        $this->tahun      = (int) $request->input('tahun', now()->year);
        $providerId = $request->input('provider_id');
        $tglDari    = $request->input('tanggal_dari');
        $tglSampai  = $request->input('tanggal_sampai');

        $query = PelangganWifi::with(['provider']);

        if ($providerId) {
            if ($providerId === 'tanpa_provider') {
                $query->whereNull('provider_wifi_id');
            } else {
                $query->where('provider_wifi_id', $providerId);
            }
        }

        $this->pelangganList = $query->orderBy('nama')->get();

        $pembayaranQuery = \App\Models\PembayaranWifi::whereIn('pelanggan_wifi_id', $this->pelangganList->pluck('id'));
        if ($tglDari && $tglSampai) {
            $pembayaranQuery->whereBetween('tanggal_bayar', [$tglDari, $tglSampai]);
        } else {
            $pembayaranQuery->where('periode_bulan', $this->bulan)->where('periode_tahun', $this->tahun);
        }
        $pembayaranRecords = $pembayaranQuery->get()->keyBy('pelanggan_wifi_id');

        $isPastMonth = $this->tahun < now()->year || ($this->tahun == now()->year && $this->bulan < now()->month);
        $referenceDate = $isPastMonth ? \Carbon\Carbon::create($this->tahun, $this->bulan, 1)->endOfMonth() : now();

        $totalTarikanRealisasi = 0;
        $totalDasarRealisasi   = 0;
        $totalTunai            = 0;
        $totalTransfer         = 0;
        $totalHasilBumdes      = 0;
        $totalHakProvider      = 0;
        $lunasCount            = 0;
        $belumBayarCount       = 0;

        $this->pelangganList->transform(function ($item) use ($pembayaranRecords, $referenceDate, &$totalTarikanRealisasi, &$totalDasarRealisasi, &$totalTunai, &$totalTransfer, &$totalHasilBumdes, &$totalHakProvider, &$lunasCount, &$belumBayarCount) {
            $pay = $pembayaranRecords->get($item->id);
            $item->pembayaran_periode = $pay;

            $provider = $item->provider;
            $tarikan  = (float) $item->total_tarikan;

            if ($provider && $provider->tipe_bagi_hasil === 'FLAT_ADMIN') {
                $adminFee     = (float) ($provider->nilai_bagi_hasil > 0 ? $provider->nilai_bagi_hasil : ($item->hasil_bumdes ?: 5000));
                $bumdesPart   = min($adminFee, $tarikan);
                $providerPart = max(0, $tarikan - $bumdesPart);
                $dasarProvider = $tarikan;
            } else {
                $pct = (float) ($item->bagi_hasil_bumdes ?: ($provider->nilai_bagi_hasil ?? 9.00));
                if ($pct <= 0) $pct = 9.00;
                $dasarProvider = (float) ($item->total_provider > 0 ? $item->total_provider : $tarikan);
                $bumdesPart   = round($dasarProvider * ($pct / 100));
                $providerPart = max(0, $dasarProvider - $bumdesPart);
            }

            $item->calc_hasil_bumdes   = $bumdesPart;
            $item->calc_total_provider = $providerPart;
            $item->dasar_provider      = $dasarProvider;

            if ($pay) {
                $item->status_bayar   = 'LUNAS';
                $item->current_status = 'LUNAS';
                $item->nominal_masuk  = (float) $pay->jumlah_bayar;
                $item->dasar_masuk    = $dasarProvider;
                $item->bumdes_masuk   = $bumdesPart;
                $item->provider_masuk = $providerPart;

                $metode = strtoupper(trim($pay->metode_pembayaran ?? 'TUNAI'));
                if (in_array($metode, ['TRANSFER', 'BANK', 'QRIS'])) {
                    $totalTransfer += $item->nominal_masuk;
                } else {
                    $totalTunai += $item->nominal_masuk;
                }

                $totalTarikanRealisasi += $item->nominal_masuk;
                $totalDasarRealisasi   += $dasarProvider;
                $totalHasilBumdes      += $bumdesPart;
                $totalHakProvider      += $providerPart;
                $lunasCount++;
            } else {
                $item->status_bayar    = 'BELUM_BAYAR';
                $item->current_status  = $this->computeCurrentStatus($item, null, $referenceDate);
                $item->nominal_masuk   = 0;
                $item->dasar_masuk     = 0;
                $item->bumdes_masuk    = 0;
                $item->provider_masuk  = 0;
                $belumBayarCount++;
            }

            return $item;
        });

        $rekap = ProviderWifi::all()->map(function ($prov) {
            $provPelanggan = $this->pelangganList->where('provider_wifi_id', $prov->id);
            $lunas         = $provPelanggan->where('status_bayar', 'LUNAS');
            $belum         = $provPelanggan->where('status_bayar', '!=', 'LUNAS');

            return [
                'id'                  => $prov->id,
                'nama_provider'       => $prov->nama_provider,
                'tipe_bagi_hasil'     => $prov->tipe_bagi_hasil,
                'nilai_bagi_hasil'    => $prov->nilai_bagi_hasil,
                'total_pelanggan'     => $provPelanggan->count(),
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'lunas_count'         => $lunas->count(),
                'tunggakan_count'     => $belum->count(),
            ];
        });

        $tanpaProvPelanggan = $this->pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $lunas = $tanpaProvPelanggan->where('status_bayar', 'LUNAS');
            $belum = $tanpaProvPelanggan->where('status_bayar', '!=', 'LUNAS');

            $rekap->push([
                'id'                  => 'tanpa_provider',
                'nama_provider'       => 'Tanpa Provider / Umum',
                'tipe_bagi_hasil'     => 'PERSENTASE',
                'nilai_bagi_hasil'    => 9.00,
                'total_pelanggan'     => $tanpaProvPelanggan->count(),
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'lunas_count'         => $lunas->count(),
                'tunggakan_count'     => $belum->count(),
            ]);
        }

        if ($providerId) {
            $rekap = $rekap->filter(function ($item) use ($providerId) {
                return (string) $item['id'] === (string) $providerId;
            })->values();
        }

        $this->rekapPerProvider = $rekap;

        $this->filters = [
            'bulan'          => $this->bulan,
            'tahun'          => $this->tahun,
            'provider_id'    => $providerId,
            'tanggal_dari'   => $tglDari,
            'tanggal_sampai' => $tglSampai,
        ];

        $this->stats = [
            'total_pelanggan'     => $this->pelangganList->count(),
            'total_tarikan_bruto' => $totalTarikanRealisasi,
            'total_dasar_non_ppn' => $totalDasarRealisasi,
            'total_tunai'         => $totalTunai,
            'total_transfer'      => $totalTransfer,
            'potensi_tarikan'     => $this->pelangganList->sum('total_tarikan'),
            'total_hasil_bumdes'  => $totalHasilBumdes,
            'total_hak_provider'  => $totalHakProvider,
            'lunas_count'         => $lunasCount,
            'belum_bayar_count'   => $belumBayarCount,
        ];
    }

    public function view(): View
    {
        return view('exports.wifi.laporan_excel', [
            'pelangganList'    => $this->pelangganList,
            'rekapPerProvider' => $this->rekapPerProvider,
            'filters'          => $this->filters,
            'stats'            => $this->stats,
            'bulan'            => $this->bulan,
            'tahun'            => $this->tahun,
            'isExcel'          => true,
        ]);
    }
}
