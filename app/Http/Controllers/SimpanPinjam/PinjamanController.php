<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StorePinjamanRequest;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Services\PinjamanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PinjamanController extends Controller
{
    public function __construct(private PinjamanService $pinjamanService) {}

    public function index(Request $request): Response
    {
        $query = Pinjaman::with('nasabah');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('nasabah', fn($q) => $q
                ->where('nama', 'like', $search)
                ->orWhere('nomor_rekening', 'like', $search)
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter bulan (format: YYYY-MM)
        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $query->whereYear('tanggal_akad', $year)
                  ->whereMonth('tanggal_akad', $month);
        }

        $pinjaman = $query->orderByDesc('tanggal_akad')->paginate(15)->withQueryString();

        // Summary total keseluruhan (tanpa filter bulan)
        $summaryAll = Pinjaman::selectRaw('
            COUNT(*) as total_pinjaman,
            SUM(pinjaman_pokok) as total_pokok,
            SUM(total_tagihan) as total_tagihan,
            SUM(sisa_pinjaman) as total_sisa
        ')->first();

        // Summary total sesuai filter aktif (termasuk bulan jika ada)
        $summaryFiltered = (clone $query->getQuery());
        $summaryFiltered = Pinjaman::selectRaw('
            COUNT(*) as total_pinjaman,
            SUM(pinjaman_pokok) as total_pokok,
            SUM(total_tagihan) as total_tagihan,
            SUM(sisa_pinjaman) as total_sisa
        ');
        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $summaryFiltered->whereYear('tanggal_akad', $year)
                            ->whereMonth('tanggal_akad', $month);
        }
        if ($request->filled('status')) {
            $summaryFiltered->where('status', $request->status);
        }
        $summaryFiltered = $summaryFiltered->first();

        // Daftar bulan yang memiliki data pinjaman (untuk dropdown)
        $availableBulan = Pinjaman::selectRaw("DATE_FORMAT(tanggal_akad, '%Y-%m') as bulan")
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->pluck('bulan');

        return Inertia::render('SimpanPinjam/Pinjaman/Index', [
            'pinjaman'         => $pinjaman,
            'filters'          => $request->only(['search', 'status', 'bulan']),
            'summaryAll'       => $summaryAll,
            'summaryFiltered'  => $summaryFiltered,
            'availableBulan'   => $availableBulan,
        ]);
    }

    public function create(): Response
    {
        $nasabah = Nasabah::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nomor_rekening']);

        return Inertia::render('SimpanPinjam/Pinjaman/Create', [
            'nasabahOptions' => $nasabah,
        ]);
    }

    public function store(StorePinjamanRequest $request)
    {
        $nasabah  = Nasabah::findOrFail($request->nasabah_id);
        $pinjaman = $this->pinjamanService->buat($nasabah, $request->validated());

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('success', 'Pinjaman berhasil ditambahkan.');
    }

    public function show(Pinjaman $pinjaman): Response
    {
        $pinjaman->load(['nasabah', 'angsuran' => fn($q) => $q->orderBy('angsuran_ke')]);

        return Inertia::render('SimpanPinjam/Pinjaman/Show', [
            'pinjaman' => $pinjaman,
        ]);
    }

    public function kalkulasi(Request $request)
    {
        $request->validate([
            'pinjaman_pokok'  => 'required|numeric|min:1',
            'bunga'           => 'required|numeric|min:0|max:100',
            'nominal_setoran' => 'required|numeric|min:1',
        ]);

        return response()->json(
            $this->pinjamanService->kalkulasi(
                $request->pinjaman_pokok,
                $request->bunga,
                $request->nominal_setoran
            )
        );
    }
}
