<?php

namespace App\Http\Controllers\Wifi;

use App\Exports\WifiPendapatanKotorExport;
use App\Http\Controllers\Controller;
use App\Models\PelangganWifi;
use App\Models\PembayaranWifi;
use App\Models\ProviderWifi;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class WifiPendapatanController extends Controller
{
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'wifi')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized access to WiFi unit.');
        }
        return $unit;
    }

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();
        $data = $this->getLaporanData($request, true);

        return Inertia::render('Wifi/Pendapatan', array_merge($data, [
            'unit' => $unit,
            'user' => $user,
        ]));
    }

    public function pdf(Request $request)
    {
        $this->authorizeUnit();
        $data = $this->getLaporanData($request, false);
        $data['user'] = Auth::user();

        return view('exports.wifi.laporan.pendapatan-kotor', $data);
    }

    public function excel(Request $request)
    {
        $this->authorizeUnit();
        $data = $this->getLaporanData($request, false);

        return Excel::download(
            new WifiPendapatanKotorExport($data),
            'laporan-pendapatan-kotor-wifi-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    private function getLaporanData(Request $request, bool $limit = false): array
    {
        $year    = (int) $request->input('tahun', now()->year);
        $month   = $request->input('bulan');
        $tanggal = $request->input('tanggal');

        // Query pembayaran wifi
        $query = PembayaranWifi::with(['pelanggan.provider', 'kasir']);

        if ($tanggal) {
            $query->whereDate('tanggal_bayar', $tanggal);
        } else {
            $query->where(function ($q) use ($year, $month) {
                $q->where(function ($sub) use ($year, $month) {
                    $sub->whereYear('tanggal_bayar', $year);
                    if ($month) {
                        $sub->whereMonth('tanggal_bayar', $month);
                    }
                })->orWhere(function ($sub) use ($year, $month) {
                    $sub->whereNull('tanggal_bayar')
                        ->where('periode_tahun', $year);
                    if ($month) {
                        $sub->where('periode_bulan', $month);
                    }
                });
            });
        }

        // Ambil transaksi berstatus LUNAS/AKTIF atau yang sudah memiliki nominal bayar
        $allPayments = (clone $query)
            ->where('jumlah_bayar', '>', 0)
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $pendapatanPersentase = 0;
        $pendapatanAdminFlat  = 0;
        $totalTarikanBruto    = 0;
        $totalHakProvider     = 0;
        $totalDasarProvider   = 0;
        $totalTunai           = 0;
        $totalTransfer        = 0;

        $detailPersentase = collect();
        $detailAdminFlat  = collect();
        $detailSemua      = collect();

        foreach ($allPayments as $p) {
            $pelanggan = $p->pelanggan;
            $provider  = $pelanggan ? $pelanggan->provider : null;
            $tarikan   = (float) $p->jumlah_bayar;
            $totalTarikanBruto += $tarikan;

            $metode = strtoupper(trim($p->metode_pembayaran ?? 'TUNAI'));
            if (in_array($metode, ['TRANSFER', 'BANK', 'QRIS'])) {
                $totalTransfer += $tarikan;
            } else {
                $totalTunai += $tarikan;
            }

            $isFlat = $provider && $provider->tipe_bagi_hasil === 'FLAT_ADMIN';

            if ($isFlat) {
                // Skema Flat Admin
                $nilaiAdmin = (float) ($provider->nilai_bagi_hasil > 0 ? $provider->nilai_bagi_hasil : ($pelanggan->hasil_bumdes ?: 5000));
                if ($nilaiAdmin > $tarikan) {
                    $nilaiAdmin = $tarikan;
                }
                $hakBumdes     = $nilaiAdmin;
                $hakProvider   = max(0, $tarikan - $hakBumdes);
                $dasarProvider = $tarikan;

                $pendapatanAdminFlat += $hakBumdes;
                $totalHakProvider    += $hakProvider;
                $totalDasarProvider  += $dasarProvider;

                $rowItem = [
                    'id'               => $p->id,
                    'no_transaksi'     => preg_replace('/^TRX-?/i', '', $p->no_transaksi),
                    'periode_bulan'    => $p->periode_bulan,
                    'periode_tahun'    => $p->periode_tahun,
                    'tanggal'          => $p->tanggal_bayar ? $p->tanggal_bayar->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : '-'),
                    'no_id_pel'        => $pelanggan->no_id_pel ?? '-',
                    'pelanggan'        => $pelanggan->nama ?? '-',
                    'alamat'           => ($pelanggan->alamat ?? '-') . ($pelanggan->rt ? ' RT ' . $pelanggan->rt . '/' . $pelanggan->rw : ''),
                    'provider'         => $provider->nama_provider ?? 'Umum / Flat',
                    'paket'            => $pelanggan->paket ?? '-',
                    'total_tarikan'    => $tarikan,
                    'dasar_provider'   => $dasarProvider,
                    'skema'            => 'FLAT_ADMIN',
                    'nilai_skema'      => 'Flat Rp ' . number_format($nilaiAdmin, 0, ',', '.'),
                    'hak_bumdes'       => $hakBumdes,
                    'hak_provider'     => $hakProvider,
                    'status'           => $p->status ?? 'LUNAS',
                    'kasir'            => $p->kasir ? $p->kasir->nama : 'Admin',
                    'metode'           => $p->metode_pembayaran ?? 'TUNAI',
                ];

                $detailAdminFlat->push($rowItem);
                $detailSemua->push($rowItem);
            } else {
                // Skema Persentase (9% dari Dasar Tarikan Non PPN)
                $pct = (float) ($pelanggan->bagi_hasil_bumdes ?? ($provider->nilai_bagi_hasil ?? 9.00));
                if ($pct <= 0) $pct = 9.00;

                $dasarProvider = (float) ($pelanggan->total_provider > 0 ? $pelanggan->total_provider : $tarikan);
                $hakBumdes     = round($dasarProvider * ($pct / 100));
                $hakProvider   = max(0, $dasarProvider - $hakBumdes);

                $pendapatanPersentase += $hakBumdes;
                $totalHakProvider     += $hakProvider;
                $totalDasarProvider   += $dasarProvider;

                $rowItem = [
                    'id'               => $p->id,
                    'no_transaksi'     => preg_replace('/^TRX-?/i', '', $p->no_transaksi),
                    'periode_bulan'    => $p->periode_bulan,
                    'periode_tahun'    => $p->periode_tahun,
                    'tanggal'          => $p->tanggal_bayar ? $p->tanggal_bayar->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : '-'),
                    'no_id_pel'        => $pelanggan->no_id_pel ?? '-',
                    'pelanggan'        => $pelanggan->nama ?? '-',
                    'alamat'           => ($pelanggan->alamat ?? '-') . ($pelanggan->rt ? ' RT ' . $pelanggan->rt . '/' . $pelanggan->rw : ''),
                    'provider'         => $provider->nama_provider ?? 'Umum / Bagi Hasil',
                    'paket'            => $pelanggan->paket ?? '-',
                    'total_tarikan'    => $tarikan,
                    'dasar_provider'   => $dasarProvider,
                    'skema'            => 'PERSENTASE',
                    'nilai_skema'      => $pct . '%',
                    'hak_bumdes'       => $hakBumdes,
                    'hak_provider'     => $hakProvider,
                    'status'           => $p->status ?? 'LUNAS',
                    'kasir'            => $p->kasir ? $p->kasir->nama : 'Admin',
                    'metode'           => $p->metode_pembayaran ?? 'TUNAI',
                ];

                $detailPersentase->push($rowItem);
                $detailSemua->push($rowItem);
            }
        }

        $pendapatanKotor = $pendapatanPersentase + $pendapatanAdminFlat;

        // Biaya pengurangan
        $biayaGaji     = (float) $request->input('biaya_gaji', 560000);
        $biayaOps      = (float) $request->input('biaya_ops', 240000);
        $biayaAsuransi = (float) $request->input('biaya_asuransi', 40000); // Biaya Pemeliharaan / Server
        $penarikanLaba = (float) $request->input('penarikan_laba', 0);

        $totalPengurangan = $biayaGaji + $biayaOps + $biayaAsuransi + $penarikanLaba;
        $labaBersih       = $pendapatanKotor - $totalPengurangan;

        $calcPersen = fn($val) => $pendapatanKotor > 0 ? round(($val / $pendapatanKotor) * 100, 1) : 0;

        $distribusi = [
            [
                'nama'    => 'Biaya Gaji Pengelola & Teknisi',
                'nominal' => $biayaGaji,
                'persen'  => $calcPersen($biayaGaji),
            ],
            [
                'nama'    => 'Biaya Operasional WiFi',
                'nominal' => $biayaOps,
                'persen'  => $calcPersen($biayaOps),
            ],
            [
                'nama'    => 'Biaya Pemeliharaan & Asuransi Perangkat',
                'nominal' => $biayaAsuransi,
                'persen'  => $calcPersen($biayaAsuransi),
            ],
            [
                'nama'    => 'Penarikan Laba / Lainnya',
                'nominal' => $penarikanLaba,
                'persen'  => $calcPersen($penarikanLaba),
            ],
            [
                'nama'    => 'Total Pengambilan',
                'nominal' => $totalPengurangan,
                'persen'  => $calcPersen($totalPengurangan),
            ],
            [
                'nama'    => 'Laba Bersih BUMDes',
                'nominal' => $labaBersih,
                'persen'  => $calcPersen($labaBersih),
            ],
        ];

        if ($limit) {
            $detailPersentase = $detailPersentase->take(30);
            $detailAdminFlat  = $detailAdminFlat->take(30);
            $detailSemua      = $detailSemua->take(30);
        }

        $tahunOptions = collect(range(now()->year, now()->year - 5))->values();

        $bulanOptions = collect([
            ['value' => '',  'label' => 'Semua Bulan'],
            ['value' => 1,   'label' => 'Januari'],
            ['value' => 2,   'label' => 'Februari'],
            ['value' => 3,   'label' => 'Maret'],
            ['value' => 4,   'label' => 'April'],
            ['value' => 5,   'label' => 'Mei'],
            ['value' => 6,   'label' => 'Juni'],
            ['value' => 7,   'label' => 'Juli'],
            ['value' => 8,   'label' => 'Agustus'],
            ['value' => 9,   'label' => 'September'],
            ['value' => 10,  'label' => 'Oktober'],
            ['value' => 11,  'label' => 'November'],
            ['value' => 12,  'label' => 'Desember'],
        ]);

        $bulanNama = $month ? $bulanOptions->firstWhere('value', (int)$month)['label'] : 'Semua Bulan';

        return [
            'tahun'                => (int) $year,
            'bulan'                => $month ? (int) $month : null,
            'tanggal'              => $tanggal,
            'tahunOptions'         => $tahunOptions,
            'bulanOptions'         => $bulanOptions,
            'pendapatanPersentase' => (float) $pendapatanPersentase,
            'pendapatanAdminFlat'  => (float) $pendapatanAdminFlat,
            'totalTarikanBruto'    => (float) $totalTarikanBruto,
            'totalDasarProvider'   => (float) $totalDasarProvider,
            'totalTunai'           => (float) $totalTunai,
            'totalTransfer'        => (float) $totalTransfer,
            'totalHakProvider'     => (float) $totalHakProvider,
            'pendapatanKotor'      => (float) $pendapatanKotor,
            'distribusi'           => $distribusi,
            'biayaGaji'            => $biayaGaji,
            'biayaOps'             => $biayaOps,
            'biayaAsuransi'        => $biayaAsuransi,
            'penarikanLaba'        => $penarikanLaba,
            'detailPersentase'     => $detailPersentase->values()->all(),
            'detailAdminFlat'      => $detailAdminFlat->values()->all(),
            'detailSemua'          => $detailSemua->values()->all(),
            'bulanNama'            => $bulanNama,
        ];
    }
}
