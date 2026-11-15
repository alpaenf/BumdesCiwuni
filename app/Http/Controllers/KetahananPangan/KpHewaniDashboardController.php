<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpEggProduction;
use App\Models\KetahananPangan\KpEggSale;
use App\Models\KetahananPangan\KpExpense;
use App\Models\KetahananPangan\KpFeedPurchase;
use App\Models\KetahananPangan\KpFeedStock;
use App\Models\KetahananPangan\KpFeedUsage;
use App\Models\KetahananPangan\KpOtherIncome;
use App\Models\KetahananPangan\KpProduct;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpPartnerPurchase;
use App\Models\LandingPageSetting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class KpHewaniDashboardController extends Controller
{
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'ketahanan-pangan')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'manager_pusat' && $user->unit_id != $unit->id) {
            abort(403, 'Akses ditolak ke Unit Ketahanan Pangan.');
        }
        return $unit;
    }

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. Populasi Ayam terbaru (fallback dari setting jika belum ada pemakaian)
        $latestFeed = KpFeedUsage::orderByDesc('tanggal')->orderByDesc('id')->first();
        $savedPopulasi = LandingPageSetting::getByKey('kp_populasi_ayam', '');
        if ($savedPopulasi !== '') {
            $populasiAyam = (int) $savedPopulasi;
        } else {
            $populasiAyam = $latestFeed ? $latestFeed->populasi_ayam : 1000;
        }

        // 2. Stok Pakan Tersedia
        $feedStock = KpFeedStock::first();
        $stokPakanKg = $feedStock ? (float) $feedStock->stok_tersedia_kg : 0;

        // 3. Stok Telur Bagus
        $eggProduct = KpProduct::where('kode', 'TELUR-BGS')->first();
        $stokTelurKg = $eggProduct ? (float) $eggProduct->stok_tersedia : 0;

        // 4. Agregasi dalam rentang filter
        $totalProduksiKg = (float) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_kg');
        $totalTelurRetak = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_retak_butir');
        $totalTelurRusak = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_rusak_butir');

        $totalPenjualanKg = (float) KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
        $pendapatanPenjualan = (float) KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('total_harga');
        $pendapatanLain = (float) KpOtherIncome::where('unit_kategori', 'hewani')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');
        $totalPendapatan = $pendapatanPenjualan + $pendapatanLain;

        $totalPengeluaran = (float) KpExpense::where('unit_kategori', 'hewani')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');
        $labaBersih = $totalPendapatan - $totalPengeluaran;

        // Saldo Kas terakhir
        $latestCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
        $saldoKas = $latestCash ? (float) $latestCash->saldo_berjalan : 0;

        // Data Rekanan
        $totalBeliRekananPeriodeKg = (float) KpPartnerPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
        $totalBiayaRekananPeriode = (float) KpPartnerPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('total_biaya');
        $totalBeliRekananAll = (float) KpPartnerPurchase::sum('jumlah_kg');
        $terpakaiRekananAll = (float) KpEggSale::sum('ambil_rekanan_kg');
        $sisaStokRekanan = max(0, $totalBeliRekananAll - $terpakaiRekananAll);

        // 5. Tren Harian (14 Hari Terakhir)
        $chartDates = collect(range(13, 0))->map(fn($days) => Carbon::now()->subDays($days)->toDateString());
        
        // Data Grafik Harian Produksi & Penjualan
        $dailyTrends = $chartDates->map(function ($date) {
            $prod = KpEggProduction::where('tanggal', $date)->first();
            $salesKg = (float) KpEggSale::where('tanggal', $date)->sum('jumlah_kg');
            $salesRp = (float) KpEggSale::where('tanggal', $date)->sum('total_harga');
            $partnerKg = (float) KpPartnerPurchase::where('tanggal', $date)->sum('jumlah_kg');

            return [
                'tanggal_raw' => $date,
                'tanggal' => Carbon::parse($date)->format('d M'),
                'produksi_kg' => $prod ? (float) $prod->produksi_kg : 0,
                'retak' => $prod ? (int) $prod->telur_retak_butir : 0,
                'rusak' => $prod ? (int) $prod->telur_rusak_butir : 0,
                'penjualan_kg' => $salesKg,
                'penjualan_rp' => $salesRp,
                'rekanan_kg' => $partnerKg,
            ];
        });

        // Recent Sales
        $recentSales = KpEggSale::orderByDesc('tanggal')->orderByDesc('id')->take(5)->get();

        return Inertia::render('ketahanan pangan/Hewani/DashboardHewani', [
            'unit' => $unit,
            'user' => $user,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'stats' => [
                'populasi_ayam' => $populasiAyam,
                'stok_pakan_kg' => $stokPakanKg,
                'stok_telur_kg' => $stokTelurKg,
                'total_produksi_kg' => $totalProduksiKg,
                'total_telur_retak' => $totalTelurRetak,
                'total_telur_rusak' => $totalTelurRusak,
                'total_penjualan_kg' => $totalPenjualanKg,
                'total_pendapatan' => $totalPendapatan,
                'pendapatan_penjualan' => $pendapatanPenjualan,
                'pendapatan_lain' => $pendapatanLain,
                'total_pengeluaran' => $totalPengeluaran,
                'laba_bersih' => $labaBersih,
                'saldo_kas' => $saldoKas,
                'total_beli_rekanan_kg' => $totalBeliRekananPeriodeKg,
                'total_biaya_rekanan' => $totalBiayaRekananPeriode,
                'sisa_stok_rekanan_kg' => $sisaStokRekanan,
                'total_stok_gabungan_kg' => $stokTelurKg + $sisaStokRekanan,
            ],
            'chartData' => $dailyTrends,
            'recentSales' => $recentSales,
        ]);
    }

    public function updatePopulasi(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'populasi_ayam' => 'required|integer|min:1',
        ]);

        LandingPageSetting::setByKey('kp_populasi_ayam', (string) $validated['populasi_ayam']);

        return redirect()->back()->with('success', 'Populasi ayam berhasil diperbarui.');
    }
}

