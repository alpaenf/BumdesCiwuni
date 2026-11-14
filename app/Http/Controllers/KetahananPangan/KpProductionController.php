<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpEggProduction;
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

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Master produk telur
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

        $productions = KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalProduksiKg = (float) KpEggProduction::whereBetween('tanggal', [$startDate, $endDate])->sum('produksi_kg');
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
                'stok_saat_ini_kg' => (float) $product->stok_tersedia,
                'total_produksi_kg' => $totalProduksiKg,
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
            'produksi_kg' => 'required|numeric|min:0',
            'harga_per_kg' => 'nullable|numeric|min:0',
            'telur_retak_butir' => 'nullable|integer|min:0',
            'keterangan_retak' => 'nullable|string',
            'telur_rusak_butir' => 'nullable|integer|min:0',
            'keterangan_rusak' => 'nullable|string',
        ]);

        $product = KpProduct::firstOrCreate(
            ['kode' => 'TELUR-BGS'],
            ['nama' => 'Telur Ayam Ras Bagus/Cokelat', 'kategori' => 'Telur Bagus', 'satuan' => 'kg', 'harga_default' => 22000, 'stok_tersedia' => 0]
        );

        $stokSebelum = (float) $product->stok_tersedia;
        $produksiKg = (float) $validated['produksi_kg'];
        $sisaStok = $stokSebelum + $produksiKg; // Belum terjual saat input produksi harian

        $hargaPerKg = (float) ($validated['harga_per_kg'] ?: $product->harga_default);
        $estimasiPendapatan = round($produksiKg * $hargaPerKg, 2);

        $carbonDate = Carbon::parse($validated['tanggal']);
        $hariName = $validated['hari'] ?: $carbonDate->locale('id')->isoFormat('dddd');

        DB::transaction(function () use ($validated, $product, $stokSebelum, $produksiKg, $sisaStok, $hargaPerKg, $estimasiPendapatan, $hariName) {
            KpEggProduction::create([
                'umur_ke' => $validated['umur_ke'] ?? null,
                'hari' => $hariName,
                'tanggal' => $validated['tanggal'],
                'umur_minggu' => $validated['umur_minggu'] ?? null,
                'hari_ke' => $validated['hari_ke'] ?? null,
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

            // Tambah stok telur bagus
            $product->increment('stok_tersedia', $produksiKg);
        });

        return redirect()->back()->with('success', 'Produksi telur harian berhasil dicatat & stok bertambah.');
    }

    public function destroy($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $prod = KpEggProduction::findOrFail($id);
            $product = KpProduct::where('kode', 'TELUR-BGS')->first();
            if ($product) {
                $product->decrement('stok_tersedia', $prod->produksi_kg);
            }
            $prod->delete();
        });

        return redirect()->back()->with('success', 'Catatan produksi telur berhasil dihapus.');
    }
}

