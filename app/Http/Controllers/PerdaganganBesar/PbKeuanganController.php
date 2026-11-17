<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbBiayaOperasional;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbTransaksiLakuPandai;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PbKeuanganController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Buku Kas Masuk & Keluar (KAS MASUK & KELUAR / REKAP SALDO KAS)
     */
    public function kasIndex(Request $request): Response
    {
        $unit = $this->getUnit();
        $periodMode = $request->input('period_mode', 'date');
        $date = $request->input('date');
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $jenis = $request->input('jenis');
        $tipeKas = $request->input('tipe_kas');
        $kategori = $request->input('kategori');
        $search = $request->input('search');

        $query = PbKas::query()
            ->when($periodMode === 'month' && $month, function ($q) use ($month) {
                $q->whereYear('tanggal', substr($month, 0, 4))
                  ->whereMonth('tanggal', substr($month, 5, 2));
            })
            ->when($periodMode === 'date' && $date, function ($q) use ($date) {
                $q->where('tanggal', $date);
            })
            ->when($jenis, fn($q) => $q->where('jenis', $jenis))
            ->when($tipeKas, fn($q) => $q->where('tipe_kas', $tipeKas))
            ->when($kategori, fn($q) => $q->where('kategori_layanan', $kategori))
            ->when($search, fn($q) => $q->where('keterangan', 'like', "%{$search}%"))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $kasList = $query->paginate(15)->withQueryString();

        // Ringkasan Akumulasi Kas
        $totalMasuk = PbKas::where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = PbKas::where('jenis', 'keluar')->sum('nominal');
        $saldoKasTunai = PbKas::where('tipe_kas', 'Tunai')->where('jenis', 'masuk')->sum('nominal')
            - PbKas::where('tipe_kas', 'Tunai')->where('jenis', 'keluar')->sum('nominal');
        $saldoKasRekening = PbKas::where('tipe_kas', 'Rekening')->where('jenis', 'masuk')->sum('nominal')
            - PbKas::where('tipe_kas', 'Rekening')->where('jenis', 'keluar')->sum('nominal');

        // Saldo Laku Pandai (Snapshot Referensi)
        $latestLp = PbTransaksiLakuPandai::orderByDesc('tanggal')->orderByDesc('id')->first();

        $summary = [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo_akhir' => $totalMasuk - $totalKeluar,
            'saldo_tunai' => $saldoKasTunai,
            'saldo_rekening' => $saldoKasRekening,
            'saldo_lp_cash' => $latestLp ? (float)$latestLp->saldo_cash : 0,
            'saldo_lp_rek' => $latestLp ? (float)$latestLp->saldo_rekening : 0,
        ];

        return Inertia::render('Perdagangan Besar/Keuangan/Kas', [
            'unit' => $unit,
            'kas' => $kasList,
            'summary' => $summary,
            'filters' => [
                'period_mode' => $periodMode,
                'date' => $date,
                'month' => $month,
                'jenis' => $jenis,
                'tipe_kas' => $tipeKas,
                'kategori' => $kategori,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Cetak Laporan PDF Buku Kas Unit
     */
    public function kasCetakPdf(Request $request): SymfonyResponse
    {
        $periodMode = $request->input('period_mode', 'date');
        $date = $request->input('date');
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $jenis = $request->input('jenis');
        $tipeKas = $request->input('tipe_kas');
        $kategori = $request->input('kategori');
        $search = $request->input('search');

        $query = PbKas::query()
            ->when($periodMode === 'month' && $month, function ($q) use ($month) {
                $q->whereYear('tanggal', substr($month, 0, 4))
                  ->whereMonth('tanggal', substr($month, 5, 2));
            })
            ->when($periodMode === 'date' && $date, function ($q) use ($date) {
                $q->where('tanggal', $date);
            })
            ->when($jenis, fn($q) => $q->where('jenis', $jenis))
            ->when($tipeKas, fn($q) => $q->where('tipe_kas', $tipeKas))
            ->when($kategori, fn($q) => $q->where('kategori_layanan', $kategori))
            ->when($search, fn($q) => $q->where('keterangan', 'like', "%{$search}%"))
            ->orderBy('tanggal')
            ->orderBy('id');

        $kasList = $query->get();

        if ($periodMode === 'month' && $month) {
            $periodLabel = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
        } elseif ($date) {
            $periodLabel = Carbon::parse($date)->translatedFormat('d F Y');
        } else {
            $periodLabel = 'Semua Periode';
        }

        $totalMasuk = $kasList->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = $kasList->where('jenis', 'keluar')->sum('nominal');
        $saldoKasTunai = $kasList->where('tipe_kas', 'Tunai')->where('jenis', 'masuk')->sum('nominal')
            - $kasList->where('tipe_kas', 'Tunai')->where('jenis', 'keluar')->sum('nominal');
        $saldoKasRekening = $kasList->where('tipe_kas', 'Rekening')->where('jenis', 'masuk')->sum('nominal')
            - $kasList->where('tipe_kas', 'Rekening')->where('jenis', 'keluar')->sum('nominal');

        $summary = [
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'saldo_akhir' => $totalMasuk - $totalKeluar,
            'saldo_tunai' => $saldoKasTunai,
            'saldo_rekening' => $saldoKasRekening,
        ];

        $pdf = Pdf::loadView('perdagangan_besar.kas_pdf', [
            'periodMode' => $periodMode,
            'periodLabel' => $periodLabel,
            'summary' => $summary,
            'kasList' => $kasList,
        ]);

        return $pdf->download("Laporan-Buku-Kas-{$periodLabel}.pdf");
    }

    public function kasStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'kategori_layanan' => ['required', 'string'],
            'jenis' => ['required', 'in:masuk,keluar'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'tipe_kas' => ['required', 'in:Tunai,Rekening'],
            'keterangan' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $validated['nama_admin'] = $user ? $user->nama : 'Admin';

        PbKas::create($validated);
        return back()->with('success', 'Catatan kas berhasil disimpan!');
    }

    public function kasDestroy($id): RedirectResponse
    {
        $kas = PbKas::findOrFail($id);
        $kas->delete();
        return back()->with('success', 'Catatan kas berhasil dihapus!');
    }

    /**
     * Biaya Operasional Unit
     */
    public function biayaIndex(Request $request): Response
    {
        $unit = $this->getUnit();
        $periodMode = $request->input('period_mode', 'month');
        $date = $request->input('date');
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $kategori = $request->input('kategori');

        $query = PbBiayaOperasional::query()
            ->when($periodMode === 'date' && $date, fn($q) => $q->where('tanggal', $date))
            ->when($periodMode === 'month' && $month, function ($q) use ($month) {
                $q->whereYear('tanggal', substr($month, 0, 4))
                  ->whereMonth('tanggal', substr($month, 5, 2));
            })
            ->when($kategori, fn($q) => $q->where('kategori_biaya', $kategori))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $biayaList = $query->paginate(15)->withQueryString();

        $totalBiaya = (clone $query)->sum('nominal');

        return Inertia::render('Perdagangan Besar/Keuangan/BiayaOperasional', [
            'unit' => $unit,
            'biaya' => $biayaList,
            'total_biaya' => $totalBiaya,
            'filters' => [
                'period_mode' => $periodMode,
                'date' => $date,
                'month' => $month,
                'kategori' => $kategori,
            ],
        ]);
    }

    /**
     * Cetak Laporan PDF Biaya Operasional
     */
    public function biayaCetakPdf(Request $request): SymfonyResponse
    {
        $periodMode = $request->input('period_mode', 'month');
        $date = $request->input('date');
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $kategori = $request->input('kategori');

        $query = PbBiayaOperasional::query()
            ->when($periodMode === 'date' && $date, fn($q) => $q->where('tanggal', $date))
            ->when($periodMode === 'month' && $month, function ($q) use ($month) {
                $q->whereYear('tanggal', substr($month, 0, 4))
                  ->whereMonth('tanggal', substr($month, 5, 2));
            })
            ->when($kategori, fn($q) => $q->where('kategori_biaya', $kategori))
            ->orderBy('tanggal')
            ->orderBy('id');

        $biayaList = $query->get();

        if ($periodMode === 'month' && $month) {
            $periodLabel = Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y');
        } elseif ($date) {
            $periodLabel = Carbon::parse($date)->translatedFormat('d F Y');
        } else {
            $periodLabel = 'Semua Periode';
        }

        $totalBiaya = $biayaList->sum('nominal');
        $kategoriBreakdown = $biayaList->groupBy('kategori_biaya')->map(fn($items) => $items->sum('nominal'));

        $summary = [
            'total_biaya' => $totalBiaya,
            'kategori_breakdown' => $kategoriBreakdown,
        ];

        $pdf = Pdf::loadView('perdagangan_besar.biaya_pdf', [
            'periodMode' => $periodMode,
            'periodLabel' => $periodLabel,
            'summary' => $summary,
            'biayaList' => $biayaList,
        ]);

        return $pdf->download("Laporan-Biaya-Operasional-{$periodLabel}.pdf");
    }

    public function biayaStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'kategori_biaya' => ['required', 'string'],
            'uraian' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['nullable', 'string'],
            'catat_ke_kas' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $adminName = $user ? $user->nama : 'Admin';

        PbBiayaOperasional::create([
            'tanggal' => $validated['tanggal'],
            'kategori_biaya' => $validated['kategori_biaya'],
            'uraian' => $validated['uraian'],
            'nominal' => $validated['nominal'],
            'nama_admin' => $adminName,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        // Opsional catat otomatis ke kas keluar
        if (!empty($validated['catat_ke_kas'])) {
            PbKas::create([
                'tanggal' => $validated['tanggal'],
                'kategori_layanan' => 'Operasional',
                'jenis' => 'keluar',
                'nominal' => $validated['nominal'],
                'tipe_kas' => 'Tunai',
                'nama_admin' => $adminName,
                'keterangan' => 'Biaya ' . $validated['kategori_biaya'] . ': ' . $validated['uraian'],
            ]);
        }

        return back()->with('success', 'Biaya operasional berhasil dicatat!');
    }

    public function biayaDestroy($id): RedirectResponse
    {
        $biaya = PbBiayaOperasional::findOrFail($id);
        $biaya->delete();
        return back()->with('success', 'Biaya operasional berhasil dihapus!');
    }
}

