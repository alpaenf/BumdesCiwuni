<?php

namespace App\Http\Controllers\Wifi;

use App\Http\Controllers\Controller;
use App\Models\PelangganWifi;
use App\Models\PembayaranWifi;
use App\Models\Unit;
use App\Traits\ComputesWifiStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class WifiPembayaranController extends Controller
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
     * Halaman Utama Tagihan & Kasir Pembayaran
     */
    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        // Periode Filter (Default: Bulan Ini, Tahun Ini, Gelombang 1_15)
        $bulan     = (int) $request->input('bulan', now()->month);
        $tahun     = (int) $request->input('tahun', now()->year);
        $gelombang = $request->input('gelombang', '1_15');
        if (!in_array($gelombang, ['1_15', '16_30'])) {
            $gelombang = '1_15';
        }

        $query = PelangganWifi::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_id_pel', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('paket', 'like', "%{$search}%");
            });
        }

        // Filter status gelombang aktif
        $statusCol = $gelombang === '1_15' ? 'status_1_15' : 'status_16_30';
        if ($statusFilter = $request->input('status')) {
            if ($statusFilter === 'KOSONG') {
                $query->whereNull($statusCol);
            } else {
                $query->where($statusCol, $statusFilter);
            }
        }

        if ($paketFilter = $request->input('paket')) {
            $query->where('paket', $paketFilter);
        }

        // Sort
        $sortField = $request->input('sort', 'no');
        $sortDir   = $request->input('dir', 'asc');
        $allowed   = ['no', 'nama', 'no_id_pel', 'paket', 'total_tarikan', $statusCol];
        if (in_array($sortField, $allowed)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 25;
        }

        // Get customers with latest payment status for selected period & gelombang
        $pelanggan = $query->paginate($perPage)->withQueryString();

        // Attach last payment record for selected period to each customer in collection
        $pelangganIds = $pelanggan->pluck('id');
        $pembayaranRecords = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganIds)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->where('gelombang', $gelombang)
            ->get()
            ->keyBy('pelanggan_wifi_id');

        $pelanggan->getCollection()->transform(function ($item) use ($pembayaranRecords, $gelombang) {
            $item->pembayaran_periode = $pembayaranRecords->get($item->id);
            // If paid this period, use that status; otherwise apply auto-isolir logic
            $item->current_status = $item->pembayaran_periode
                ? $item->pembayaran_periode->status
                : $this->computeCurrentStatus($item, null);
            return $item;
        });

        // ── Stat Summary ─────────────────────────────────────────
        $totalPelanggan = PelangganWifi::count();
        $totalLunas     = PembayaranWifi::where('periode_bulan', $bulan)->where('periode_tahun', $tahun)->where('gelombang', $gelombang)->where('status', 'LUNAS')->count();
        if ($totalLunas === 0) {
            $totalLunas = PelangganWifi::where($statusCol, 'LUNAS')->count();
        }
        $totalTunggakan = PembayaranWifi::where('periode_bulan', $bulan)->where('periode_tahun', $tahun)->where('gelombang', $gelombang)->where('status', 'TUNGGAKAN')->count();
        $totalIsolir    = PembayaranWifi::where('periode_bulan', $bulan)->where('periode_tahun', $tahun)->where('gelombang', $gelombang)->where('status', 'ISOLIR')->count();
        $totalBelum     = max(0, $totalPelanggan - ($totalLunas + $totalTunggakan + $totalIsolir));

        $totalNominalBulanIni = PembayaranWifi::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->where('gelombang', $gelombang)
            ->where('status', 'LUNAS')
            ->sum('jumlah_bayar');

        $kasHariIni = PembayaranWifi::whereDate('tanggal_bayar', now()->toDateString())
            ->where('status', 'LUNAS')
            ->sum('jumlah_bayar');

        $paketOptions = PelangganWifi::select('paket')->whereNotNull('paket')->distinct()->orderBy('paket')->pluck('paket');

        $settingsKeys = ['bank_accounts', 'wa_company_name'];
        $wifiSettings = [];
        foreach ($settingsKeys as $key) {
            $unitSetting = \App\Models\LandingPageSetting::where('key', "wifi_{$key}")->first();
            if ($unitSetting) {
                $wifiSettings[$key] = $key === 'bank_accounts' ? (json_decode($unitSetting->value, true) ?: []) : $unitSetting->value;
            }
        }

        return Inertia::render('Wifi/Pembayaran', [
            'unit'         => $unit,
            'user'         => $user,
            'pelanggan'    => $pelanggan,
            'paketOptions' => $paketOptions,
            'wifiSettings' => $wifiSettings,
            'filters'      => [
                'bulan'     => $bulan,
                'tahun'     => $tahun,
                'gelombang' => $gelombang,
                'search'    => $request->input('search', ''),
                'status'    => $request->input('status', ''),
                'paket'     => $request->input('paket', ''),
                'sort'      => $sortField,
                'dir'       => $sortDir,
                'per_page'  => $perPage,
            ],
            'stats' => [
                'total_pelanggan'          => $totalPelanggan,
                'total_lunas'              => $totalLunas,
                'total_tunggakan'          => $totalTunggakan,
                'total_isolir'             => $totalIsolir,
                'total_belum'              => $totalBelum,
                'total_nominal_terkumpul'  => $totalNominalBulanIni,
                'kas_hari_ini'             => $kasHariIni,
            ],
        ]);
    }

    /**
     * Input Pembayaran Single
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUnit();

        $request->validate([
            'pelanggan_wifi_id' => 'required|exists:pelanggan_wifi,id',
            'periode_bulan'     => 'required|integer|between:1,12',
            'periode_tahun'     => 'required|integer|min:2020',
            'gelombang'         => 'required|in:1_15,16_30',
            'tanggal_bayar'     => 'required|date',
            'jumlah_bayar'      => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:TUNAI,TRANSFER',
            'status'            => 'required|in:AKTIF,LUNAS,ISOLIR',
            'catatan'           => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            $pelanggan = PelangganWifi::findOrFail($request->pelanggan_wifi_id);

            // Generate nomor transaksi unik TRX-WF-202608-0001
            $prefix = sprintf('TRX-WF-%04d%02d-', $request->periode_tahun, $request->periode_bulan);
            $lastCount = PembayaranWifi::where('no_transaksi', 'like', "{$prefix}%")->count();
            $noTransaksi = $prefix . sprintf('%04d', $lastCount + 1);

            // Create or update payment record for this customer + period + gelombang
            $pembayaran = PembayaranWifi::updateOrCreate(
                [
                    'pelanggan_wifi_id' => $pelanggan->id,
                    'periode_bulan'     => $request->periode_bulan,
                    'periode_tahun'     => $request->periode_tahun,
                    'gelombang'         => $request->gelombang,
                ],
                [
                    'no_transaksi'      => $noTransaksi,
                    'tanggal_bayar'     => $request->tanggal_bayar,
                    'jumlah_bayar'      => $request->jumlah_bayar,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status'            => $request->status,
                    'catatan'           => $request->catatan,
                    'kasir_user_id'     => Auth::id(),
                ]
            );

            // Update PelangganWifi main status and gelombang
            if ($request->gelombang === '1_15') {
                $pelanggan->status_1_15 = $request->status;
            } else {
                $pelanggan->status_16_30 = $request->status;
            }
            $pelanggan->gelombang = $request->gelombang;
            $pelanggan->save();
        });

        return redirect()->back()->with('success', 'Pembayaran berhasil disimpan.');
    }

    /**
     * Bayar Masal (Batch Payment)
     */
    public function bayarMasal(Request $request): RedirectResponse
    {
        $this->authorizeUnit();

        $request->validate([
            'pelanggan_ids'     => 'required|array|min:1',
            'pelanggan_ids.*'   => 'exists:pelanggan_wifi,id',
            'periode_bulan'     => 'required|integer|between:1,12',
            'periode_tahun'     => 'required|integer|min:2020',
            'gelombang'         => 'required|in:1_15,16_30',
            'tanggal_bayar'     => 'required|date',
            'metode_pembayaran' => 'required|in:TUNAI,TRANSFER',
            'status'            => 'required|in:AKTIF,LUNAS,ISOLIR',
        ]);

        DB::transaction(function () use ($request) {
            $pelangganList = PelangganWifi::whereIn('id', $request->pelanggan_ids)->get();

            foreach ($pelangganList as $pelanggan) {
                $prefix = sprintf('TRX-WF-%04d%02d-', $request->periode_tahun, $request->periode_bulan);
                $lastCount = PembayaranWifi::where('no_transaksi', 'like', "{$prefix}%")->count();
                $noTransaksi = $prefix . sprintf('%04d', $lastCount + 1);

                $nominal = $pelanggan->total_tarikan ?? 0;

                PembayaranWifi::updateOrCreate(
                    [
                        'pelanggan_wifi_id' => $pelanggan->id,
                        'periode_bulan'     => $request->periode_bulan,
                        'periode_tahun'     => $request->periode_tahun,
                        'gelombang'         => $request->gelombang,
                    ],
                    [
                        'no_transaksi'      => $noTransaksi,
                        'tanggal_bayar'     => $request->tanggal_bayar,
                        'jumlah_bayar'      => $nominal,
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'status'            => $request->status,
                        'catatan'           => 'Pembayaran Masal / Kolektor',
                        'kasir_user_id'     => Auth::id(),
                    ]
                );

                if ($request->gelombang === '1_15') {
                    $pelanggan->status_1_15 = $request->status;
                } else {
                    $pelanggan->status_16_30 = $request->status;
                }
                $pelanggan->gelombang = $request->gelombang;
                $pelanggan->save();
            }
        });

        return redirect()->back()->with('success', count($request->pelanggan_ids) . ' pembayaran pelanggan berhasil diproses.');
    }

    /**
     * Get Payment History JSON for Modal
     */
    public function history(PelangganWifi $pelanggan)
    {
        $this->authorizeUnit();

        $history = PembayaranWifi::where('pelanggan_wifi_id', $pelanggan->id)
            ->with('kasir:id,nama')
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'pelanggan' => $pelanggan,
            'history'   => $history,
        ]);
    }

    /**
     * Halaman Struk Thermal (80mm) Printable
     */
    public function struk(PembayaranWifi $pembayaran)
    {
        $this->authorizeUnit();
        $pembayaran->load(['pelanggan', 'kasir']);

        return view('wifi.struk', [
            'pembayaran' => $pembayaran,
        ]);
    }
}
