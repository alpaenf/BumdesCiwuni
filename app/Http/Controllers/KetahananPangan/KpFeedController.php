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

    private function syncFeedStock(): float
    {
        $feedStock = KpFeedStock::firstOrCreate(
            ['nama_pakan' => 'Pakan Layer'],
            ['stok_tersedia_kg' => 0, 'keterangan' => 'Stok Pakan Utama Ayam Petelur']
        );

        $totalBeliAll = (float) KpFeedPurchase::sum('jumlah_kg');
        $totalPakaiAll = (float) KpFeedUsage::sum('total_kebutuhan_kg');
        $calculatedStock = max(0, round($totalBeliAll - $totalPakaiAll, 2));

        if ((float) $feedStock->stok_tersedia_kg !== (float) $calculatedStock) {
            $feedStock->stok_tersedia_kg = $calculatedStock;
            $feedStock->save();
        }

        // Sinkronkan juga varian nama pakan lainnya jika ada di tabel kp_feed_stocks
        KpFeedStock::where('nama_pakan', '!=', 'Pakan Layer')->update(['stok_tersedia_kg' => $calculatedStock]);

        return $calculatedStock;
    }

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $activeTab = $request->input('tab', 'pemakaian'); // pemakaian or pembelian
        $currentStock = $this->syncFeedStock();
        $feedStock = KpFeedStock::where('nama_pakan', 'Pakan Layer')->first();

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();

        $isAll = $request->boolean('all', false) || $request->input('filter_preset') === 'all';
        $filterPreset = $request->input('filter_preset', '');

        // Default startDate and endDate
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Jika tidak dispesifikasikan (default buka halaman pertama kali)
        if (!$isAll && (empty($startDate) || empty($endDate))) {
            if ($filterPreset === 'today') {
                $startDate = $today;
                $endDate = $today;
            } elseif ($filterPreset === 'this_month') {
                $startDate = $startOfMonth;
                $endDate = $today;
            } else {
                $hasDataThisMonth = KpFeedUsage::whereBetween('tanggal', [$startOfMonth, $today])->exists();
                if ($hasDataThisMonth) {
                    $startDate = $startOfMonth;
                    $endDate = $today;
                    $filterPreset = 'this_month';
                } else {
                    $isAll = true;
                    $filterPreset = 'all';
                    $startDate = '';
                    $endDate = '';
                }
            }
        }

        // Usages
        $usagesQuery = KpFeedUsage::query();
        if (!$isAll) {
            $usagesQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }
        $usages = $usagesQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'usage_page')
            ->withQueryString();

        // Purchases
        $purchasesQuery = KpFeedPurchase::query();
        if (!$isAll) {
            $purchasesQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }
        $purchases = $purchasesQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'purchase_page')
            ->withQueryString();

        // Aggregations
        if ($isAll) {
            $totalBeliKg = (float) KpFeedPurchase::sum('jumlah_kg');
            $totalBiayaBeli = (float) KpFeedPurchase::sum('total_biaya');
            $totalPakaiKg = (float) KpFeedUsage::sum('total_kebutuhan_kg');
        } else {
            $totalBeliKg = (float) KpFeedPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
            $totalBiayaBeli = (float) KpFeedPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('total_biaya');
            $totalPakaiKg = (float) KpFeedUsage::whereBetween('tanggal', [$startDate, $endDate])->sum('total_kebutuhan_kg');
        }

        // Pemakaian Hari Ini (riil per hari ini)
        $pemakaianHariIniKg = (float) KpFeedUsage::where('tanggal', $today)->sum('total_kebutuhan_kg');

        $latestUsage = KpFeedUsage::orderByDesc('tanggal')->orderByDesc('id')->first();
        $latestPurchase = KpFeedPurchase::orderByDesc('tanggal')->orderByDesc('id')->first();

        return Inertia::render('ketahanan pangan/Hewani/LogistikPakan', [
            'unit' => $unit,
            'user' => $user,
            'activeTab' => $activeTab,
            'feedStock' => $feedStock,
            'usages' => $usages,
            'purchases' => $purchases,
            'latestUsage' => $latestUsage,
            'latestPurchase' => $latestPurchase,
            'filters' => [
                'start_date' => $isAll ? '' : $startDate,
                'end_date' => $isAll ? '' : $endDate,
                'tab' => $activeTab,
                'is_all' => $isAll,
                'filter_preset' => $filterPreset,
                'today' => $today,
            ],
            'summary' => [
                'stok_saat_ini_kg' => (float) $currentStock,
                'total_beli_kg' => $totalBeliKg,
                'total_biaya_beli' => $totalBiayaBeli,
                'total_pakai_kg' => $totalPakaiKg,
                'pemakaian_hari_ini_kg' => $pemakaianHariIniKg,
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

            // Auto-record ke Pengeluaran (Biaya Bahan Baku Pakan - Variabel)
            KpExpense::create([
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

            $this->syncFeedStock();
            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pembelian pakan berhasil dicatat & stok bertambah.');
    }

    public function updatePurchase(Request $request, $id)
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

        $purchase = KpFeedPurchase::findOrFail($id);
        $totalBiaya = round($validated['jumlah_kg'] * $validated['harga_per_kg'], 2);

        DB::transaction(function () use ($purchase, $validated, $totalBiaya) {
            $purchase->update([
                'tanggal' => $validated['tanggal'],
                'nama_pakan' => $validated['nama_pakan'],
                'jumlah_kg' => $validated['jumlah_kg'],
                'harga_per_kg' => $validated['harga_per_kg'],
                'total_biaya' => $totalBiaya,
                'supplier' => $validated['supplier'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Update terkait di KpExpense
            $expense = KpExpense::where('referensi_tipe', 'feed_purchase')->where('referensi_id', $purchase->id)->first();
            if ($expense) {
                $expense->update([
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Pembelian pakan: ' . $validated['nama_pakan'] . ' (' . number_format($validated['jumlah_kg'], 2) . ' kg)',
                    'nominal' => $totalBiaya,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }

            // Update terkait di KpCashFlow
            $cashFlow = KpCashFlow::where('sumber_tipe', 'feed_purchase')->where('sumber_id', $purchase->id)->first();
            if ($cashFlow) {
                $cashFlow->update([
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Pembelian Pakan (' . $validated['nama_pakan'] . ')',
                    'kredit' => $totalBiaya,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }

            $this->syncFeedStock();
            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Data pembelian pakan berhasil diperbarui.');
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

        $stokAwal = $this->syncFeedStock();

        // Rumus: (Jumlah ayam x kebutuhan/ekor) / 1000
        $totalKebutuhanKg = round(($validated['populasi_ayam'] * $validated['kebutuhan_per_ekor_gram']) / 1000, 2);

        if ($stokAwal < $totalKebutuhanKg) {
            return redirect()->back()->withErrors([
                'total_kebutuhan_kg' => "Stok pakan tidak mencukupi! Tersedia: {$stokAwal} kg, Kebutuhan: {$totalKebutuhanKg} kg."
            ]);
        }

        $sisaPakan = max(0, round($stokAwal - $totalKebutuhanKg, 2));
        $hargaPerKg = (float) ($validated['harga_per_kg'] ?? 0);
        $totalBiaya = round($totalKebutuhanKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($validated, $stokAwal, $totalKebutuhanKg, $sisaPakan, $hargaPerKg, $totalBiaya, $hariName) {
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

            $this->syncFeedStock();
        });

        return redirect()->back()->with('success', 'Pencatatan pemakaian pakan berhasil disimpan.');
    }

    public function updateUsage(Request $request, $id)
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

        $usage = KpFeedUsage::findOrFail($id);

        $totalKebutuhanKg = round(($validated['populasi_ayam'] * $validated['kebutuhan_per_ekor_gram']) / 1000, 2);

        // Validasi ketersediaan stok selain pemakaian ini
        $totalPurchases = (float) KpFeedPurchase::sum('jumlah_kg');
        $otherUsages = (float) KpFeedUsage::where('id', '!=', $id)->sum('total_kebutuhan_kg');
        $availableForThis = max(0, round($totalPurchases - $otherUsages, 2));

        if ($totalKebutuhanKg > $availableForThis) {
            return redirect()->back()->withErrors([
                'total_kebutuhan_kg' => "Stok pakan tidak mencukupi untuk perubahan ini! Tersedia: {$availableForThis} kg, Kebutuhan baru: {$totalKebutuhanKg} kg."
            ]);
        }

        $sisaPakan = max(0, round($availableForThis - $totalKebutuhanKg, 2));
        $hargaPerKg = (float) ($validated['harga_per_kg'] ?? 0);
        $totalBiaya = round($totalKebutuhanKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($usage, $validated, $availableForThis, $totalKebutuhanKg, $sisaPakan, $hargaPerKg, $totalBiaya, $hariName) {
            $usage->update([
                'hari_ke' => $validated['hari_ke'] ?? null,
                'hari' => $hariName,
                'tanggal' => $validated['tanggal'],
                'umur_minggu_ke' => $validated['umur_minggu_ke'] ?? null,
                'umur_minggu_hari_ke' => $validated['umur_minggu_hari_ke'] ?? null,
                'stok_awal_kg' => $availableForThis,
                'kebutuhan_per_ekor_gram' => $validated['kebutuhan_per_ekor_gram'],
                'populasi_ayam' => $validated['populasi_ayam'],
                'total_kebutuhan_kg' => $totalKebutuhanKg,
                'sisa_pakan_kg' => $sisaPakan,
                'harga_per_kg' => $hargaPerKg,
                'total_biaya_pemakaian' => $totalBiaya,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $this->syncFeedStock();
        });

        return redirect()->back()->with('success', 'Catatan pemakaian pakan berhasil diperbarui.');
    }

    public function destroyPurchase($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $purchase = KpFeedPurchase::findOrFail($id);

            // Hapus referensi expense & cashflow
            KpExpense::where('referensi_tipe', 'feed_purchase')->where('referensi_id', $purchase->id)->delete();
            KpCashFlow::where('sumber_tipe', 'feed_purchase')->where('sumber_id', $purchase->id)->delete();

            $purchase->delete();

            // Auto sinkronisasi stok & kas
            $this->syncFeedStock();
            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Data pembelian pakan dibatalkan.');
    }

    public function destroyUsage($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $usage = KpFeedUsage::findOrFail($id);
            $usage->delete();

            // Auto sinkronisasi stok & kas
            $this->syncFeedStock();
            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Catatan pemakaian pakan berhasil dihapus.');
    }
}
