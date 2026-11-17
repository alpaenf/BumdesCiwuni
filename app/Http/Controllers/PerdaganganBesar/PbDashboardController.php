<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbBiayaOperasional;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbNasabahLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiPbb;
use App\Models\PerdaganganBesar\PbTransaksiSamsat;
use App\Models\PerdaganganBesar\PbTransaksiUmum;
use App\Models\PerdaganganBesar\PbWajibPajakSamsat;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PbDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $unit = Unit::where('slug', 'perdagangan-besar')->firstOrFail();
        $user = Auth::user();

        // Periode Filter: today, this_week, this_month, custom
        $period = $request->input('period', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $now = Carbon::now();
        if ($period === 'today') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
        } elseif ($period === 'this_week') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
        } elseif ($period === 'this_month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        } elseif ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
        }

        // 1. Transaksi Laku Pandai
        $lpQuery = PbTransaksiLakuPandai::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);
        $lpCount = (clone $lpQuery)->count();
        $lpJasaCash = (clone $lpQuery)->sum('pendapatan_jasa_cash');
        $lpJasaRek = (clone $lpQuery)->sum('pendapatan_jasa_rekening');
        $lpTotalJasa = $lpJasaCash + $lpJasaRek;
        $lpVolMasuk = (clone $lpQuery)->sum('cash_masuk') + (clone $lpQuery)->sum('rekening_masuk');
        $lpVolKeluar = (clone $lpQuery)->sum('cash_keluar') + (clone $lpQuery)->sum('rekening_keluar');

        // Saldo Terakhir Laku Pandai
        $latestLp = PbTransaksiLakuPandai::orderByDesc('tanggal')->orderByDesc('id')->first();
        $saldoCashLp = $latestLp ? (float)$latestLp->saldo_cash : 0;
        $saldoRekeningLp = $latestLp ? (float)$latestLp->saldo_rekening : 0;
        $totalSaldoLp = $latestLp ? (float)$latestLp->total_saldo : 0;

        // 2. Transaksi Samsat Budiman
        $samsatQuery = PbTransaksiSamsat::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('status_transaksi', 'SUCCES');
        $samsatCount = (clone $samsatQuery)->count();
        $samsatJasa = (clone $samsatQuery)->sum('biaya_jasa_bumdes');
        $samsatTotal = (clone $samsatQuery)->sum('total_pembayaran');

        // 3. Transaksi PBB
        $pbbQuery = PbTransaksiPbb::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'SUCCES');
        $pbbCount = (clone $pbbQuery)->count();
        $pbbJasa = (clone $pbbQuery)->sum('biaya_admin');
        $pbbTotal = (clone $pbbQuery)->sum('total_bayar');

        // 4. Transaksi Umum
        $umumQuery = PbTransaksiUmum::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'SUCCES');
        $umumCount = (clone $umumQuery)->count();
        $umumJasa = (clone $umumQuery)->sum('biaya_jasa_agen');
        $umumTotal = (clone $umumQuery)->sum('total_bayar');

        // 5. Kas & Biaya Operasional
        $kasMasuk = PbKas::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis', 'masuk')->sum('nominal');
        $kasKeluar = PbKas::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis', 'keluar')->sum('nominal');
        $biayaOperasional = PbBiayaOperasional::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->sum('nominal');

        // Beban Biaya Admin Bank (Dari Biaya Operasional / Kas Keluar Admin Bank)
        $bebanAdminBankBiaya = PbBiayaOperasional::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where(function ($q) {
                $q->where('kategori_biaya', 'like', '%Bank%')
                  ->orWhere('uraian', 'like', '%admin bank%')
                  ->orWhere('uraian', 'like', '%biaya bank%');
            })
            ->sum('nominal');

        $bebanAdminBankKas = PbKas::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('jenis', 'keluar')
            ->where(function ($q) {
                $q->where('kategori_layanan', 'like', '%Bank%')
                  ->orWhere('keterangan', 'like', '%admin bank%')
                  ->orWhere('keterangan', 'like', '%biaya bank%');
            })
            ->sum('nominal');

        $totalBebanAdminBank = max((float)$bebanAdminBankBiaya, (float)$bebanAdminBankKas);

        // Total Agregat
        $totalTransaksi = $lpCount + $samsatCount + $pbbCount + $umumCount;
        $totalPendapatanJasa = $lpTotalJasa + $samsatJasa + $pbbJasa + $umumJasa;
        $labaBersih = $totalPendapatanJasa - $biayaOperasional;

        // Transaksi Hari Ini
        $todayStr = Carbon::today()->toDateString();
        $todayCount = PbTransaksiLakuPandai::where('tanggal', $todayStr)->count()
            + PbTransaksiSamsat::where('tanggal', $todayStr)->where('status_transaksi', 'SUCCES')->count()
            + PbTransaksiPbb::where('tanggal', $todayStr)->where('status', 'SUCCES')->count()
            + PbTransaksiUmum::where('tanggal', $todayStr)->where('status', 'SUCCES')->count();

        // Recent Activity (Union 10 transaksi terakhir dari berbagai subunit)
        $recentActivities = collect();

        $recentLp = PbTransaksiLakuPandai::orderByDesc('tanggal')->orderByDesc('id')->limit(5)->get();
        foreach ($recentLp as $lp) {
            $recentActivities->push([
                'id' => 'LP-' . $lp->id,
                'layanan' => 'Laku Pandai',
                'kode' => $lp->nomor_transaksi,
                'nama' => $lp->nama_nasabah,
                'detail' => $lp->jenis_transaksi,
                'nominal' => (float)$lp->harga_konsumen ?: ((float)$lp->cash_masuk + (float)$lp->rekening_masuk),
                'jasa' => (float)$lp->pendapatan_jasa_cash + (float)$lp->pendapatan_jasa_rekening,
                'tanggal' => $lp->tanggal->format('d/m/Y'),
                'created_at' => $lp->created_at,
                'status' => $lp->status_pembayaran,
                'badge_color' => 'blue',
            ]);
        }

        $recentSamsat = PbTransaksiSamsat::orderByDesc('tanggal')->orderByDesc('id')->limit(5)->get();
        foreach ($recentSamsat as $sm) {
            $recentActivities->push([
                'id' => 'SM-' . $sm->id,
                'layanan' => 'Samsat Budiman',
                'kode' => $sm->nomor_transaksi,
                'nama' => $sm->nama_wajib_pajak,
                'detail' => $sm->nomor_polisi . ' (' . $sm->jenis_kendaraan . ')',
                'nominal' => (float)$sm->total_pembayaran,
                'jasa' => (float)$sm->biaya_jasa_bumdes,
                'tanggal' => $sm->tanggal->format('d/m/Y'),
                'created_at' => $sm->created_at,
                'status' => $sm->status_transaksi,
                'badge_color' => 'amber',
            ]);
        }

        $recentPbb = PbTransaksiPbb::orderByDesc('tanggal')->orderByDesc('id')->limit(5)->get();
        foreach ($recentPbb as $pb) {
            $recentActivities->push([
                'id' => 'PBB-' . $pb->id,
                'layanan' => 'PBB',
                'kode' => $pb->nomor_transaksi,
                'nama' => $pb->nama_wajib_pajak,
                'detail' => 'NOP: ' . $pb->nop . ' (Thn ' . $pb->tahun_pajak . ')',
                'nominal' => (float)$pb->total_bayar,
                'jasa' => (float)$pb->biaya_admin,
                'tanggal' => $pb->tanggal->format('d/m/Y'),
                'created_at' => $pb->created_at,
                'status' => $pb->status,
                'badge_color' => 'emerald',
            ]);
        }

        $recentUmum = PbTransaksiUmum::orderByDesc('tanggal')->orderByDesc('id')->limit(5)->get();
        foreach ($recentUmum as $um) {
            $recentActivities->push([
                'id' => 'UM-' . $um->id,
                'layanan' => 'Transaksi Umum',
                'kode' => $um->nomor_transaksi,
                'nama' => $um->nama_pelanggan,
                'detail' => $um->jenis_transaksi . ' - ' . $um->nomor_tujuan,
                'nominal' => (float)$um->total_bayar,
                'jasa' => (float)$um->biaya_jasa_agen,
                'tanggal' => $um->tanggal->format('d/m/Y'),
                'created_at' => $um->created_at,
                'status' => $um->status,
                'badge_color' => 'indigo',
            ]);
        }

        $sortedRecent = $recentActivities->sortByDesc('created_at')->values()->take(10);

        $stats = [
            'period' => $period,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_transaksi' => $totalTransaksi,
            'today_count' => $todayCount,
            'total_pendapatan_jasa' => $totalPendapatanJasa,
            'total_kas_masuk' => $kasMasuk,
            'total_kas_keluar' => $kasKeluar,
            'biaya_operasional' => $biayaOperasional,
            'beban_admin_bank' => $totalBebanAdminBank,
            'laba_bersih' => $labaBersih,
            'saldo_cash' => $saldoCashLp,
            'saldo_rekening' => $saldoRekeningLp,
            'total_saldo' => $totalSaldoLp,
            'layanan_breakdown' => [
                'laku_pandai' => [
                    'label' => 'Laku Pandai',
                    'count' => $lpCount,
                    'pendapatan' => $lpTotalJasa,
                    'volume' => $lpVolMasuk,
                ],
                'samsat' => [
                    'label' => 'Samsat Budiman',
                    'count' => $samsatCount,
                    'pendapatan' => $samsatJasa,
                    'volume' => $samsatTotal,
                ],
                'pbb' => [
                    'label' => 'PBB',
                    'count' => $pbbCount,
                    'pendapatan' => $pbbJasa,
                    'volume' => $pbbTotal,
                ],
                'umum' => [
                    'label' => 'Transaksi Umum',
                    'count' => $umumCount,
                    'pendapatan' => $umumJasa,
                    'volume' => $umumTotal,
                ],
            ],
            'recent' => $sortedRecent,
            'master_counts' => [
                'nasabah_lp' => PbNasabahLakuPandai::count(),
                'wp_samsat' => PbWajibPajakSamsat::count(),
            ],
        ];

        return Inertia::render('Perdagangan Besar/Dashboard', [
            'unit' => $unit,
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}

