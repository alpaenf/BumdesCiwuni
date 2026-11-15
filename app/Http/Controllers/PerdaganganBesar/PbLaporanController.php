<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbBiayaOperasional;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbTransaksiLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiPbb;
use App\Models\PerdaganganBesar\PbTransaksiSamsat;
use App\Models\PerdaganganBesar\PbTransaksiUmum;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PbLaporanController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Halaman Utama Laporan & Rekapitulasi
     */
    public function index(Request $request): Response
    {
        $unit = $this->getUnit();
        $tab = $request->input('tab', 'rekap'); // rekap, laku_pandai, samsat, pbb, umum, laba_rugi
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $subLayananUmum = $request->input('sub_layanan_umum'); // filter jenis layanan di tab umum
        $statusUmum = $request->input('status_umum'); // filter status di tab umum

        // 1. Data Rekap Per Layanan
        // Laku Pandai
        $lpTrx = PbTransaksiLakuPandai::whereBetween('tanggal', [$startDate, $endDate])->get();
        $lpCount = $lpTrx->count();
        $lpPendapatan = $lpTrx->sum('pendapatan_jasa_cash') + $lpTrx->sum('pendapatan_jasa_rekening');
        $lpVolume = $lpTrx->sum('cash_masuk') + $lpTrx->sum('rekening_masuk');

        // Samsat
        $samsatTrx = PbTransaksiSamsat::whereBetween('tanggal', [$startDate, $endDate])
            ->where('status_transaksi', 'SUCCES')->get();
        $samsatCount = $samsatTrx->count();
        $samsatPendapatan = $samsatTrx->sum('biaya_jasa_bumdes');
        $samsatVolume = $samsatTrx->sum('total_pembayaran');

        // PBB
        $pbbTrx = PbTransaksiPbb::whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', 'SUCCES')->get();
        $pbbCount = $pbbTrx->count();
        $pbbPendapatan = $pbbTrx->sum('biaya_admin');
        $pbbVolume = $pbbTrx->sum('total_bayar');

        // Transaksi Umum
        $umumTrx = PbTransaksiUmum::whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', 'SUCCES')->get();
        $umumCount = $umumTrx->count();
        $umumPendapatan = $umumTrx->sum('biaya_jasa_agen');
        $umumVolume = $umumTrx->sum('total_bayar');

        // Biaya Operasional
        $biayaOperasional = PbBiayaOperasional::whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');

        // Total
        $totalPendapatanJasa = $lpPendapatan + $samsatPendapatan + $pbbPendapatan + $umumPendapatan;
        $labaBersih = $totalPendapatanJasa - $biayaOperasional;

        $rekapLayanan = [
            [
                'layanan' => 'Laku Pandai',
                'transaksi' => $lpCount,
                'volume' => $lpVolume,
                'pendapatan_jasa' => $lpPendapatan,
            ],
            [
                'layanan' => 'Samsat Budiman',
                'transaksi' => $samsatCount,
                'volume' => $samsatVolume,
                'pendapatan_jasa' => $samsatPendapatan,
            ],
            [
                'layanan' => 'Pajak PBB',
                'transaksi' => $pbbCount,
                'volume' => $pbbVolume,
                'pendapatan_jasa' => $pbbPendapatan,
            ],
            [
                'layanan' => 'Transaksi Umum & PPOB',
                'transaksi' => $umumCount,
                'volume' => $umumVolume,
                'pendapatan_jasa' => $umumPendapatan,
            ],
        ];

        // 2. Data detail per tab
        $dataDetail = [];
        if ($tab === 'laku_pandai') {
            $dataDetail = PbTransaksiLakuPandai::with('nasabah')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderByDesc('tanggal')
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'samsat') {
            $dataDetail = PbTransaksiSamsat::with('wajibPajak')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderByDesc('tanggal')
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'pbb') {
            $dataDetail = PbTransaksiPbb::whereBetween('tanggal', [$startDate, $endDate])
                ->orderByDesc('tanggal')
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'umum') {
            $dataDetail = PbTransaksiUmum::whereBetween('tanggal', [$startDate, $endDate])
                ->when($subLayananUmum, fn($q) => $q->where('jenis_transaksi', $subLayananUmum))
                ->when($statusUmum, fn($q) => $q->where('status', $statusUmum))
                ->orderByDesc('tanggal')
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'laba_rugi') {
            $rincianBiaya = PbBiayaOperasional::selectRaw('kategori_biaya, sum(nominal) as total')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->groupBy('kategori_biaya')
                ->get();

            $dataDetail = [
                'pendapatan_laku_pandai' => $lpPendapatan,
                'pendapatan_samsat' => $samsatPendapatan,
                'pendapatan_pbb' => $pbbPendapatan,
                'pendapatan_umum' => $umumPendapatan,
                'total_pendapatan' => $totalPendapatanJasa,
                'rincian_biaya' => $rincianBiaya,
                'total_biaya' => $biayaOperasional,
                'laba_bersih' => $labaBersih,
            ];
        }

        return Inertia::render('Perdagangan Besar/Laporan/Index', [
            'unit' => $unit,
            'tab' => $tab,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'sub_layanan_umum' => $subLayananUmum,
                'status_umum' => $statusUmum,
            ],
            'rekap_layanan' => $rekapLayanan,
            'total_pendapatan' => $totalPendapatanJasa,
            'total_biaya' => $biayaOperasional,
            'laba_bersih' => $labaBersih,
            'data_detail' => $dataDetail,
        ]);
    }

    /**
     * Cetak Laporan PDF
     */
    public function cetakPdf(Request $request): SymfonyResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $subLayananUmum = $request->input('sub_layanan_umum'); // filter jenis layanan di tab umum
        $statusUmum = $request->input('status_umum'); // filter status di tab umum
        $layanan = $request->input('layanan', 'semua');

        $lpTrx = PbTransaksiLakuPandai::whereBetween('tanggal', [$startDate, $endDate])->get();
        $samsatTrx = PbTransaksiSamsat::whereBetween('tanggal', [$startDate, $endDate])->where('status_transaksi', 'SUCCES')->get();
        $pbbTrx = PbTransaksiPbb::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'SUCCES')->get();
        $umumTrx = PbTransaksiUmum::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'SUCCES')->get();
        $biayaOperasional = PbBiayaOperasional::whereBetween('tanggal', [$startDate, $endDate])->get();

        $pdf = Pdf::loadView('perdagangan_besar.laporan_pdf', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'layanan' => $layanan,
            'lpTrx' => $lpTrx,
            'samsatTrx' => $samsatTrx,
            'pbbTrx' => $pbbTrx,
            'umumTrx' => $umumTrx,
            'biayaOperasional' => $biayaOperasional,
        ]);

        return $pdf->download("Laporan-Perdagangan-Besar-{$startDate}-sd-{$endDate}.pdf");
    }
}

