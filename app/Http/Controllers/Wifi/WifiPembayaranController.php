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

        // Filter status tagihan dan koneksi pada periode aktif
        $statusCol = $gelombang === '1_15' ? 'status_1_15' : 'status_16_30';

        $prevBulan = $bulan == 1 ? 12 : $bulan - 1;
        $prevTahun = $bulan == 1 ? $tahun - 1 : $tahun;
        $currentPeriodYM = sprintf('%04d-%02d', $tahun, $bulan);

        $statusBayarFilter   = $request->input('status_bayar', '');
        $statusKoneksiFilter = $request->input('status_koneksi', '');

        // Dukungan kompatibilitas jika masih mengirim parameter 'status'
        if ($legacyStatus = $request->input('status')) {
            if (in_array($legacyStatus, ['LUNAS', 'SUDAH_BAYAR'])) {
                $statusBayarFilter = 'LUNAS';
            } elseif (in_array($legacyStatus, ['BELUM_BAYAR', 'BELUM'])) {
                $statusBayarFilter = 'BELUM_BAYAR';
            } elseif ($legacyStatus === 'AKTIF') {
                $statusKoneksiFilter = 'AKTIF';
            } elseif ($legacyStatus === 'ISOLIR') {
                $statusKoneksiFilter = 'ISOLIR';
            }
        }

        // 1. Filter Status Bayar (LUNAS vs BELUM_BAYAR)
        if ($statusBayarFilter === 'LUNAS') {
            $query->whereHas('pembayaran', function ($q) use ($bulan, $tahun, $gelombang) {
                $q->where('periode_bulan', $bulan)
                  ->where('periode_tahun', $tahun)
                  ->where('gelombang', $gelombang)
                  ->whereIn('status', ['LUNAS', 'AKTIF']);
            });
        } elseif ($statusBayarFilter === 'BELUM_BAYAR') {
            $query->whereDoesntHave('pembayaran', function ($q) use ($bulan, $tahun, $gelombang) {
                $q->where('periode_bulan', $bulan)
                  ->where('periode_tahun', $tahun)
                  ->where('gelombang', $gelombang)
                  ->whereIn('status', ['LUNAS', 'AKTIF']);
            });
        }

        // 2. Filter Status Koneksi (AKTIF vs ISOLIR - Memperhitungkan Bulan Kemarin)
        if ($statusKoneksiFilter === 'ISOLIR') {
            $query->where(function ($q) use ($bulan, $tahun, $prevBulan, $prevTahun, $gelombang, $statusCol, $currentPeriodYM) {
                // Manual isolir
                $q->where($statusCol, 'ISOLIR');

                // Jika lewat tanggal 10: belum bayar bulan ini langsung isolir
                if (now()->day >= 11) {
                    $q->orWhereDoesntHave('pembayaran', function ($sub) use ($bulan, $tahun, $gelombang) {
                        $sub->where('periode_bulan', $bulan)
                            ->where('periode_tahun', $tahun)
                            ->where('gelombang', $gelombang)
                            ->whereIn('status', ['LUNAS', 'AKTIF']);
                    });
                } else {
                    // Masih tanggal 1-10: isolir jika belum bayar bulan ini DAN belum bayar bulan lalu (bukan pelanggan baru)
                    $q->orWhere(function ($subQ) use ($bulan, $tahun, $prevBulan, $prevTahun, $gelombang, $currentPeriodYM) {
                        $subQ->whereDoesntHave('pembayaran', function ($sub) use ($bulan, $tahun, $gelombang) {
                            $sub->where('periode_bulan', $bulan)
                                ->where('periode_tahun', $tahun)
                                ->where('gelombang', $gelombang)
                                ->whereIn('status', ['LUNAS', 'AKTIF']);
                        })->whereDoesntHave('pembayaran', function ($sub) use ($prevBulan, $prevTahun, $gelombang) {
                            $sub->where('periode_bulan', $prevBulan)
                                ->where('periode_tahun', $prevTahun)
                                ->where('gelombang', $gelombang)
                                ->whereIn('status', ['LUNAS', 'AKTIF']);
                        })->where(function ($dateQ) use ($currentPeriodYM) {
                            $dateQ->whereNull('tanggal_daftar')
                                  ->orWhere('tanggal_daftar', '<', "{$currentPeriodYM}-01");
                        });
                    });
                }
            });
        } elseif ($statusKoneksiFilter === 'AKTIF') {
            $query->where(function ($q) use ($statusCol) {
                $q->whereNull($statusCol)->orWhere($statusCol, '!=', 'ISOLIR');
            })->where(function ($q) use ($bulan, $tahun, $prevBulan, $prevTahun, $gelombang, $currentPeriodYM) {
                // Sudah lunas bulan ini pasti aktif
                $q->whereHas('pembayaran', function ($sub) use ($bulan, $tahun, $gelombang) {
                    $sub->where('periode_bulan', $bulan)
                        ->where('periode_tahun', $tahun)
                        ->where('gelombang', $gelombang)
                        ->whereIn('status', ['LUNAS', 'AKTIF']);
                });

                // Jika masih tanggal 1-10, aktif jika lunas bulan lalu atau baru daftar bulan ini
                if (now()->day <= 10) {
                    $q->orWhere(function ($subQ) use ($prevBulan, $prevTahun, $gelombang, $currentPeriodYM) {
                        $subQ->whereHas('pembayaran', function ($sub) use ($prevBulan, $prevTahun, $gelombang) {
                            $sub->where('periode_bulan', $prevBulan)
                                ->where('periode_tahun', $prevTahun)
                                ->where('gelombang', $gelombang)
                                ->whereIn('status', ['LUNAS', 'AKTIF']);
                        })->orWhere('tanggal_daftar', '>=', "{$currentPeriodYM}-01");
                    });
                }
            });
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
        $pelanggan = $query->with('provider')->paginate($perPage)->withQueryString();

        // Attach last payment record for selected period to each customer in collection
        $pelangganIds = $pelanggan->pluck('id');
        $pembayaranRecords = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganIds)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->where('gelombang', $gelombang)
            ->with('pelanggan')
            ->get()
            ->keyBy('pelanggan_wifi_id');

        // Ambil pembayaran bulan sebelumnya untuk pelanggan di halaman ini
        $prevPembayaranRecords = PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganIds)
            ->where('periode_bulan', $prevBulan)
            ->where('periode_tahun', $prevTahun)
            ->where('gelombang', $gelombang)
            ->whereIn('status', ['LUNAS', 'AKTIF'])
            ->get()
            ->keyBy('pelanggan_wifi_id');

        $pelanggan->getCollection()->transform(function ($item) use ($pembayaranRecords, $prevPembayaranRecords, $statusCol, $currentPeriodYM) {
            $item->pembayaran_periode = $pembayaranRecords->get($item->id);

            // 1. Status Pembayaran Periode Ini
            $item->status_bayar = ($item->pembayaran_periode && in_array(strtoupper($item->pembayaran_periode->status ?? ''), ['LUNAS', 'AKTIF']))
                ? 'LUNAS'
                : 'BELUM_BAYAR';

            // 2. Cek apakah baru mendaftar di bulan berjalan ini
            $isNewThisMonth = false;
            if ($item->tanggal_daftar) {
                $daftarYM = $item->tanggal_daftar instanceof \Carbon\Carbon
                    ? $item->tanggal_daftar->format('Y-m')
                    : substr((string)$item->tanggal_daftar, 0, 7);
                if ($daftarYM >= $currentPeriodYM) {
                    $isNewThisMonth = true;
                }
            }

            $lunasBulanLalu = $prevPembayaranRecords->has($item->id);
            $item->lunas_bulan_lalu = $lunasBulanLalu;
            $item->is_new_this_month = $isNewThisMonth;
            $item->prev_bulan = $prevBulan;
            $item->prev_tahun = $prevTahun;

            // 3. Status Koneksi WiFi (Aktif vs Isolir)
            if ($item->status_bayar === 'LUNAS') {
                $item->status_koneksi = 'AKTIF';
                $item->koneksi_note   = 'Lancar';
            } elseif ($item->$statusCol === 'ISOLIR') {
                $item->status_koneksi = 'ISOLIR';
                $item->koneksi_note   = 'Isolir Manual';
            } elseif (!$isNewThisMonth && !$lunasBulanLalu) {
                // Menunggak bulan lalu -> Tetap ISOLIR meski masih tgl 1-10!
                $item->status_koneksi = 'ISOLIR';
                $item->koneksi_note   = 'Menunggak Bulan Lalu';
            } elseif (now()->day >= 11) {
                // Bulan lalu lunas, tapi bulan ini belum bayar & lewat tanggal 10
                $item->status_koneksi = 'ISOLIR';
                $item->koneksi_note   = 'Lewat Batas Tgl 10';
            } else {
                // Bulan lalu lunas (atau pelanggan baru) & masih dalam masa bayar (tgl 1-10)
                $item->status_koneksi = 'AKTIF';
                $item->koneksi_note   = 'Masa Bayar (s/d Tgl 10)';
            }

            // Fallback status
            $item->current_status = $item->status_bayar;

            return $item;
        });

        // ── Stat Summary ─────────────────────────────────────────
        $allPelanggan = PelangganWifi::all();
        $totalPelanggan = $allPelanggan->count();

        // Ambil semua ID pelanggan yang sudah bayar pada periode aktif ini
        $paidPelangganIds = PembayaranWifi::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->where('gelombang', $gelombang)
            ->whereIn('status', ['LUNAS', 'AKTIF'])
            ->pluck('pelanggan_wifi_id')
            ->toArray();

        // Ambil semua ID pelanggan yang sudah bayar pada bulan lalu
        $prevPaidPelangganIds = PembayaranWifi::where('periode_bulan', $prevBulan)
            ->where('periode_tahun', $prevTahun)
            ->where('gelombang', $gelombang)
            ->whereIn('status', ['LUNAS', 'AKTIF'])
            ->pluck('pelanggan_wifi_id')
            ->toArray();

        $currPaidSet = array_flip($paidPelangganIds);
        $prevPaidSet = array_flip($prevPaidPelangganIds);

        $totalLunas = count($paidPelangganIds);
        $totalBelumBayar = max(0, $totalPelanggan - $totalLunas);

        $totalIsolir = 0;
        foreach ($allPelanggan as $p) {
            $isPaidCurr = isset($currPaidSet[$p->id]);
            if ($isPaidCurr) continue; // lunas bulan ini -> aktif

            if ($p->$statusCol === 'ISOLIR') {
                $totalIsolir++;
                continue;
            }

            $daftarYM = $p->tanggal_daftar ? (
                $p->tanggal_daftar instanceof \Carbon\Carbon
                    ? $p->tanggal_daftar->format('Y-m')
                    : substr((string)$p->tanggal_daftar, 0, 7)
            ) : null;
            $isNew = $daftarYM && $daftarYM >= $currentPeriodYM;
            $isPaidPrev = isset($prevPaidSet[$p->id]);

            if (!$isNew && !$isPaidPrev) {
                // Menunggak bulan lalu
                $totalIsolir++;
            } elseif (now()->day >= 11) {
                // Lewat tgl 10
                $totalIsolir++;
            }
        }
        $totalKoneksiAktif = max(0, $totalPelanggan - $totalIsolir);

        $totalNominalBulanIni = PembayaranWifi::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->whereIn('status', ['LUNAS', 'AKTIF'])
            ->sum('jumlah_bayar');

        $kasHariIni = PembayaranWifi::whereDate('tanggal_bayar', now()->toDateString())
            ->whereIn('status', ['LUNAS', 'AKTIF'])
            ->sum('jumlah_bayar');

        $paketOptions = PelangganWifi::select('paket')->whereNotNull('paket')->distinct()->orderBy('paket')->pluck('paket');

        $settingsKeys = ['bank_accounts', 'wa_company_name'];
        $wifiSettings = [];
        foreach ($settingsKeys as $key) {
            $unitSetting = \App\Models\LandingPageSetting::where('key', "wifi_{$key}")->first();
            if ($unitSetting && $unitSetting->value) {
                $wifiSettings[$key] = $unitSetting->value;
            }
        }

        return Inertia::render('Wifi/Pembayaran', [
            'unit'         => $unit,
            'user'         => $user,
            'pelanggan'    => $pelanggan,
            'paketOptions' => $paketOptions,
            'wifiSettings' => $wifiSettings,
            'filters'      => [
                'bulan'          => $bulan,
                'tahun'          => $tahun,
                'gelombang'      => $gelombang,
                'search'         => $request->input('search', ''),
                'status_bayar'   => $statusBayarFilter,
                'status_koneksi' => $statusKoneksiFilter,
                'status'         => $request->input('status', ''),
                'paket'          => $request->input('paket', ''),
                'sort'           => $sortField,
                'dir'            => $sortDir,
                'per_page'       => $perPage,
            ],
            'stats' => [
                'total_pelanggan'          => $totalPelanggan,
                'total_lunas'              => $totalLunas,
                'total_belum_bayar'        => $totalBelumBayar,
                'total_aktif'              => $totalLunas,
                'total_koneksi_aktif'      => $totalKoneksiAktif,
                'total_isolir'             => $totalIsolir,
                'total_nominal_terkumpul'  => $totalNominalBulanIni,
                'kas_hari_ini'             => $kasHariIni,
            ],
        ]);
    }

    /**
     * Input Pembayaran Single / Multi-Bulan
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUnit();

        $request->validate([
            'pelanggan_wifi_id'    => 'required|exists:pelanggan_wifi,id',
            'periode_bulan'        => 'nullable|integer|between:1,12',
            'periode_tahun'        => 'nullable|integer|min:2020',
            'periode_list'         => 'nullable|array|min:1',
            'periode_list.*.bulan' => 'required_with:periode_list|integer|between:1,12',
            'periode_list.*.tahun' => 'required_with:periode_list|integer|min:2020',
            'gelombang'            => 'required|in:1_15,16_30',
            'tanggal_bayar'        => 'required|date',
            'jumlah_bayar'         => 'required|numeric|min:0',
            'metode_pembayaran'    => 'required|in:TUNAI,TRANSFER',
            'status'               => 'required|in:AKTIF,LUNAS,ISOLIR',
            'catatan'              => 'nullable|string|max:500',
        ]);

        $namaBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        DB::transaction(function () use ($request, $namaBulanMap) {
            $pelanggan = PelangganWifi::findOrFail($request->pelanggan_wifi_id);

            // Tentukan daftar periode yang akan dibayar (bisa 1 bulan atau banyak bulan sekaligus)
            $targetPeriods = [];
            if ($request->has('periode_list') && is_array($request->periode_list) && count($request->periode_list) > 0) {
                foreach ($request->periode_list as $p) {
                    $targetPeriods[] = [
                        'bulan' => (int) $p['bulan'],
                        'tahun' => (int) $p['tahun'],
                    ];
                }
            } else {
                $targetPeriods[] = [
                    'bulan' => (int) ($request->periode_bulan ?? now()->month),
                    'tahun' => (int) ($request->periode_tahun ?? now()->year),
                ];
            }

            // Map status for MySQL enum ('LUNAS', 'TUNGGAKAN', 'ISOLIR')
            $statusEnum = in_array($request->status, ['LUNAS', 'AKTIF']) ? 'LUNAS' : $request->status;

            $countPeriods = count($targetPeriods);
            $totalBayar   = (float) $request->jumlah_bayar;
            $nominalPerBulan = $countPeriods > 0 ? round($totalBayar / $countPeriods) : $totalBayar;

            $namaBulanTeks = collect($targetPeriods)
                ->map(fn($p) => ($namaBulanMap[$p['bulan']] ?? $p['bulan']) . ' ' . $p['tahun'])
                ->join(', ');

            foreach ($targetPeriods as $item) {
                $b = $item['bulan'];
                $y = $item['tahun'];

                $existing = PembayaranWifi::where([
                    'pelanggan_wifi_id' => $pelanggan->id,
                    'periode_bulan'     => $b,
                    'periode_tahun'     => $y,
                    'gelombang'         => $request->gelombang,
                ])->first();

                if ($existing && $existing->no_transaksi) {
                    $noTransaksi = $existing->no_transaksi;
                } else {
                    $prefix = sprintf('WF-%04d%02d-', $y, $b);
                    $lastCount = PembayaranWifi::where(function ($q) use ($prefix) {
                        $q->where('no_transaksi', 'like', "{$prefix}%")
                          ->orWhere('no_transaksi', 'like', "TRX-{$prefix}%");
                    })->count();
                    $noTransaksi = $prefix . sprintf('%04d', $lastCount + 1);
                }

                $catatanItem = $request->catatan;
                if ($countPeriods > 1) {
                    $multiNote = "Bayar Sekaligus ({$namaBulanTeks})";
                    $catatanItem = $catatanItem ? "{$multiNote} - {$catatanItem}" : $multiNote;
                }

                // Create or update payment record for this customer + period + gelombang
                PembayaranWifi::updateOrCreate(
                    [
                        'pelanggan_wifi_id' => $pelanggan->id,
                        'periode_bulan'     => $b,
                        'periode_tahun'     => $y,
                        'gelombang'         => $request->gelombang,
                    ],
                    [
                        'no_transaksi'      => $noTransaksi,
                        'tanggal_bayar'     => $request->tanggal_bayar,
                        'jumlah_bayar'      => $nominalPerBulan,
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'status'            => $statusEnum,
                        'catatan'           => $catatanItem,
                        'kasir_user_id'     => Auth::id(),
                    ]
                );
            }

            // Update PelangganWifi main status and gelombang
            if ($request->gelombang === '1_15') {
                $pelanggan->status_1_15 = $statusEnum;
            } else {
                $pelanggan->status_16_30 = $statusEnum;
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
            $statusEnum = in_array($request->status, ['LUNAS', 'AKTIF']) ? 'LUNAS' : $request->status;

            foreach ($pelangganList as $pelanggan) {
                $prefix = sprintf('WF-%04d%02d-', $request->periode_tahun, $request->periode_bulan);
                $lastCount = PembayaranWifi::where(function ($q) use ($prefix) {
                    $q->where('no_transaksi', 'like', "{$prefix}%")
                      ->orWhere('no_transaksi', 'like', "TRX-{$prefix}%");
                })->count();
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
                        'status'            => $statusEnum,
                        'catatan'           => 'Pembayaran Masal / Kolektor',
                        'kasir_user_id'     => Auth::id(),
                    ]
                );

                if ($request->gelombang === '1_15') {
                    $pelanggan->status_1_15 = $statusEnum;
                } else {
                    $pelanggan->status_16_30 = $statusEnum;
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
     * Hapus / Batalkan Transaksi Pembayaran WiFi
     */
    public function destroy(Request $request, PembayaranWifi $pembayaran)
    {
        $this->authorizeUnit();

        $infoTrx = $pembayaran->no_transaksi;
        $namaPelanggan = $pembayaran->pelanggan?->nama ?? 'Pelanggan';

        DB::transaction(function () use ($pembayaran) {
            $pelanggan = $pembayaran->pelanggan;
            $periodeBulan = $pembayaran->periode_bulan;
            $periodeTahun = $pembayaran->periode_tahun;
            $gelombang = $pembayaran->gelombang;

            // Hapus record pembayaran
            $pembayaran->delete();

            // Sinkronisasi status pelanggan berdasarkan riwayat pembayaran terbaru yang masih tersisa
            if ($pelanggan) {
                $latestPay = PembayaranWifi::where('pelanggan_wifi_id', $pelanggan->id)
                    ->where('gelombang', $gelombang)
                    ->orderByDesc('periode_tahun')
                    ->orderByDesc('periode_bulan')
                    ->orderByDesc('id')
                    ->first();

                if ($gelombang === '1_15') {
                    $pelanggan->status_1_15 = $latestPay ? $latestPay->status : null;
                } else {
                    $pelanggan->status_16_30 = $latestPay ? $latestPay->status : null;
                }
                $pelanggan->save();
            }
        });

        $message = "Transaksi {$infoTrx} ({$namaPelanggan}) berhasil dibatalkan/dihapus.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Halaman Struk Thermal (80mm) Printable
     */
    public function struk(PembayaranWifi $pembayaran)
    {
        $pembayaran->load(['pelanggan', 'kasir']);

        return view('wifi.struk', [
            'pembayaran' => $pembayaran,
        ]);
    }
}
