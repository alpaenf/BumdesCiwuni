<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\PeriodeTabungan;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\NomorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PeriodeTabunganController extends Controller
{
    public function index(): Response
    {
        $periodes = PeriodeTabungan::orderByDesc('tanggal_mulai')->get();
        $periodeAktif = PeriodeTabungan::where('status', 'aktif')->first();

        return Inertia::render('SimpanPinjam/Tabungan/Periode', [
            'periodes' => $periodes,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
        ]);

        if (PeriodeTabungan::where('status', 'aktif')->exists()) {
            return back()->withErrors(['message' => 'Masih ada periode yang aktif. Tutup terlebih dahulu.']);
        }

        PeriodeTabungan::create([
            'nama' => $request->nama,
            'tanggal_mulai' => $request->tanggal_mulai,
            'status' => 'aktif',
        ]);

        return back()->with('success', 'Periode tabungan baru berhasil dibuka.');
    }

    public function tutup(Request $request, PeriodeTabungan $periode)
    {
        $request->validate([
            'tanggal_tutup' => 'required|date',
        ]);

        if ($periode->status !== 'aktif') {
            return back()->withErrors(['message' => 'Periode sudah ditutup.']);
        }

        DB::transaction(function () use ($periode, $request) {
            $nomorService = app(NomorService::class);
            $tabungans = Tabungan::where('saldo', '>', 0)->get();
            $totalPendapatan = 0;

            $unitId = auth()->user()->unit_id;
            $prefix = $unitId ? "unit_{$unitId}_" : "global_";
            $endapanWajib = \App\Models\Setting::get($prefix . 'endapan_wajib_tabungan', 20000);

            foreach ($tabungans as $tabungan) {
                $potongan = min($endapanWajib, $tabungan->saldo);
                $saldoBaru = $tabungan->saldo - $potongan;

                $tabungan->update(['saldo' => $saldoBaru]);

                TransaksiTabungan::create([
                    'tabungan_id' => $tabungan->id,
                    'tanggal' => $request->tanggal_tutup,
                    'nomor_transaksi' => $nomorService->generateNomorTransaksi(),
                    'jenis_transaksi' => 'tutup_periode',
                    'keterangan' => 'Penutupan Periode: Potongan Endapan Wajib',
                    'nominal' => $potongan,
                    'administrasi' => 0,
                    'saldo_setelah' => $saldoBaru,
                ]);

                $totalPendapatan += $potongan;
            }

            $periode->update([
                'tanggal_tutup' => $request->tanggal_tutup,
                'status' => 'tutup',
            ]);

            // Here we could also log $totalPendapatan to a Kas BUMDes if there was a centralized Kas model,
            // but for now, the requirement is to generate the report / record it as admin income.
        });

        return back()->with('success', 'Periode berhasil ditutup. Endapan wajib telah diproses.');
    }
}
