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

    public function tutupBukuMasal(Request $request)
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

        return back()->with('success', "Tutup buku massal berhasil diproses. Sebanyak {$processedCount} rekening tabungan telah dipotong biaya administrasi periode.");
    }
}

