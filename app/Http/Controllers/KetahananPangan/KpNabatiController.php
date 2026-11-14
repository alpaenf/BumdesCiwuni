<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpExpense;
use App\Models\KetahananPangan\KpPlantCommodity;
use App\Models\KetahananPangan\KpPlantProduction;
use App\Models\KetahananPangan\KpPlantSale;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpNabatiController extends Controller
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

    public function dashboard(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $commoditiesCount = KpPlantCommodity::count();
        $totalStokKg = (float) KpPlantCommodity::sum('stok_tersedia');
        $totalPanenKg = (float) KpPlantProduction::sum('jumlah_panen');
        $totalPenjualanRp = (float) KpPlantSale::sum('total_harga');
        $totalPengeluaranRp = (float) KpExpense::where('unit_kategori', 'nabati')->sum('nominal');
        $labaRugi = $totalPenjualanRp - $totalPengeluaranRp;

        $commodities = KpPlantCommodity::orderBy('nama')->get();
        $recentSales = KpPlantSale::with('commodity')->orderByDesc('tanggal')->take(5)->get();

        return Inertia::render('ketahanan pangan/Nabati/DashboardNabati', [
            'unit' => $unit,
            'user' => $user,
            'stats' => [
                'commodities_count' => $commoditiesCount,
                'total_stok_kg' => $totalStokKg,
                'total_panen_kg' => $totalPanenKg,
                'total_penjualan_rp' => $totalPenjualanRp,
                'total_pengeluaran_rp' => $totalPengeluaranRp,
                'laba_rugi' => $labaRugi,
            ],
            'commodities' => $commodities,
            'recentSales' => $recentSales,
        ]);
    }

    // Komoditas
    public function komoditas(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $commodities = KpPlantCommodity::orderBy('nama')->paginate(15);

        return Inertia::render('ketahanan pangan/Nabati/Komoditas', [
            'unit' => $unit,
            'user' => $user,
            'commodities' => $commodities,
        ]);
    }

    public function storeKomoditas(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'kode' => 'required|string|unique:kp_plant_commodities,kode|max:50',
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:20',
            'harga_default' => 'nullable|numeric|min:0',
            'stok_tersedia' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        KpPlantCommodity::create($validated);

        return redirect()->back()->with('success', 'Komoditas berhasil ditambahkan.');
    }

    // Produksi / Panen
    public function produksi(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $productions = KpPlantProduction::with('commodity')->orderByDesc('tanggal')->orderByDesc('id')->paginate(15);
        $commodities = KpPlantCommodity::orderBy('nama')->get();

        return Inertia::render('ketahanan pangan/Nabati/ProduksiPanen', [
            'unit' => $unit,
            'user' => $user,
            'productions' => $productions,
            'commodities' => $commodities,
        ]);
    }

    public function storeProduksi(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'commodity_id' => 'required|exists:kp_plant_commodities,id',
            'jumlah_panen' => 'required|numeric|min:0.01',
            'satuan' => 'required|string|max:20',
            'lokasi_lahan' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            KpPlantProduction::create($validated);

            $comm = KpPlantCommodity::findOrFail($validated['commodity_id']);
            $comm->increment('stok_tersedia', $validated['jumlah_panen']);
        });

        return redirect()->back()->with('success', 'Hasil panen berhasil dicatat & stok bertambah.');
    }

    // Penjualan Nabati
    public function penjualan(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $sales = KpPlantSale::with('commodity')->orderByDesc('tanggal')->orderByDesc('id')->paginate(15);
        $commodities = KpPlantCommodity::orderBy('nama')->get();

        return Inertia::render('ketahanan pangan/Nabati/PenjualanNabati', [
            'unit' => $unit,
            'user' => $user,
            'sales' => $sales,
            'commodities' => $commodities,
        ]);
    }

    public function storePenjualan(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pelanggan' => 'required|string|max:255',
            'commodity_id' => 'required|exists:kp_plant_commodities,id',
            'jumlah' => 'required|numeric|min:0.01',
            'satuan' => 'required|string|max:20',
            'harga_satuan' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,Qris,Hutang',
            'keterangan' => 'nullable|string',
        ]);

        $comm = KpPlantCommodity::findOrFail($validated['commodity_id']);
        if ($comm->stok_tersedia < $validated['jumlah']) {
            return redirect()->back()->withErrors([
                'jumlah' => "Stok tidak mencukupi! Tersedia: {$comm->stok_tersedia} {$comm->satuan}."
            ]);
        }

        $totalHarga = round($validated['jumlah'] * $validated['harga_satuan'], 2);
        $status = ($validated['jumlah_pembayaran'] >= $totalHarga && $validated['metode_pembayaran'] !== 'Hutang') ? 'Lunas' : 'Belum Lunas';

        $nomorTransaksi = 'STR-NBT-' . Carbon::parse($validated['tanggal'])->format('ymd') . '-' . str_pad(KpPlantSale::where('tanggal', $validated['tanggal'])->count() + 1, 3, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($validated, $comm, $totalHarga, $status, $nomorTransaksi) {
            $sale = KpPlantSale::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'commodity_id' => $validated['commodity_id'],
                'jumlah' => $validated['jumlah'],
                'satuan' => $validated['satuan'],
                'harga_satuan' => $validated['harga_satuan'],
                'total_harga' => $totalHarga,
                'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $status,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $comm->decrement('stok_tersedia', $validated['jumlah']);

            if ($validated['metode_pembayaran'] !== 'Hutang' && $validated['jumlah_pembayaran'] > 0) {
                $pemasukan = min($totalHarga, $validated['jumlah_pembayaran']);
                $lastCash = KpCashFlow::where('unit_kategori', 'nabati')->orderByDesc('id')->first();
                $saldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
                $newSaldo = $saldo + $pemasukan;

                KpCashFlow::create([
                    'unit_kategori' => 'nabati',
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Penjualan ' . $comm->nama . ' (' . $sale->nama_pelanggan . ')',
                    'debit' => $pemasukan,
                    'kredit' => 0,
                    'saldo_berjalan' => $newSaldo,
                    'sumber_tipe' => 'plant_sale',
                    'sumber_id' => $sale->id,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Penjualan komoditas nabati berhasil dicatat.');
    }
}

