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

        $pelangganList = $query->orderBy('no')->orderBy('nama')->get();

        // Query real payment records according to date range or month/year
        $pembayaranQuery = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganList->pluck('id'))
            ->whereIn('status', ['LUNAS', 'AKTIF']);

        if ($tglDari && $tglSampai) {
            $pembayaranQuery->whereBetween('tanggal_bayar', [$tglDari, $tglSampai]);
        } else {
            $pembayaranQuery->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        }
        $pembayaranRecords = $pembayaranQuery->get()->keyBy('pelanggan_wifi_id');

        $isPastMonth = $tahun < now()->year || ($tahun == now()->year && $bulan < now()->month);
        $referenceDate = $isPastMonth ? \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth() : now();

        $totalPelanggan = $pelangganList->count();
        $totalTarikanRealisasi = 0;
        $totalDasarRealisasi   = 0;
        $totalHasilBumdes      = 0;
        $totalHakProvider      = 0;
        $lunasCount            = 0;
        $belumBayarCount       = 0;

        $pelangganList->transform(function ($item) use ($pembayaranRecords, $referenceDate, &$totalTarikanRealisasi, &$totalDasarRealisasi, &$totalHasilBumdes, &$totalHakProvider, &$lunasCount, &$belumBayarCount) {
            $pay = $pembayaranRecords->get($item->id);
            $item->pembayaran_periode = $pay;

            $provider = $item->provider;
            $tarikan  = (float) $item->total_tarikan;

            // Hitung porsi BUMDes & Provider
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

        // Rekapitulasi Per Provider berdasarkan transaksi riil periode ini
        $rekapPerProvider = ProviderWifi::all()->map(function ($prov) use ($pelangganList) {
            $provPelanggan = $pelangganList->where('provider_wifi_id', $prov->id);
            $pCount        = $provPelanggan->count();
            $lunas         = $provPelanggan->where('status_bayar', 'LUNAS');
            $belum         = $provPelanggan->where('status_bayar', '!=', 'LUNAS');

            return [
                'id'                  => $prov->id,
                'nama_provider'       => $prov->nama_provider,
                'tipe_bagi_hasil'     => $prov->tipe_bagi_hasil,
                'nilai_bagi_hasil'    => $prov->nilai_bagi_hasil,
                'total_pelanggan'     => $pCount,
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'potensi_tarikan'     => $provPelanggan->sum('total_tarikan'),
                'aktif_count'         => $lunas->count(),
                'lunas_count'         => $lunas->count(),
                'isolir_count'        => $belum->count(),
                'belum_bayar_count'   => $belum->count(),
            ];
        });

        // Provider Tanpa Kategori
        $tanpaProvPelanggan = $pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $lunas = $tanpaProvPelanggan->where('status_bayar', 'LUNAS');
            $belum = $tanpaProvPelanggan->where('status_bayar', '!=', 'LUNAS');

            $rekapPerProvider->push([
                'id'                  => 'tanpa_provider',
                'nama_provider'       => 'Tanpa Provider / Umum',
                'tipe_bagi_hasil'     => 'PERSENTASE',
                'nilai_bagi_hasil'    => 9.00,
                'total_pelanggan'     => $tanpaProvPelanggan->count(),
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'potensi_tarikan'     => $tanpaProvPelanggan->sum('total_tarikan'),
                'aktif_count'         => $lunas->count(),
                'lunas_count'         => $lunas->count(),
                'isolir_count'        => $belum->count(),
                'belum_bayar_count'   => $belum->count(),
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
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'provider_id'    => $providerId,
                'tanggal_dari'   => $tglDari,
                'tanggal_sampai' => $tglSampai,
            ],
            'stats'            => [
                'total_pelanggan'     => $totalPelanggan,
                'total_tarikan_bruto' => $totalTarikanRealisasi,
                'total_dasar_non_ppn' => $totalDasarRealisasi,
                'potensi_tarikan'     => $pelangganList->sum('total_tarikan'),
                'total_hasil_bumdes'  => $totalHasilBumdes,
                'total_hak_provider'  => $totalHakProvider,
                'aktif_count'         => $lunasCount,
                'lunas_count'         => $lunasCount,
                'isolir_count'        => $belumBayarCount,
                'belum_bayar_count'   => $belumBayarCount,
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

        $pelangganList = $query->orderBy('no')->orderBy('nama')->get();

        $pembayaranQuery = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganList->pluck('id'))
            ->whereIn('status', ['LUNAS', 'AKTIF']);

        if ($tglDari && $tglSampai) {
            $pembayaranQuery->whereBetween('tanggal_bayar', [$tglDari, $tglSampai]);
        } else {
            $pembayaranQuery->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        }
        $pembayaranRecords = $pembayaranQuery->get()->keyBy('pelanggan_wifi_id');

        $isPastMonth = $tahun < now()->year || ($tahun == now()->year && $bulan < now()->month);
        $referenceDate = $isPastMonth ? \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth() : now();

        $totalPelanggan = $pelangganList->count();
        $totalTarikanRealisasi = 0;
        $totalDasarRealisasi   = 0;
        $totalHasilBumdes      = 0;
        $totalHakProvider      = 0;
        $lunasCount            = 0;
        $belumBayarCount       = 0;

        $pelangganList->transform(function ($item) use ($pembayaranRecords, $referenceDate, &$totalTarikanRealisasi, &$totalDasarRealisasi, &$totalHasilBumdes, &$totalHakProvider, &$lunasCount, &$belumBayarCount) {
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

        $rekapPerProvider = ProviderWifi::all()->map(function ($prov) use ($pelangganList) {
            $provPelanggan = $pelangganList->where('provider_wifi_id', $prov->id);
            $pCount        = $provPelanggan->count();
            $lunas         = $provPelanggan->where('status_bayar', 'LUNAS');
            $belum         = $provPelanggan->where('status_bayar', '!=', 'LUNAS');

            return [
                'id'                  => $prov->id,
                'nama_provider'       => $prov->nama_provider,
                'tipe_bagi_hasil'     => $prov->tipe_bagi_hasil,
                'nilai_bagi_hasil'    => $prov->nilai_bagi_hasil,
                'total_pelanggan'     => $pCount,
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'potensi_tarikan'     => $provPelanggan->sum('total_tarikan'),
                'aktif_count'         => $lunas->count(),
                'lunas_count'         => $lunas->count(),
                'isolir_count'        => $belum->count(),
                'belum_bayar_count'   => $belum->count(),
            ];
        });

        $tanpaProvPelanggan = $pelangganList->whereNull('provider_wifi_id');
        if ($tanpaProvPelanggan->count() > 0) {
            $lunas = $tanpaProvPelanggan->where('status_bayar', 'LUNAS');
            $belum = $tanpaProvPelanggan->where('status_bayar', '!=', 'LUNAS');

            $rekapPerProvider->push([
                'id'                  => 'tanpa_provider',
                'nama_provider'       => 'Tanpa Provider / Umum',
                'tipe_bagi_hasil'     => 'PERSENTASE',
                'nilai_bagi_hasil'    => 9.00,
                'total_pelanggan'     => $tanpaProvPelanggan->count(),
                'total_tarikan'       => $lunas->sum('nominal_masuk'),
                'total_dasar_non_ppn' => $lunas->sum('dasar_masuk'),
                'total_hasil_bumdes'  => $lunas->sum('bumdes_masuk'),
                'total_hak_provider'  => $lunas->sum('provider_masuk'),
                'potensi_tarikan'     => $tanpaProvPelanggan->sum('total_tarikan'),
                'aktif_count'         => $lunas->count(),
                'lunas_count'         => $lunas->count(),
                'isolir_count'        => $belum->count(),
                'belum_bayar_count'   => $belum->count(),
            ]);
        }

        if ($providerId) {
            $rekapPerProvider = $rekapPerProvider->filter(function ($item) use ($providerId) {
                return (string) $item['id'] === (string) $providerId;
            })->values();
        }

        $stats = [
            'total_pelanggan'     => $totalPelanggan,
            'total_tarikan_bruto' => $totalTarikanRealisasi,
            'potensi_tarikan'     => $pelangganList->sum('total_tarikan'),
            'total_hasil_bumdes'  => $totalHasilBumdes,
            'total_hak_provider'  => $totalHakProvider,
            'aktif_count'         => $lunasCount,
            'lunas_count'         => $lunasCount,
            'isolir_count'        => $belumBayarCount,
            'belum_bayar_count'   => $belumBayarCount,
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
