<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Exports\AngsuranExport;
use App\Exports\NasabahExport;
use App\Exports\PinjamanExport;
use App\Exports\TabunganExport;
use App\Models\Angsuran;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\PinjamanStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function __construct(private PinjamanStatusService $statusService) {}

    public function index(): Response
    {
        return Inertia::render('SimpanPinjam/Laporan/Index');
    }

    private function logRequest(Request $request, string $source)
    {
        \Illuminate\Support\Facades\Log::info("LaporanController::{$source} - Request: " . json_encode($request->all()));
    }

    // =====================================================================
    // NASABAH
    // =====================================================================
    public function nasabah(Request $request): Response
    {
        $query = Nasabah::query();
        $this->applyDateFilter($query, $request, 'tanggal_bergabung');
        $data = $query->orderBy('nama')->get();

        return Inertia::render('SimpanPinjam/Laporan/Nasabah', [
            'data'    => $data,
            'filters' => $request->only(['start_date', 'end_date', 'bulan']),
            'summary' => [
                'total'       => $data->count(),
                'aktif'       => $data->where('status', 'aktif')->count(),
                'tidak_aktif' => $data->where('status', 'tidak aktif')->count(),
            ],
        ]);
    }

    public function nasabahPdf(Request $request)
    {
        $query = Nasabah::query();
        $this->applyDateFilter($query, $request, 'tanggal_bergabung');
        $data = $query->orderBy('nama')->get();
        $summary = ['total' => $data->count(), 'aktif' => $data->where('status', 'aktif')->count(), 'tidak_aktif' => $data->where('status', 'tidak aktif')->count()];
        $filters = $request->only(['start_date', 'end_date']);

        $pdf = Pdf::loadView('exports.simpan-pinjam.laporan.nasabah', compact('data', 'summary', 'filters'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('laporan-nasabah-' . now()->format('Ymd') . '.pdf');
    }

    public function nasabahExcel(Request $request)
    {
        return Excel::download(new NasabahExport($request), 'laporan-nasabah-' . now()->format('Ymd') . '.xlsx');
    }

    // =====================================================================
    // TABUNGAN
    // =====================================================================
    public function tabungan(Request $request): Response
    {
        $query     = TransaksiTabungan::with('tabungan.nasabah');
        $this->applyDateFilter($query, $request, 'tanggal');
        $transaksi = $query->orderBy('tanggal')->get();

        return Inertia::render('SimpanPinjam/Laporan/Tabungan', [
            'transaksi' => $transaksi,
            'filters'   => $request->only(['start_date', 'end_date', 'bulan']),
            'summary'   => [
                'total_setoran'   => $transaksi->where('jenis_transaksi', 'setor')->sum('nominal'),
                'total_penarikan' => $transaksi->whereIn('jenis_transaksi', ['tarik_tunai', 'tarik_sembako'])->sum('nominal'),
                'total_admin'     => $transaksi->sum('administrasi'),
            ],
        ]);
    }

    public function tabunganPdf(Request $request)
    {
        $query     = TransaksiTabungan::with('tabungan.nasabah');
        $this->applyDateFilter($query, $request, 'tanggal');
        $transaksi = $query->orderBy('tanggal')->get();
        $summary   = [
            'total_setoran'   => $transaksi->where('jenis_transaksi', 'setor')->sum('nominal'),
            'total_penarikan' => $transaksi->whereIn('jenis_transaksi', ['tarik_tunai', 'tarik_sembako'])->sum('nominal'),
            'total_admin'     => $transaksi->sum('administrasi'),
        ];
        $filters = $request->only(['start_date', 'end_date']);

        $pdf = Pdf::loadView('exports.simpan-pinjam.laporan.tabungan', compact('transaksi', 'summary', 'filters'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('laporan-tabungan-' . now()->format('Ymd') . '.pdf');
    }

    public function tabunganExcel(Request $request)
    {
        return Excel::download(new TabunganExport($request), 'laporan-tabungan-' . now()->format('Ymd') . '.xlsx');
    }

    // =====================================================================
    // PINJAMAN
    // =====================================================================
    public function pinjaman(Request $request): Response
    {
        $query = Pinjaman::with('nasabah');
        $this->applyDateFilter($query, $request, 'tanggal_akad');
        if ($request->filled('status')) $query->where('status', $request->status);

        $pinjaman = $query->orderByDesc('tanggal_akad')->get();
        $enriched = $pinjaman->map(function (Pinjaman $p) {
            $p->loadMissing('angsuran');
            return array_merge($p->toArray(), ['computed_status' => $this->statusService->status($p)]);
        });

        return Inertia::render('SimpanPinjam/Laporan/Pinjaman', [
            'pinjaman' => $enriched,
            'filters'  => $request->only(['start_date', 'end_date', 'status', 'bulan']),
            'summary'  => [
                'total'         => $pinjaman->count(),
                'aktif'         => $pinjaman->where('status', 'aktif')->count(),
                'lunas'         => $pinjaman->where('status', 'lunas')->count(),
                'total_pokok'   => $pinjaman->sum('pinjaman_pokok'),
                'total_tagihan' => $pinjaman->sum('total_tagihan'),
                'total_sisa'    => $pinjaman->sum('sisa_pinjaman'),
            ],
        ]);
    }

    public function pinjamanPdf(Request $request)
    {
        $query = Pinjaman::with('nasabah');
        $this->applyDateFilter($query, $request, 'tanggal_akad');
        if ($request->filled('status')) $query->where('status', $request->status);

        $rawPinjaman = $query->orderByDesc('tanggal_akad')->get();
        $rawPinjaman->each(fn($p) => $p->loadMissing('angsuran'));
        $pinjaman = $rawPinjaman->map(fn($p) => array_merge($p->toArray(), ['computed_status' => $this->statusService->status($p)]));

        $summary = [
            'total'         => $rawPinjaman->count(),
            'aktif'         => $rawPinjaman->where('status', 'aktif')->count(),
            'lunas'         => $rawPinjaman->where('status', 'lunas')->count(),
            'total_pokok'   => $rawPinjaman->sum('pinjaman_pokok'),
            'total_tagihan' => $rawPinjaman->sum('total_tagihan'),
            'total_sisa'    => $rawPinjaman->sum('sisa_pinjaman'),
        ];
        $filters = $request->only(['start_date', 'end_date', 'status']);

        $pdf = Pdf::loadView('exports.simpan-pinjam.laporan.pinjaman', compact('pinjaman', 'summary', 'filters'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('laporan-pinjaman-' . now()->format('Ymd') . '.pdf');
    }

    public function pinjamanExcel(Request $request)
    {
        return Excel::download(new PinjamanExport($request, $this->statusService), 'laporan-pinjaman-' . now()->format('Ymd') . '.xlsx');
    }

    // =====================================================================
    // ANGSURAN
    // =====================================================================
    public function angsuran(Request $request): Response
    {
        $query    = Angsuran::with('pinjaman.nasabah');
        $this->applyDateFilter($query, $request, 'tanggal');
        $angsuran = $query->orderByDesc('tanggal')->get();

        return Inertia::render('SimpanPinjam/Laporan/Angsuran', [
            'angsuran' => $angsuran,
            'filters'  => $request->only(['start_date', 'end_date', 'bulan']),
            'summary'  => [
                'total_transaksi' => $angsuran->count(),
                'total_bayar'     => $angsuran->sum('jumlah_bayar'),
            ],
        ]);
    }

    public function angsuranPdf(Request $request)
    {
        $query    = Angsuran::with('pinjaman.nasabah');
        $this->applyDateFilter($query, $request, 'tanggal');
        $angsuran = $query->orderByDesc('tanggal')->get();
        $summary  = ['total_transaksi' => $angsuran->count(), 'total_bayar' => $angsuran->sum('jumlah_bayar')];
        $filters  = $request->only(['start_date', 'end_date']);

        $pdf = Pdf::loadView('exports.simpan-pinjam.laporan.angsuran', compact('angsuran', 'summary', 'filters'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('laporan-angsuran-' . now()->format('Ymd') . '.pdf');
    }

    public function angsuranExcel(Request $request)
    {
        return Excel::download(new AngsuranExport($request), 'laporan-angsuran-' . now()->format('Ymd') . '.xlsx');
    }

    // =====================================================================
    // KAS
    // =====================================================================
    public function kas(Request $request): Response
    {
        $this->logRequest($request, 'kas');
        [$summary, $rincian] = $this->buildKasSummary($request);

        return Inertia::render('SimpanPinjam/Laporan/Kas', [
            'filters' => $request->only(['start_date', 'end_date', 'bulan']),
            'summary' => $summary,
            'rincian' => $rincian,
        ]);
    }

    public function kasPdf(Request $request)
    {
        [$summary, $rincian] = $this->buildKasSummary($request);
        $filters = $request->only(['start_date', 'end_date']);

        $pdf = Pdf::loadView('exports.simpan-pinjam.laporan.kas', compact('summary', 'rincian', 'filters'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('laporan-kas-' . now()->format('Ymd') . '.pdf');
    }

    // Kas doesn't need tabular Excel — use PDF only (summary report)

    // =====================================================================
    // HELPERS
    // =====================================================================
    private function applyDateFilter($query, Request $request, string $field): void
    {
        if ($request->filled('bulan')) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $request->bulan);
                $query->whereBetween($field, [
                    $date->copy()->startOfMonth()->toDateString(),
                    $date->copy()->endOfMonth()->toDateString()
                ]);
                return;
            } catch (\Exception $e) {
                // Fallback to start_date and end_date
            }
        }

        if ($request->filled('start_date')) $query->where($field, '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->where($field, '<=', $request->end_date);
    }

    private function buildKasSummary(Request $request): array
    {
        $querySetorReguler = TransaksiTabungan::where('jenis_transaksi', 'setor')
            ->whereHas('tabungan', fn($q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER));
        $querySetorSembako = TransaksiTabungan::where('jenis_transaksi', 'setor')
            ->whereHas('tabungan', fn($q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO));

        $queryTarikReguler = TransaksiTabungan::whereIn('jenis_transaksi', ['tarik_tunai', 'tarik_sembako', 'tutup_periode'])
            ->whereHas('tabungan', fn($q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER));
        $queryTarikSembako = TransaksiTabungan::whereIn('jenis_transaksi', ['tarik_tunai', 'tarik_sembako', 'tutup_periode'])
            ->whereHas('tabungan', fn($q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO));

        $queryAngsuran = Angsuran::query();
        $queryPinjaman = Pinjaman::query();

        $this->applyDateFilter($querySetorReguler, $request, 'tanggal');
        $this->applyDateFilter($querySetorSembako, $request, 'tanggal');
        $this->applyDateFilter($queryTarikReguler, $request, 'tanggal');
        $this->applyDateFilter($queryTarikSembako, $request, 'tanggal');
        $this->applyDateFilter($queryAngsuran, $request, 'tanggal');
        $this->applyDateFilter($queryPinjaman, $request, 'tanggal_akad');

        $masukReguler  = $querySetorReguler->sum('nominal');
        $masukSembako  = $querySetorSembako->sum('nominal');
        $masukAngsuran = $queryAngsuran->sum('jumlah_bayar');
        
        $keluarReguler  = $queryTarikReguler->sum('nominal') + $queryTarikReguler->sum('administrasi');
        $keluarSembako  = $queryTarikSembako->sum('nominal') + $queryTarikSembako->sum('administrasi');
        $keluarPinjaman = $queryPinjaman->sum('pinjaman_pokok');
        
        $totalMasuk  = $masukReguler + $masukSembako + $masukAngsuran;
        $totalKeluar = $keluarReguler + $keluarSembako + $keluarPinjaman;

        $saldoReguler = Tabungan::where('jenis_tabungan', Tabungan::JENIS_REGULER)->sum('saldo');
        $saldoSembako = Tabungan::where('jenis_tabungan', Tabungan::JENIS_SEMBAKO)->sum('saldo');
        $totalAngsuranAll = Angsuran::sum('jumlah_bayar');
        $totalPinjamanAll = Pinjaman::sum('pinjaman_pokok');

        $masukRegulerItems = (clone $querySetorReguler)->with('tabungan.nasabah')->get();
        $masukSembakoItems = (clone $querySetorSembako)->with('tabungan.nasabah')->get();
        $masukAngsuranItems = (clone $queryAngsuran)->with('pinjaman.nasabah')->get();
        $keluarRegulerItems = (clone $queryTarikReguler)->with('tabungan.nasabah')->get();
        $keluarSembakoItems = (clone $queryTarikSembako)->with('tabungan.nasabah')->get();
        $keluarPinjamanItems = (clone $queryPinjaman)->with('nasabah')->get();

        return [[
            'masuk_reguler'      => (float) $masukReguler,
            'masuk_sembako'      => (float) $masukSembako,
            'masuk_angsuran'     => (float) $masukAngsuran,
            'total_masuk'        => (float) $totalMasuk,
            'keluar_reguler'     => (float) $keluarReguler,
            'keluar_sembako'     => (float) $keluarSembako,
            'keluar_pinjaman'    => (float) $keluarPinjaman,
            'total_keluar'       => (float) $totalKeluar,
            'saldo_kas'          => (float) ($totalMasuk - $totalKeluar),
            'saldo_reguler'      => (float) $saldoReguler,
            'saldo_sembako'      => (float) $saldoSembako,
            'total_saldo_all'    => (float) ($saldoReguler + $saldoSembako),
            'total_angsuran_all' => (float) $totalAngsuranAll,
            'total_pinjaman_all' => (float) $totalPinjamanAll,
        ], [
            'masuk_reguler'   => $masukRegulerItems,
            'masuk_sembako'   => $masukSembakoItems,
            'masuk_angsuran'  => $masukAngsuranItems,
            'keluar_reguler'  => $keluarRegulerItems,
            'keluar_sembako'  => $keluarSembakoItems,
            'keluar_pinjaman' => $keluarPinjamanItems,
        ]];
    }
}
