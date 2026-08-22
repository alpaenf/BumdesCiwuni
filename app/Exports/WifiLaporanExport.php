<?php

namespace App\Exports;

use App\Models\PelangganWifi;
use App\Models\ProviderWifi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;

class WifiLaporanExport implements FromView, ShouldAutoSize, WithDrawings, WithTitle
{
    use WithLogo;

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

        $this->pelangganList->transform(function ($item) use ($pembayaranRecords) {
            $pay = $pembayaranRecords->get($item->id);
            $item->current_status = $pay?->status ?? ($item->gelombang === '16_30' ? $item->status_16_30 : $item->status_1_15);
            $item->pembayaran_periode = $pay;
            return $item;
        });

        $rekap = ProviderWifi::all()->map(function ($prov) {
            $provPelanggan = $this->pelangganList->where('provider_wifi_id', $prov->id);
            return [
                'id'                 => $prov->id,
                'nama_provider'      => $prov->nama_provider,
                'tipe_bagi_hasil'    => $prov->tipe_bagi_hasil,
                'nilai_bagi_hasil'   => $prov->nilai_bagi_hasil,
                'total_pelanggan'    => $provPelanggan->count(),
                'total_tarikan'      => $provPelanggan->sum('total_tarikan'),
                'total_hasil_bumdes' => $provPelanggan->sum('hasil_bumdes'),
                'total_hak_provider' => $provPelanggan->sum('total_provider'),
                'lunas_count'        => $provPelanggan->where('current_status', 'LUNAS')->count(),
                'tunggakan_count'    => $provPelanggan->whereIn('current_status', ['TUNGGAKAN', 'ISOLIR'])->count(),
            ];
        });

        $tanpaProvPelanggan = $this->pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $rekap->push([
                'id'                 => 'tanpa_provider',
                'nama_provider'      => 'Tanpa Provider / Umum',
                'tipe_bagi_hasil'    => 'PERSENTASE',
                'nilai_bagi_hasil'   => 9.00,
                'total_pelanggan'    => $tanpaProvPelanggan->count(),
                'total_tarikan'      => $tanpaProvPelanggan->sum('total_tarikan'),
                'total_hasil_bumdes' => $tanpaProvPelanggan->sum('hasil_bumdes'),
                'total_hak_provider' => $tanpaProvPelanggan->sum('total_provider'),
                'lunas_count'        => $tanpaProvPelanggan->where('current_status', 'LUNAS')->count(),
                'tunggakan_count'    => $tanpaProvPelanggan->whereIn('current_status', ['TUNGGAKAN', 'ISOLIR'])->count(),
            ]);
        }

        if ($providerId) {
            $rekap = $rekap->filter(function ($item) use ($providerId) {
                return (string) $item['id'] === (string) $providerId;
            })->values();
        }

        $this->rekapPerProvider = $rekap;

        $this->filters = [
            'bulan'        => $this->bulan,
            'tahun'        => $this->tahun,
            'provider_id'  => $providerId,
            'tanggal_dari'   => $tglDari,
            'tanggal_sampai' => $tglSampai,
        ];

        $this->stats = [
            'total_pelanggan'     => $this->pelangganList->count(),
            'total_tarikan_bruto' => $this->pelangganList->sum('total_tarikan'),
            'total_hasil_bumdes'  => $this->pelangganList->sum('hasil_bumdes'),
            'total_hak_provider'  => $this->pelangganList->sum('total_provider'),
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
