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

class TabunganController extends Controller
{
    public function __construct(private TabunganService $tabunganService) {}

    public function index(Request $request): Response
    {
        $query = Nasabah::whereJsonContains('kategori', 'tabungan')
            ->with(['tabungan' => function($q) {
                $q->where('jenis_tabungan', Tabungan::JENIS_REGULER);
            }]);

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', $search)
                  ->orWhere('nomor_rekening', 'like', $search)
                  ->orWhere('nomor_registrasi', 'like', $search);
            });
        }

        $nasabah = $query->orderBy('nama')->paginate(15)->withQueryString();

        return Inertia::render('SimpanPinjam/Tabungan/Index', [
            'nasabah' => $nasabah,
            'filters' => $request->only(['search']),
        ]);
    }

    public function setor(Request $request, Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_REGULER],
            ['saldo' => 0]
        );

        return Inertia::render('SimpanPinjam/Tabungan/Setor', [
            'nasabah'  => $nasabah,
            'tabungan' => $tabungan,
        ]);
    }

    public function storeSetor(StoreTransaksiRequest $request, Nasabah $nasabah)
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_REGULER],
            ['saldo' => 0]
        );
        $transaksi = $this->tabunganService->setor($tabungan, $request->validated());

        return redirect()->route('tabungan.index')
            ->with('success', 'Setoran berhasil dicatat.')
            ->with('struk_url', route('tabungan.struk', $transaksi));
    }

    public function tarik(Request $request, Nasabah $nasabah): Response
    {
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_REGULER],
            ['saldo' => 0]
        );

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
        $tabungan = $nasabah->tabungan()->firstOrCreate(
            ['jenis_tabungan' => Tabungan::JENIS_REGULER],
            ['saldo' => 0]
        );

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

    public function pdf(TransaksiTabungan $transaksi)
    {
        $transaksi->load('tabungan.nasabah');

        $pdf = Pdf::loadView('exports.simpan-pinjam.struk-tabungan-pdf', ['transaksi' => $transaksi])
            ->setPaper([0, 0, 226.77, 566.93]);

        $filename = 'struk-tabungan-' . ($transaksi->nomor_transaksi ?? $transaksi->id) . '.pdf';

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
                // Recalculate saldo_setelah semua transaksi tabungan secara kronologis
                $tabungan = $transaksi->tabungan;
                $allTrx = $tabungan->transaksi()->orderBy('tanggal')->orderBy('id')->get();

                $saldo = 0;
                foreach ($allTrx as $trx) {
                    if (in_array($trx->jenis_transaksi, [TransaksiTabungan::JENIS_SETOR])) {
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
            ['jenis_tabungan' => Tabungan::JENIS_REGULER],
            ['saldo' => 0]
        );
        $transaksi = $tabungan->transaksi()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'tanggal', 'nomor_transaksi', 'jenis_transaksi', 'nominal']);

        return response()->json($transaksi);
    }
}
