<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpEggProduction;
use App\Models\KetahananPangan\KpEggSale;
use App\Models\KetahananPangan\KpExpense;
use App\Models\KetahananPangan\KpOtherIncome;
use App\Models\LandingPageSetting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class KpReportController extends Controller
{
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'ketahanan-pangan')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'manager_pusat' && $user->unit_id != $unit->id) {
            abort(403, 'Akses ditolak.');
        }
        return $unit;
    }

    // 1. Laporan Produksi Telur
    public function produksi(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $productions = KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $summary = [
            'total_produksi_kg' => (float) $productions->sum('produksi_kg'),
            'total_retak_butir' => (int) $productions->sum('telur_retak_butir'),
            'total_rusak_butir' => (int) $productions->sum('telur_rusak_butir'),
        ];

        return Inertia::render('ketahanan pangan/Hewani/LaporanProduksi', [
            'unit' => $unit,
            'user' => $user,
            'productions' => $productions,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => $summary,
        ]);
    }

    // 2. Laporan Penjualan Telur
    public function penjualan(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $customerType = $request->input('customer_type', '');

        $query = KpEggSale::with('customer')->whereBetween('tanggal', [$startDate, $endDate]);

        if ($customerType) {
            $query->where('customer_type', $customerType);
        }

        $sales = $query->orderBy('tanggal')->get();

        $summary = [
            'total_kg' => (float) $sales->sum('jumlah_kg'),
            'total_rp' => (float) $sales->sum('total_harga'),
            'total_transaksi' => $sales->count(),
            'total_eceran_rp' => (float) $sales->where('customer_type', 'Eceran')->sum('total_harga'),
            'total_agen_rp' => (float) $sales->where('customer_type', 'Agen')->sum('total_harga'),
        ];

        return Inertia::render('ketahanan pangan/Hewani/LaporanPenjualan', [
            'unit' => $unit,
            'user' => $user,
            'sales' => $sales,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'customer_type' => $customerType,
            ],
            'summary' => $summary,
        ]);
    }

    // 3. Laporan Arus Kas
    public function arusKas(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Sinkronisasi dan rekalkulasi otomatis
        KpCashFlow::syncAndRecalculate('hewani');

        $modalAwalSetting = (float) LandingPageSetting::getByKey('kp_hewani_modal_awal', '0');

        // Saldo awal sebelum periode
        $debitAwal = (float) KpCashFlow::where('unit_kategori', 'hewani')->where('tanggal', '<', $startDate)->sum('debit');
        $kreditAwal = (float) KpCashFlow::where('unit_kategori', 'hewani')->where('tanggal', '<', $startDate)->sum('kredit');
        $saldoAwal = max(0, $modalAwalSetting + $debitAwal - $kreditAwal);

        $flows = KpCashFlow::where('unit_kategori', 'hewani')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        // Rekalkulasi running saldo
        $running = $saldoAwal;
        $flowItems = $flows->map(function ($item) use (&$running) {
            $running = $running + (float) $item->debit - (float) $item->kredit;
            $item->saldo_berjalan = max(0, $running);
            return $item;
        });

        $totalDebit = (float) $flows->sum('debit');
        $totalKredit = (float) $flows->sum('kredit');
        $saldoAkhir = max(0, $saldoAwal + $totalDebit - $totalKredit);

        return Inertia::render('ketahanan pangan/Hewani/LaporanArusKas', [
            'unit' => $unit,
            'user' => $user,
            'flows' => $flowItems,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'saldo_awal' => $saldoAwal,
                'total_debit' => $totalDebit,
                'total_kredit' => $totalKredit,
                'saldo_akhir' => $saldoAkhir,
                'modal_awal' => $modalAwalSetting,
            ],
        ]);
    }

    // 4. Laporan Laba Rugi
    public function labaRugi(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $bulan = (int) $request->input('bulan', Carbon::now()->month);
        $tahun = (int) $request->input('tahun', Carbon::now()->year);

        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        // Pendapatan
        $hasilPenjualan = (float) KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('total_harga');
        $pendapatanLain = (float) KpOtherIncome::where('unit_kategori', 'hewani')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');
        $totalPendapatan = $hasilPenjualan + $pendapatanLain;

        // Biaya Tetap
        $fixedItems = [
            'Biaya Sewa' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Sewa')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Pajak' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Pajak')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Tenaga Kerja' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Tenaga Kerja')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Penyusutan Bibit Ayam' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Penyusutan Bibit Ayam')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
        ];
        $totalBiayaTetap = array_sum($fixedItems);

        // Biaya Variabel
        $variableItems = [
            'Biaya Bahan Baku Pakan' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Bahan Baku Pakan')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Vaksin dan Obat' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Vaksin dan Obat')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Operasional' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Operasional')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Promosi Penjualan' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Promosi Penjualan')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
            'Biaya Lain-Lain' => (float) KpExpense::where('unit_kategori', 'hewani')->where('kategori_biaya', 'Biaya Lain-Lain')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal'),
        ];
        $totalBiayaVariabel = array_sum($variableItems);

        $totalPengeluaran = $totalBiayaTetap + $totalBiayaVariabel;
        $labaRugi = $totalPendapatan - $totalPengeluaran;

        return Inertia::render('ketahanan pangan/Hewani/LaporanLabaRugi', [
            'unit' => $unit,
            'user' => $user,
            'filters' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            'report' => [
                'pendapatan' => [
                    'hasil_penjualan' => $hasilPenjualan,
                    'pendapatan_lain' => $pendapatanLain,
                    'total' => $totalPendapatan,
                ],
                'biaya_tetap' => [
                    'items' => $fixedItems,
                    'total' => $totalBiayaTetap,
                ],
                'biaya_variabel' => [
                    'items' => $variableItems,
                    'total' => $totalBiayaVariabel,
                ],
                'total_pengeluaran' => $totalPengeluaran,
                'laba_rugi' => $labaRugi,
                'is_laba' => $labaRugi >= 0,
            ],
        ]);
    }
}

