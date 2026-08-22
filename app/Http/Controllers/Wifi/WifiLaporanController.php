<?php

namespace App\Http\Controllers\Wifi;

use App\Http\Controllers\Controller;
use App\Models\PelangganWifi;
use App\Models\PembayaranWifi;
use App\Models\ProviderWifi;
use App\Models\Unit;
use App\Traits\ComputesWifiStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WifiLaporanController extends Controller
{
    use ComputesWifiStatus;
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'wifi')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized access to WiFi unit.');
        }
        return $unit;
    }

    /**
     * Halaman Laporan WiFi per Provider & Rekap BUMDes
     */
    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $bulan      = (int) $request->input('bulan', now()->month);
        $tahun      = (int) $request->input('tahun', now()->year);
        $providerId = $request->input('provider_id');
        $tglDari    = $request->input('tanggal_dari');
        $tglSampai  = $request->input('tanggal_sampai');

        $providersList = ProviderWifi::orderBy('nama_provider')->get();

        // ── Data Pelanggan & Rekap Per Provider ───────────────────────
        $query = PelangganWifi::with(['provider']);

        if ($providerId) {
            if ($providerId === 'tanpa_provider') {
                $query->whereNull('provider_wifi_id');
            } else {
                $query->where('provider_wifi_id', $providerId);
            }
        }

        $pelangganList = $query->orderBy('nama')->get();

        // Query real payment records according to date range or month/year
        $pembayaranQuery = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganList->pluck('id'));
        if ($tglDari && $tglSampai) {
            $pembayaranQuery->whereBetween('tanggal_bayar', [$tglDari, $tglSampai]);
        } else {
            $pembayaranQuery->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        }
        $pembayaranRecords = $pembayaranQuery->get()->keyBy('pelanggan_wifi_id');

        $pelangganList->transform(function ($item) use ($pembayaranRecords) {
            $pay = $pembayaranRecords->get($item->id);
            $item->current_status     = $this->computeCurrentStatus($item, $pay);
            $item->pembayaran_periode = $pay;
            return $item;
        });

        // ── Summary Metrics ──────────────────────────────────────────
        $totalPelanggan = $pelangganList->count();
        $totalTarikanBruto = $pelangganList->sum('total_tarikan');
        $totalHasilBumdes  = $pelangganList->sum('hasil_bumdes');
        $totalHakProvider  = $pelangganList->sum('total_provider');

        // Status Lunas vs Tunggakan vs Isolir untuk periode aktif
        $totalLunas115     = $pelangganList->where('current_status', 'LUNAS')->count();
        $totalTunggakan115 = $pelangganList->where('current_status', 'TUNGGAKAN')->count();
        $totalIsolir115    = $pelangganList->where('current_status', 'ISOLIR')->count();

        // Rekapitulasi Per Provider Grouping
        $rekapPerProvider = ProviderWifi::all()->map(function ($prov) use ($pelangganList) {
            $provPelanggan = $pelangganList->where('provider_wifi_id', $prov->id);
            $pCount    = $provPelanggan->count();
            $tarikan   = $provPelanggan->sum('total_tarikan');
            $bumdes    = $provPelanggan->sum('hasil_bumdes');
            $provider  = $provPelanggan->sum('total_provider');
            $lunas     = $provPelanggan->where('current_status', 'LUNAS')->count();
            $tunggakan = $provPelanggan->whereIn('current_status', ['TUNGGAKAN', 'ISOLIR'])->count();

            return [
                'id'                 => $prov->id,
                'nama_provider'      => $prov->nama_provider,
                'tipe_bagi_hasil'    => $prov->tipe_bagi_hasil,
                'nilai_bagi_hasil'   => $prov->nilai_bagi_hasil,
                'total_pelanggan'    => $pCount,
                'total_tarikan'      => $tarikan,
                'total_hasil_bumdes' => $bumdes,
                'total_hak_provider' => $provider,
                'lunas_count'        => $lunas,
                'tunggakan_count'    => $tunggakan,
            ];
        });

        // Provider Tanpa Kategori
        $tanpaProvPelanggan = $pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $rekapPerProvider->push([
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

        // Filter $rekapPerProvider if $providerId is set
        if ($providerId) {
            $rekapPerProvider = $rekapPerProvider->filter(function ($item) use ($providerId) {
                return (string) $item['id'] === (string) $providerId;
            })->values();
        }

        return Inertia::render('Wifi/Laporan', [
            'unit'             => $unit,
            'user'             => $user,
            'providersList'    => $providersList,
            'pelangganList'    => $pelangganList,
            'rekapPerProvider' => $rekapPerProvider,
            'filters'          => [
                'bulan'        => $bulan,
                'tahun'        => $tahun,
                'provider_id'  => $providerId,
                'tanggal_dari'   => $tglDari,
                'tanggal_sampai' => $tglSampai,
            ],
            'stats'            => [
                'total_pelanggan'     => $totalPelanggan,
                'total_tarikan_bruto' => $totalTarikanBruto,
                'total_hasil_bumdes'  => $totalHasilBumdes,
                'total_hak_provider'  => $totalHakProvider,
                'lunas_115'           => $totalLunas115,
                'tunggakan_115'       => $totalTunggakan115,
                'isolir_115'          => $totalIsolir115,
            ],
        ]);
    }

    /**
     * Printable PDF / HTML Report of Laporan WiFi Per Provider
     */
    public function cetakPdf(Request $request)
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $bulan      = (int) $request->input('bulan', now()->month);
        $tahun      = (int) $request->input('tahun', now()->year);
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

        $pelangganList = $query->orderBy('nama')->get();

        $pembayaranQuery = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganList->pluck('id'));
        if ($tglDari && $tglSampai) {
            $pembayaranQuery->whereBetween('tanggal_bayar', [$tglDari, $tglSampai]);
        } else {
            $pembayaranQuery->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        }
        $pembayaranRecords = $pembayaranQuery->get()->keyBy('pelanggan_wifi_id');

        $pelangganList->transform(function ($item) use ($pembayaranRecords) {
            $pay = $pembayaranRecords->get($item->id);
            $item->current_status     = $this->computeCurrentStatus($item, $pay);
            $item->pembayaran_periode = $pay;
            return $item;
        });

        $totalPelanggan = $pelangganList->count();
        $totalTarikanBruto = $pelangganList->sum('total_tarikan');
        $totalHasilBumdes  = $pelangganList->sum('hasil_bumdes');
        $totalHakProvider  = $pelangganList->sum('total_provider');

        $rekapPerProvider = ProviderWifi::all()->map(function ($prov) use ($pelangganList) {
            $provPelanggan = $pelangganList->where('provider_wifi_id', $prov->id);
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

        $tanpaProvPelanggan = $pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $rekapPerProvider->push([
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
            $rekapPerProvider = $rekapPerProvider->filter(function ($item) use ($providerId) {
                return (string) $item['id'] === (string) $providerId;
            })->values();
        }

        $stats = [
            'total_pelanggan'     => $totalPelanggan,
            'total_tarikan_bruto' => $totalTarikanBruto,
            'total_hasil_bumdes'  => $totalHasilBumdes,
            'total_hak_provider'  => $totalHakProvider,
        ];

        return view('wifi.cetak_laporan_pdf', compact('unit', 'user', 'pelangganList', 'rekapPerProvider', 'stats', 'bulan', 'tahun', 'providerId', 'tglDari', 'tglSampai'));
    }

    /**
     * Export Excel (.xlsx) Laporan WiFi Per Provider
     */
    public function export(Request $request)
    {
        $this->authorizeUnit();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\WifiLaporanExport($request),
            'laporan_wifi_provider_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
