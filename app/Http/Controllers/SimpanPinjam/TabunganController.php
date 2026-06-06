<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Nasabah;
use App\Models\Setting;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\TabunganService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TabunganController extends Controller
{
    public function __construct(private TabunganService $tabunganService) {}

    public function index(Request $request): Response
    {
        $query = Nasabah::with(['tabungan' => function($q) {
            $q->where('jenis_tabungan', Tabungan::JENIS_REGULER);
        }]);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', $search)
                  ->orWhere('nomor_rekening', 'like', $search);
            });
        }

        $nasabah = $query->where('status', 'aktif')->orderBy('nama')->paginate(15)->withQueryString();

        return Inertia::render('SimpanPinjam/Tabungan/Index', [
            'nasabah' => $nasabah,
            'filters' => $request->only(['search']),
        ]);
    }

    public function setor(Request $request, Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->where('jenis_tabungan', Tabungan::JENIS_REGULER)->firstOrFail();

        return Inertia::render('SimpanPinjam/Tabungan/Setor', [
            'nasabah'  => $nasabah,
            'tabungan' => $tabungan,
        ]);
    }

    public function storeSetor(StoreTransaksiRequest $request, Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->where('jenis_tabungan', Tabungan::JENIS_REGULER)->firstOrFail();
        $transaksi = $this->tabunganService->setor($tabungan, $request->validated());

        return redirect()->route('tabungan.index')
            ->with('success', 'Setoran berhasil dicatat.')
            ->with('struk_url', route('tabungan.struk', $transaksi));
    }

    public function tarik(Request $request, Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->where('jenis_tabungan', Tabungan::JENIS_REGULER)->firstOrFail();

        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";
        $endapanWajib = \App\Models\Setting::get($prefix . 'endapan_wajib_tabungan', 20000);

        return Inertia::render('SimpanPinjam/Tabungan/Tarik', [
            'nasabah'         => $nasabah,
            'tabungan'        => $tabungan,
            'endapanWajib'    => $endapanWajib,
        ]);
    }

    public function storeTarik(StoreTransaksiRequest $request, Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->where('jenis_tabungan', Tabungan::JENIS_REGULER)->firstOrFail();

        try {
            $transaksi = $this->tabunganService->tarik($tabungan, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['nominal' => $e->getMessage()]);
        }

        return redirect()->route('tabungan.index')
            ->with('success', 'Penarikan berhasil dicatat.')
            ->with('struk_url', route('tabungan.struk', $transaksi));
    }

    public function struk(TransaksiTabungan $transaksi)
    {
        $transaksi->load('tabungan.nasabah');

        return view('exports.simpan-pinjam.struk-tabungan', ['transaksi' => $transaksi]);
    }

    public function riwayat(Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->where('jenis_tabungan', Tabungan::JENIS_REGULER)->firstOrFail();
        $transaksi = $tabungan->transaksi()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'tanggal', 'nomor_transaksi', 'jenis_transaksi', 'nominal']);

        return response()->json($transaksi);
    }
}
