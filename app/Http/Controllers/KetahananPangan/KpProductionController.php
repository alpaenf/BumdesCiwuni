<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpEggProduction;
use App\Models\KetahananPangan\KpEggSale;
use App\Models\KetahananPangan\KpProduct;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpProductionController extends Controller
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

    private function syncEggStock(): float
    {
        $product = KpProduct::firstOrCreate(
            ['kode' => 'TELUR-BGS'],
            [
                'nama' => 'Telur Ayam Ras Bagus/Cokelat',
                'kategori' => 'Telur Bagus',
                'satuan' => 'kg',
                'harga_default' => 22000,
                'stok_tersedia' => 0
            ]
        );

        $totalProduksiAll = (float) KpEggProduction::sum('produksi_kg');
        $totalTerjualKandangAll = (float) KpEggSale::sum('ambil_kandang_kg');
        $calculatedEggStock = max(0, round($totalProduksiAll - $totalTerjualKandangAll, 2));

        if ((float) $product->stok_tersedia !== (float) $calculatedEggStock) {
            $product->stok_tersedia = $calculatedEggStock;
            $product->save();
        }

        return $calculatedEggStock;
    }

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $startOfMonth = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateString();

        $isAll = $request->boolean('all', false) || $request->input('filter_preset') === 'all';
        $filterPreset = $request->input('filter_preset', '');

        // Default startDate and endDate
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Jika tidak dispesifikasikan (default buka halaman pertama kali):
        if (!$isAll && (empty($startDate) || empty($endDate))) {
            if ($filterPreset === 'today') {
                $startDate = $today;
                $endDate = $today;
            } elseif ($filterPreset === 'this_month') {
                $startDate = $startOfMonth;
                $endDate = $today;
            } else {
                $hasDataThisMonth = KpEggProduction::whereBetween('tanggal', [$startOfMonth, $today])->exists();
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

        $currentStock = $this->syncEggStock();
        $product = KpProduct::where('kode', 'TELUR-BGS')->first();

        $productionsQuery = KpEggProduction::query();
        if (!$isAll) {
            $productionsQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }
        $productions = $productionsQuery
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        if ($isAll) {
            $totalProduksiKg = (float) KpEggProduction::sum('produksi_kg');
            $totalProduksiButir = (int) KpEggProduction::sum('produksi_butir');
            $totalRetakButir = (int) KpEggProduction::sum('telur_retak_butir');
            $totalRusakButir = (int) KpEggProduction::sum('telur_rusak_butir');
            $totalEstimasiPendapatan = (float) KpEggProduction::sum('pendapatan_estimasi');
        } else {
            $totalProduksiKg = (float) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_kg');
            $totalProduksiButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_butir');
            $totalRetakButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_retak_butir');
            $totalRusakButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_rusak_butir');
            $totalEstimasiPendapatan = (float) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('pendapatan_estimasi');
        }

        // Produksi Hari Ini (riil per hari ini)
        $produksiHariIniKg = (float) KpEggProduction::where('tanggal', $today)->sum('produksi_kg');
        $produksiHariIniButir = (int) KpEggProduction::where('tanggal', $today)->sum('produksi_butir');

        $latestProduction = KpEggProduction::orderByDesc('tanggal')->orderByDesc('id')->first();

        return Inertia::render('ketahanan pangan/Hewani/ProduksiTelur', [
            'unit' => $unit,
            'user' => $user,
            'product' => $product,
            'productions' => $productions,
            'latestProduction' => $latestProduction,
            'filters' => [
                'start_date' => $isAll ? '' : $startDate,
                'end_date' => $isAll ? '' : $endDate,
                'is_all' => $isAll,
                'filter_preset' => $filterPreset,
                'today' => $today,
            ],
            'summary' => [
                'stok_saat_ini_kg' => (float) $currentStock,
                'total_produksi_kg' => $totalProduksiKg,
                'total_produksi_butir' => $totalProduksiButir,
                'total_retak_butir' => $totalRetakButir,
                'total_rusak_butir' => $totalRusakButir,
                'total_estimasi_pendapatan' => $totalEstimasiPendapatan,
                'produksi_hari_ini_kg' => $produksiHariIniKg,
                'produksi_hari_ini_butir' => $produksiHariIniButir,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'hari' => 'nullable|string',
            'umur_ke' => 'nullable|integer',
            'umur_minggu' => 'nullable|integer',
            'hari_ke' => 'nullable|integer',
            'produksi_butir' => 'nullable|integer|min:0',
            'butir_per_kg' => 'nullable|numeric|min:0.1',
            'produksi_kg' => 'required|numeric|min:0',
            'harga_per_kg' => 'nullable|numeric|min:0',
            'telur_retak_butir' => 'nullable|integer|min:0',
            'keterangan_retak' => 'nullable|string',
            'telur_rusak_butir' => 'nullable|integer|min:0',
            'keterangan_rusak' => 'nullable|string',
        ]);

        $stokSebelum = $this->syncEggStock();
        $produksiKg = (float) $validated['produksi_kg'];
        $sisaStok = $stokSebelum + $produksiKg;

        $product = KpProduct::where('kode', 'TELUR-BGS')->first();
        $hargaPerKg = (float) ($validated['harga_per_kg'] ?: ($product ? $product->harga_default : 22000));
        $estimasiPendapatan = round($produksiKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($validated, $stokSebelum, $produksiKg, $sisaStok, $hargaPerKg, $estimasiPendapatan, $hariName) {
            KpEggProduction::create([
                'umur_ke' => $validated['umur_ke'] ?? null,
                'hari' => $hariName,
                'tanggal' => $validated['tanggal'],
                'umur_minggu' => $validated['umur_minggu'] ?? null,
                'hari_ke' => $validated['hari_ke'] ?? null,
                'produksi_butir' => $validated['produksi_butir'] ?? 0,
                'butir_per_kg' => $validated['butir_per_kg'] ?? 16,
                'produksi_kg' => $produksiKg,
                'stok_sebelum_kg' => $stokSebelum,
                'terjual_kg' => 0,
                'sisa_stok_kg' => $sisaStok,
                'harga_per_kg' => $hargaPerKg,
                'pendapatan_estimasi' => $estimasiPendapatan,
                'telur_retak_butir' => $validated['telur_retak_butir'] ?? 0,
                'keterangan_retak' => $validated['keterangan_retak'] ?? null,
                'telur_rusak_butir' => $validated['telur_rusak_butir'] ?? 0,
                'keterangan_rusak' => $validated['keterangan_rusak'] ?? null,
            ]);

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', 'Produksi telur harian berhasil dicatat & stok bertambah.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'hari' => 'nullable|string',
            'umur_ke' => 'nullable|integer',
            'umur_minggu' => 'nullable|integer',
            'hari_ke' => 'nullable|integer',
            'produksi_butir' => 'nullable|integer|min:0',
            'butir_per_kg' => 'nullable|numeric|min:0.1',
            'produksi_kg' => 'required|numeric|min:0',
            'harga_per_kg' => 'nullable|numeric|min:0',
            'telur_retak_butir' => 'nullable|integer|min:0',
            'keterangan_retak' => 'nullable|string',
            'telur_rusak_butir' => 'nullable|integer|min:0',
            'keterangan_rusak' => 'nullable|string',
        ]);

        $prod = KpEggProduction::findOrFail($id);
        $produksiKg = (float) $validated['produksi_kg'];

        $product = KpProduct::where('kode', 'TELUR-BGS')->first();
        $hargaPerKg = (float) ($validated['harga_per_kg'] ?: ($product ? $product->harga_default : 22000));
        $estimasiPendapatan = round($produksiKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($prod, $validated, $produksiKg, $hargaPerKg, $estimasiPendapatan, $hariName) {
            $prod->update([
                'umur_ke' => $validated['umur_ke'] ?? null,
                'hari' => $hariName,
                'tanggal' => $validated['tanggal'],
                'umur_minggu' => $validated['umur_minggu'] ?? null,
                'hari_ke' => $validated['hari_ke'] ?? null,
                'produksi_butir' => $validated['produksi_butir'] ?? 0,
                'butir_per_kg' => $validated['butir_per_kg'] ?? 16,
                'produksi_kg' => $produksiKg,
                'harga_per_kg' => $hargaPerKg,
                'pendapatan_estimasi' => $estimasiPendapatan,
                'telur_retak_butir' => $validated['telur_retak_butir'] ?? 0,
                'keterangan_retak' => $validated['keterangan_retak'] ?? null,
                'telur_rusak_butir' => $validated['telur_rusak_butir'] ?? 0,
                'keterangan_rusak' => $validated['keterangan_rusak'] ?? null,
            ]);

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', 'Catatan produksi telur harian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $prod = KpEggProduction::findOrFail($id);
            $prod->delete();

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', 'Catatan produksi telur berhasil dihapus.');
    }
}
