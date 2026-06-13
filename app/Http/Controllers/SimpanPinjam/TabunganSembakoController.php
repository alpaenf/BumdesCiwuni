<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Nasabah;
use App\Models\Setting;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\TabunganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TabunganSembakoController extends Controller
{
    public function __construct(private TabunganService $tabunganService) {}

    public function index(Request $request): Response
    {
        $query = Nasabah::whereHas('tabungan', function ($q) {
            $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO);
        })->with(['tabungan' => function ($q) {
            $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO);
        }]);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', $search)
                  ->orWhere('nomor_rekening', 'like', $search);
            });
        }

        $nasabah = $query->where('status', 'aktif')->orderBy('nama')->paginate(15)->withQueryString();

        return Inertia::render('SimpanPinjam/TabunganSembako/Index', [
            'nasabah' => $nasabah,
            'filters' => $request->only(['search']),
        ]);
    }

    public function setor(Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_SEMBAKO],
            ['saldo' => 0]
        );

        return Inertia::render('SimpanPinjam/TabunganSembako/Setor', [
            'nasabah'  => $nasabah,
            'tabungan' => $tabungan,
        ]);
    }

    public function storeSetor(StoreTransaksiRequest $request, Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_SEMBAKO],
            ['saldo' => 0]
        );
        $transaksi = $this->tabunganService->setor($tabungan, $request->validated());

        return redirect()->route('tabungan-sembako.index')
            ->with('success', 'Setoran sembako berhasil dicatat.')
            ->with('struk_url', route('tabungan-sembako.struk', $transaksi));
    }

    public function ambil(Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_SEMBAKO],
            ['saldo' => 0]
        );

        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        return Inertia::render('SimpanPinjam/TabunganSembako/Ambil', [
            'nasabah'    => $nasabah,
            'tabungan'   => $tabungan,
            'biayaAdmin' => (int) Setting::get($prefix . 'biaya_admin_sembako', 20000),
        ]);
    }


    public function storeAmbil(StoreTransaksiRequest $request, Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_SEMBAKO],
            ['saldo' => 0]
        );

        try {
            $transaksi = $this->tabunganService->ambilSembako($tabungan, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['nominal' => $e->getMessage()]);
        }

        return redirect()->route('tabungan-sembako.index')
            ->with('success', 'Pengambilan sembako berhasil dicatat.')
            ->with('struk_url', route('tabungan-sembako.struk', $transaksi));
    }

    public function struk(TransaksiTabungan $transaksi)
    {
        $transaksi->load('tabungan.nasabah');

        return view('exports.simpan-pinjam.struk-tabungan-sembako', ['transaksi' => $transaksi]);
    }

    public function pdf(TransaksiTabungan $transaksi)
    {
        $transaksi->load('tabungan.nasabah');

        $pdf = Pdf::loadView('exports.simpan-pinjam.struk-tabungan-sembako-pdf', ['transaksi' => $transaksi])
            ->setPaper([0, 0, 226.77, 566.93]);

        $filename = 'struk-sembako-' . ($transaksi->nomor_transaksi ?? $transaksi->id) . '.pdf';

        return $pdf->stream($filename);
    }

    public function updateTransaksi(Request $request, TransaksiTabungan $transaksi)
    {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'nominal'    => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($transaksi, $validated) {
            $selisih = (float)$validated['nominal'] - (float)$transaksi->nominal;

            $transaksi->update([
                'tanggal'    => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'nominal'    => $validated['nominal'],
            ]);

            if ($selisih != 0) {
                $tabungan = $transaksi->tabungan;
                $allTrx = $tabungan->transaksi()->orderBy('tanggal')->orderBy('id')->get();

                $saldo = 0;
                foreach ($allTrx as $trx) {
                    if ($trx->jenis_transaksi === TransaksiTabungan::JENIS_SETOR) {
                        $saldo += $trx->nominal;
                    } else {
                        $saldo = max(0, $saldo - $trx->nominal - $trx->administrasi);
                    }
                    $trx->updateQuietly(['saldo_setelah' => $saldo]);
                }

                $tabungan->update(['saldo' => $saldo]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui.']);
    }

    public function riwayat(Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_SEMBAKO],
            ['saldo' => 0]
        );
        $transaksi = $tabungan->transaksi()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'tanggal', 'nomor_transaksi', 'jenis_transaksi', 'nominal', 'keterangan', 'foto_barang']);

        return response()->json($transaksi);
    }
}
