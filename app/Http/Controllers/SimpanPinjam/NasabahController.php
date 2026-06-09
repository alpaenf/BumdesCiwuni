<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreNasabahRequest;
use App\Http\Requests\UpdateNasabahRequest;
use App\Models\Nasabah;
use App\Services\NasabahService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NasabahController extends Controller
{
    public function __construct(private NasabahService $nasabahService) {}

    public function index(Request $request): Response
    {
        $query = Nasabah::query();

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', $search)
                  ->orWhere('nomor_rekening', 'like', $search)
                  ->orWhere('nomor_registrasi', 'like', $search)
                  ->orWhere('nik', 'like', $search)
                  ->orWhere('no_hp', 'like', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nasabah = $query->orderBy('nama')->paginate(15)->withQueryString();

        return Inertia::render('SimpanPinjam/Nasabah/Index', [
            'nasabah' => $nasabah,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('SimpanPinjam/Nasabah/Form');
    }

    public function store(StoreNasabahRequest $request)
    {
        $nasabah = $this->nasabahService->create($request->validated());

        return redirect()->route('nasabah.show', $nasabah)
            ->with('success', 'Nasabah berhasil ditambahkan.');
    }

    public function show(Nasabah $nasabah): Response
    {
        $nasabah->load([
            'tabungan.transaksi',
            'pinjaman.angsuran',
        ]);

        return Inertia::render('SimpanPinjam/Nasabah/Show', [
            'nasabah' => $nasabah,
        ]);
    }

    public function edit(Nasabah $nasabah): Response
    {
        return Inertia::render('SimpanPinjam/Nasabah/Form', [
            'nasabah' => $nasabah,
        ]);
    }

    public function update(UpdateNasabahRequest $request, Nasabah $nasabah)
    {
        $this->nasabahService->update($nasabah, $request->validated());

        return redirect()->route('nasabah.show', $nasabah)
            ->with('success', 'Data nasabah berhasil diperbarui.');
    }

    public function destroy(Nasabah $nasabah)
    {
        $this->nasabahService->delete($nasabah);

        return redirect()->route('nasabah.index')
            ->with('success', 'Nasabah berhasil dihapus.');
    }

    public function print(Nasabah $nasabah)
    {
        $nasabah->load(['tabungan', 'pinjaman']);

        return view('exports.simpan-pinjam.nasabah', ['nasabah' => $nasabah]);
    }

    public function pinjamanAktif(Nasabah $nasabah)
    {
        $pinjaman = $nasabah->pinjaman()
            ->where('status', 'aktif')
            ->get(['id', 'pinjaman_pokok', 'total_tagihan', 'sisa_pinjaman', 'nominal_setoran', 'jumlah_angsuran', 'biaya_tambahan']);
            
        $pinjaman->map(function($p) {
            $countPaid = $p->angsuran()->count();
            $p->angsuran_ke = $countPaid + 1;
            return $p;
        });

        return response()->json($pinjaman);
    }
}
