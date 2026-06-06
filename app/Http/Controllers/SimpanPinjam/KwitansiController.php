<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreKwitansiRequest;
use App\Models\Kwitansi;
use App\Models\Nasabah;
use App\Services\KwitansiService;
use App\Services\NomorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KwitansiController extends Controller
{
    public function __construct(
        private KwitansiService $kwitansiService,
        private NomorService $nomorService,
    ) {}

    public function index(Request $request): Response
    {
        $query = Kwitansi::with('nasabah')->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', $search)
                  ->orWhere('keperluan', 'like', $search)
                  ->orWhereHas('nasabah', fn($sq) => $sq->where('nama', 'like', $search));
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        $kwitansi = $query->paginate(15)->withQueryString();

        return Inertia::render('SimpanPinjam/Kwitansi/Index', [
            'kwitansi' => $kwitansi,
            'filters'  => $request->only(['search', 'start_date', 'end_date']),
        ]);
    }

    public function create(): Response
    {
        $nasabah = Nasabah::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'nomor_rekening', 'alamat', 'no_hp']);

        return Inertia::render('SimpanPinjam/Kwitansi/Create', [
            'nasabahOptions' => $nasabah,
        ]);
    }

    public function store(StoreKwitansiRequest $request)
    {
        $nasabah  = Nasabah::findOrFail($request->nasabah_id);
        $kwitansi = $this->kwitansiService->buat($nasabah, $request->validated());

        return redirect()->route('kwitansi.print', $kwitansi)
            ->with('success', 'Kwitansi berhasil dibuat.');
    }

    public function print(Kwitansi $kwitansi)
    {
        $kwitansi->load('nasabah');
        $terbilang = $this->nomorService->terbilang($kwitansi->nominal);

        return view('exports.simpan-pinjam.kwitansi', [
            'kwitansi'  => $kwitansi,
            'terbilang' => $terbilang,
        ]);
    }
}
