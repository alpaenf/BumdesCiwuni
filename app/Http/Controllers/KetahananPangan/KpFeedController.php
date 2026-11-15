<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpExpense;
use App\Models\KetahananPangan\KpFeedPurchase;
use App\Models\KetahananPangan\KpFeedStock;
use App\Models\KetahananPangan\KpFeedUsage;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpFeedController extends Controller
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

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $activeTab = $request->input('tab', 'pemakaian'); // pemakaian or pembelian
        $feedStock = KpFeedStock::firstOrCreate(
            ['nama_pakan' => 'Pakan Layer'],
            ['stok_tersedia_kg' => 0, 'keterangan' => 'Stok Pakan Utama Ayam Petelur']
        );

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Usages
        $usages = KpFeedUsage::whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'usage_page')
            ->withQueryString();

        // Purchases
        $purchases = KpFeedPurchase::whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'purchase_page')
            ->withQueryString();

        // Aggregations
        $totalBeliKg = (float) KpFeedPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
        $totalBiayaBeli = (float) KpFeedPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('total_biaya');
        $totalPakaiKg = (float) KpFeedUsage::whereBetween('tanggal', [$startDate, $endDate])->sum('total_kebutuhan_kg');

        return Inertia::render('ketahanan pangan/Hewani/LogistikPakan', [
            'unit' => $unit,
            'user' => $user,
            'activeTab' => $activeTab,
            'feedStock' => $feedStock,
            'usages' => $usages,
            'purchases' => $purchases,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'tab' => $activeTab,
            ],
            'summary' => [
                'stok_saat_ini_kg' => (float) $feedStock->stok_tersedia_kg,
                'total_beli_kg' => $totalBeliKg,
                'total_biaya_beli' => $totalBiayaBeli,
                'total_pakai_kg' => $totalPakaiKg,
            ]
        ]);
    }

    public function storePurchase(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_pakan' => 'required|string|max:255',
            'jumlah_kg' => 'required|numeric|min:0.01',
            'harga_per_kg' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $totalBiaya = round($validated['jumlah_kg'] * $validated['harga_per_kg'], 2);

        DB::transaction(function () use ($validated, $totalBiaya) {
            $purchase = KpFeedPurchase::create([
                'tanggal' => $validated['tanggal'],
                'nama_pakan' => $validated['nama_pakan'],
                'jumlah_kg' => $validated['jumlah_kg'],
                'harga_per_kg' => $validated['harga_per_kg'],
                'total_biaya' => $totalBiaya,
                'supplier' => $validated['supplier'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Update stok pakan utama Layer
            $feedStock = KpFeedStock::firstOrCreate(
                ['nama_pakan' => 'Pakan Layer'],
                ['stok_tersedia_kg' => 0]
            );
            $feedStock->increment('stok_tersedia_kg', $validated['jumlah_kg']);

            // Auto-record ke Pengeluaran (Biaya Bahan Baku Pakan - Variabel)
            $expense = KpExpense::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => 'Pembelian pakan: ' . $validated['nama_pakan'] . ' (' . number_format($validated['jumlah_kg'], 2) . ' kg)',
                'jenis_biaya' => 'variabel',
                'kategori_biaya' => 'Biaya Bahan Baku Pakan',
                'nominal' => $totalBiaya,
                'referensi_tipe' => 'feed_purchase',
                'referensi_id' => $purchase->id,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Auto-record ke Arus Kas (Kredit)
            $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
            $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
            $newSaldo = $currentSaldo - $totalBiaya;

            KpCashFlow::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => 'Pembelian Pakan (' . $validated['nama_pakan'] . ')',
                'debit' => 0,
                'kredit' => $totalBiaya,
                'saldo_berjalan' => $newSaldo,
                'sumber_tipe' => 'feed_purchase',
                'sumber_id' => $purchase->id,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()->back()->with('success', 'Pembelian pakan berhasil dicatat & stok bertambah.');
    }

    public function storeUsage(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'hari' => 'nullable|string',
            'hari_ke' => 'nullable|integer',
            'umur_minggu_ke' => 'nullable|integer',
            'umur_minggu_hari_ke' => 'nullable|integer',
            'kebutuhan_per_ekor_gram' => 'required|numeric|min:0.1',
            'populasi_ayam' => 'required|integer|min:1',
            'harga_per_kg' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $feedStock = KpFeedStock::firstOrCreate(
            ['nama_pakan' => 'Pakan Layer'],
            ['stok_tersedia_kg' => 0]
        );

        // Rumus: (Jumlah ayam x kebutuhan/ekor) / 1000
        $totalKebutuhanKg = round(($validated['populasi_ayam'] * $validated['kebutuhan_per_ekor_gram']) / 1000, 2);
        $stokAwal = (float) $feedStock->stok_tersedia_kg;

        if ($stokAwal < $totalKebutuhanKg) {
            return redirect()->back()->withErrors([
                'total_kebutuhan_kg' => "Stok pakan tidak mencukupi! Tersedia: {$stokAwal} kg, Kebutuhan: {$totalKebutuhanKg} kg."
            ]);
        }

        $sisaPakan = $stokAwal - $totalKebutuhanKg;
        $hargaPerKg = (float) ($validated['harga_per_kg'] ?? 0);
        $totalBiaya = round($totalKebutuhanKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($validated, $stokAwal, $totalKebutuhanKg, $sisaPakan, $hargaPerKg, $totalBiaya, $hariName, $feedStock) {
            KpFeedUsage::create([
                'hari_ke' => $validated['hari_ke'] ?? null,
                'hari' => $hariName,
                'tanggal' => $validated['tanggal'],
                'umur_minggu_ke' => $validated['umur_minggu_ke'] ?? null,
                'umur_minggu_hari_ke' => $validated['umur_minggu_hari_ke'] ?? null,
                'stok_awal_kg' => $stokAwal,
                'kebutuhan_per_ekor_gram' => $validated['kebutuhan_per_ekor_gram'],
                'populasi_ayam' => $validated['populasi_ayam'],
                'total_kebutuhan_kg' => $totalKebutuhanKg,
                'sisa_pakan_kg' => $sisaPakan,
                'harga_per_kg' => $hargaPerKg,
                'total_biaya_pemakaian' => $totalBiaya,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Potong stok pakan
            $feedStock->decrement('stok_tersedia_kg', $totalKebutuhanKg);
        });

        return redirect()->back()->with('success', 'Pencatatan pemakaian pakan berhasil disimpan.');
    }

    public function destroyPurchase($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $purchase = KpFeedPurchase::findOrFail($id);

            // Rollback stok
            $feedStock = KpFeedStock::first();
            if ($feedStock) {
                $feedStock->decrement('stok_tersedia_kg', $purchase->jumlah_kg);
            }

            // Hapus referensi expense & cashflow
            KpExpense::where('referensi_tipe', 'feed_purchase')->where('referensi_id', $purchase->id)->delete();
            KpCashFlow::where('sumber_tipe', 'feed_purchase')->where('sumber_id', $purchase->id)->delete();

            $purchase->delete();
        });

        return redirect()->back()->with('success', 'Data pembelian pakan dibatalkan.');
    }

    public function destroyUsage($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $usage = KpFeedUsage::findOrFail($id);

            // Kembalikan stok
            $feedStock = KpFeedStock::first();
            if ($feedStock) {
                $feedStock->increment('stok_tersedia_kg', $usage->total_kebutuhan_kg);
            }

            $usage->delete();
        });

        return redirect()->back()->with('success', 'Catatan pemakaian pakan berhasil dihapus.');
    }
}

