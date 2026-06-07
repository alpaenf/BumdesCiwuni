<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\NomorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PengaturanTabunganController extends Controller
{
    public function index(): Response
    {
        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        $keys = [
            $prefix . 'endapan_wajib_tabungan',
        ];

        $setting = Setting::getMany($keys, [
            $prefix . 'endapan_wajib_tabungan' => 20000,
        ]);

        return Inertia::render('SimpanPinjam/Pengaturan/Tabungan', [
            'pengaturan' => [
                'endapan_wajib' => $setting[$prefix . 'endapan_wajib_tabungan'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'endapan_wajib' => ['required', 'numeric', 'min:0'],
        ]);

        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        Setting::set($prefix . 'endapan_wajib_tabungan', $data['endapan_wajib']);

        return back()->with('success', 'Pengaturan tabungan berhasil disimpan.');
    }

    public function tutupBukuMasalIndex(): Response
    {
        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        $endapanWajibReguler = (float) Setting::get($prefix . 'endapan_wajib_tabungan', 20000);
        $endapanWajibSembako = (float) Setting::get($prefix . 'biaya_admin_sembako', 20000);

        $query = Tabungan::query();
        if ($unitId) {
            $query->whereHas('nasabah', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        $allTabungans = $query->with('nasabah')->get();

        $affectedAccounts = [];
        $totalDeduction = 0;

        foreach ($allTabungans as $tab) {
            $endapan = $tab->jenis_tabungan === Tabungan::JENIS_SEMBAKO ? $endapanWajibSembako : $endapanWajibReguler;
            if ($tab->saldo >= $endapan) {
                $affectedAccounts[] = [
                    'id' => $tab->id,
                    'nomor_rekening' => $tab->nasabah->nomor_rekening,
                    'nama' => $tab->nasabah->nama,
                    'jenis' => $tab->jenis_tabungan,
                    'saldo' => (float)$tab->saldo,
                    'potongan' => $endapan,
                    'saldo_setelah' => $tab->saldo - $endapan,
                ];
                $totalDeduction += $endapan;
            }
        }

        // Count nasabahs who don't have tabungan reguler / sembako
        $nasabahQuery = \App\Models\Nasabah::query();
        if ($unitId) {
            $nasabahQuery->where('unit_id', $unitId);
        }
        $nasabahs = $nasabahQuery->get();
        $totalNasabah = $nasabahs->count();

        $hasRegulerCount = Tabungan::where('jenis_tabungan', 'reguler')
            ->whereIn('nasabah_id', $nasabahs->pluck('id'))
            ->count();
        $unopenedRegulerCount = max(0, $totalNasabah - $hasRegulerCount);

        $hasSembakoCount = Tabungan::where('jenis_tabungan', 'sembako')
            ->whereIn('nasabah_id', $nasabahs->pluck('id'))
            ->count();
        $unopenedSembakoCount = max(0, $totalNasabah - $hasSembakoCount);

        return Inertia::render('SimpanPinjam/Tabungan/TutupBukuMassal', [
            'affectedAccounts' => $affectedAccounts,
            'totalDeduction' => (float) $totalDeduction,
            'biayaAdmin' => [
                'reguler' => $endapanWajibReguler,
                'sembako' => $endapanWajibSembako,
            ],
            'unopenedRegulerCount' => $unopenedRegulerCount,
            'unopenedSembakoCount' => $unopenedSembakoCount,
        ]);
    }

    public function tutupBukuMasalStore(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        $endapanWajibReguler = (float) Setting::get($prefix . 'endapan_wajib_tabungan', 20000);
        $endapanWajibSembako = (float) Setting::get($prefix . 'biaya_admin_sembako', 20000);

        $query = Tabungan::query();
        if ($unitId) {
            $query->whereHas('nasabah', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        $tabungans = $query->with('nasabah')->get();
        $processedCount = 0;

        DB::transaction(function () use ($tabungans, $endapanWajibReguler, $endapanWajibSembako, &$processedCount) {
            $nomorService = app(NomorService::class);
            $today = now()->format('Y-m-d');

            foreach ($tabungans as $tabungan) {
                $endapanWajib = $tabungan->jenis_tabungan === Tabungan::JENIS_SEMBAKO 
                    ? $endapanWajibSembako 
                    : $endapanWajibReguler;

                if ($tabungan->saldo >= $endapanWajib) {
                    $saldoBaru = $tabungan->saldo - $endapanWajib;

                    // Update account balance
                    $tabungan->update(['saldo' => $saldoBaru]);

                    // Record tutup_periode transaction
                    TransaksiTabungan::create([
                        'tabungan_id'     => $tabungan->id,
                        'tanggal'         => $today,
                        'nomor_transaksi' => $nomorService->generateNomorTransaksi(),
                        'jenis_transaksi' => 'tutup_periode',
                        'keterangan'      => 'Potongan Administrasi Periode (Massal)',
                        'nominal'         => 0,
                        'administrasi'    => $endapanWajib,
                        'saldo_setelah'   => $saldoBaru,
                    ]);

                    $processedCount++;
                }
            }
        });

        return redirect()->route('tabungan.tutup-buku-masal.index')->with('success', "Tutup buku massal berhasil diproses. Sebanyak {$processedCount} rekening tabungan telah dipotong biaya administrasi periode.");
    }

    public function mulaiBukuMasalStore(Request $request)
    {
        $request->validate([
            'jenis_tabungan' => 'required|in:reguler,sembako',
        ]);

        $jenis = $request->jenis_tabungan;
        $unitId = auth()->user()->unit_id;

        $nasabahQuery = \App\Models\Nasabah::query();
        if ($unitId) {
            $nasabahQuery->where('unit_id', $unitId);
        }

        $nasabahs = $nasabahQuery->get();
        $createdCount = 0;

        DB::transaction(function () use ($nasabahs, $jenis, &$createdCount) {
            foreach ($nasabahs as $nasabah) {
                $exists = $nasabah->tabungan()->where('jenis_tabungan', $jenis)->exists();
                if (!$exists) {
                    $nasabah->tabungan()->create([
                        'jenis_tabungan' => $jenis,
                        'saldo' => 0,
                    ]);

                    // Sync nasabah's kategori array
                    $kategori = $nasabah->kategori ?? [];
                    $searchVal = $jenis === 'reguler' ? 'tabungan' : 'sembako';
                    if (!in_array($searchVal, $kategori)) {
                        $kategori[] = $searchVal;
                        $nasabah->update(['kategori' => $kategori]);
                    }

                    $createdCount++;
                }
            }
        });

        return redirect()->route('tabungan.tutup-buku-masal.index')
            ->with('success', "Mulai Buku Massal berhasil diproses. Sebanyak {$createdCount} rekening tabungan " . ($jenis === 'reguler' ? 'Reguler' : 'Sembako') . " baru telah dibuka.");
    }
}

