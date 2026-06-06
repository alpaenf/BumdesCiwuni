<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreAngsuranRequest;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Services\AngsuranService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AngsuranController extends Controller
{
    public function __construct(private AngsuranService $angsuranService) {}

    public function index(Request $request): Response
    {
        $query = Angsuran::with(['pinjaman.nasabah'])->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('pinjaman.nasabah', fn($q) => $q
                ->where('nama', 'like', $search)
                ->orWhere('nomor_rekening', 'like', $search)
            );
        }

        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        } else {
            if ($request->filled('start_date')) $query->whereDate('tanggal', '>=', $request->start_date);
            if ($request->filled('end_date'))   $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $angsuran = $query->paginate(15)->withQueryString();

        // Summary sesuai filter aktif
        $summaryQuery = Angsuran::query();
        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $summaryQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        } else {
            if ($request->filled('start_date')) $summaryQuery->whereDate('tanggal', '>=', $request->start_date);
            if ($request->filled('end_date'))   $summaryQuery->whereDate('tanggal', '<=', $request->end_date);
        }
        $summary = $summaryQuery->selectRaw('COUNT(*) as total_transaksi, SUM(jumlah_bayar) as total_bayar')->first();

        // Summary keseluruhan
        $summaryAll = Angsuran::selectRaw('COUNT(*) as total_transaksi, SUM(jumlah_bayar) as total_bayar')->first();

        // Daftar bulan yang ada datanya
        $availableBulan = Angsuran::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan")
            ->groupBy('bulan')->orderByDesc('bulan')->pluck('bulan');

        $nasabahOptions = \App\Models\Nasabah::whereHas('pinjaman', fn($q) => $q->where('status', 'aktif'))
            ->orderBy('nama')
            ->get(['id', 'nama', 'nomor_rekening']);

        return Inertia::render('SimpanPinjam/Angsuran/Index', [
            'angsuran'       => $angsuran,
            'nasabahOptions' => $nasabahOptions,
            'filters'        => $request->only(['search', 'start_date', 'end_date', 'bulan']),
            'summary'        => $summary,
            'summaryAll'     => $summaryAll,
            'availableBulan' => $availableBulan,
        ]);
    }

    public function create(Request $request): Response
    {
        $nasabahOptions = \App\Models\Nasabah::whereHas('pinjaman', fn($q) => $q->where('status', 'aktif'))
            ->orderBy('nama')
            ->get(['id', 'nama', 'nomor_rekening']);

        $pinjamanOptions = [];
        if ($request->filled('nasabah_id')) {
            $pinjamanOptions = Pinjaman::where('nasabah_id', $request->nasabah_id)
                ->where('status', 'aktif')
                ->get(['id', 'pinjaman_pokok', 'total_tagihan', 'sisa_pinjaman', 'nominal_setoran', 'jumlah_angsuran']);
        }

        return Inertia::render('SimpanPinjam/Angsuran/Create', [
            'nasabahOptions'  => $nasabahOptions,
            'pinjamanOptions' => $pinjamanOptions,
            'filters'         => $request->only(['nasabah_id']),
        ]);
    }

    public function store(StoreAngsuranRequest $request)
    {
        $pinjaman  = Pinjaman::findOrFail($request->pinjaman_id);
        $angsuran  = $this->angsuranService->bayar($pinjaman, $request->validated());

        return redirect()->route('angsuran.index')
            ->with('success', 'Angsuran berhasil dicatat.')
            ->with('struk_url', route('angsuran.struk', $angsuran));
    }

    public function struk(Angsuran $angsuran)
    {
        $angsuran->load('pinjaman.nasabah');

        return view('exports.simpan-pinjam.struk-pinjaman', ['angsuran' => $angsuran]);
    }
}
