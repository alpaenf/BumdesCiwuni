<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpPartnerPurchase;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpExpense;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpPartnerPurchaseController extends Controller
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
        $search = $request->input('search', '');

        $query = KpPartnerPurchase::whereBetween('tanggal', [$startDate, $endDate]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_rekanan', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $purchases = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(15)->withQueryString();

        $totalKg = (float) KpPartnerPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
        $totalBiaya = (float) KpPartnerPurchase::whereBetween('tanggal', [$startDate, $endDate])->sum('total_biaya');

        return Inertia::render('ketahanan pangan/Hewani/PembelianRekanan', [
            'unit' => $unit,
            'user' => $user,
            'purchases' => $purchases,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search,
            ],
            'summary' => [
                'total_kg' => $totalKg,
                'total_biaya' => $totalBiaya,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama_rekanan' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'jumlah_kg' => 'required|numeric|min:0.1',
            'harga_per_kg' => 'required|numeric|min:0',
            'status_pembayaran' => 'required|in:Lunas,Hutang / Belum Lunas',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,QRIS',
            'keterangan' => 'nullable|string',
        ]);

        $totalBiaya = round($validated['jumlah_kg'] * $validated['harga_per_kg'], 2);
        $validated['total_biaya'] = $totalBiaya;

        DB::transaction(function () use ($validated, $totalBiaya) {
            $purchase = KpPartnerPurchase::create($validated);

            // Jika statusnya Lunas, catat pengeluaran & arus kas
            if ($validated['status_pembayaran'] === 'Lunas') {
                KpExpense::create([
                    'unit_kategori' => 'hewani',
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Pembelian Telur Rekanan: ' . $validated['nama_rekanan'] . ' (' . number_format($validated['jumlah_kg'], 1) . ' kg)',
                    'jenis_biaya' => 'variabel',
                    'kategori_biaya' => 'Biaya Pembelian Telur Rekanan',
                    'nominal' => $totalBiaya,
                    'referensi_tipe' => 'partner_purchase',
                    'referensi_id' => $purchase->id,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
                $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
                $newSaldo = $currentSaldo - $totalBiaya;

                KpCashFlow::create([
                    'unit_kategori' => 'hewani',
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Pembelian Rekanan Luar (' . $validated['nama_rekanan'] . ')',
                    'debit' => 0,
                    'kredit' => $totalBiaya,
                    'saldo_berjalan' => $newSaldo,
                    'sumber_tipe' => 'partner_purchase',
                    'sumber_id' => $purchase->id,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Riwayat pembelian telur rekanan berhasil disimpan.');
    }

    public function destroy($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $purchase = KpPartnerPurchase::findOrFail($id);

            // Hapus pencatatan expense & cash flow terkait
            KpExpense::where('referensi_tipe', 'partner_purchase')->where('referensi_id', $purchase->id)->delete();
            KpCashFlow::where('sumber_tipe', 'partner_purchase')->where('sumber_id', $purchase->id)->delete();

            $purchase->delete();
        });

        return redirect()->back()->with('success', 'Data pembelian rekanan berhasil dihapus.');
    }
}
