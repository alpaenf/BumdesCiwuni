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

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $currentStock = $this->syncEggStock();
        $product = KpProduct::where('kode', 'TELUR-BGS')->first();

        $productions = KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalProduksiKg = (float) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_kg');
        $totalProduksiButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_butir');
        $totalRetakButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_retak_butir');
        $totalRusakButir = (int) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('telur_rusak_butir');

        return Inertia::render('ketahanan pangan/Hewani/ProduksiTelur', [
            'unit' => $unit,
            'user' => $user,
            'product' => $product,
            'productions' => $productions,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'stok_saat_ini_kg' => (float) $currentStock,
                'total_produksi_kg' => $totalProduksiKg,
                'total_produksi_butir' => $totalProduksiButir,
                'total_retak_butir' => $totalRetakButir,
                'total_rusak_butir' => $totalRusakButir,
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
