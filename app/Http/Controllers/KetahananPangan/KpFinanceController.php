<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpExpense;
use App\Models\KetahananPangan\KpOtherIncome;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpFinanceController extends Controller
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

    // List Pengeluaran
    public function pengeluaran(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $jenisBiaya = $request->input('jenis_biaya', '');

        $query = KpExpense::where('unit_kategori', 'hewani')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($jenisBiaya) {
            $query->where('jenis_biaya', $jenisBiaya);
        }

        $expenses = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(15)->withQueryString();

        $totalTetap = (float) KpExpense::where('unit_kategori', 'hewani')->where('jenis_biaya', 'tetap')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');
        $totalVariabel = (float) KpExpense::where('unit_kategori', 'hewani')->where('jenis_biaya', 'variabel')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');

        return Inertia::render('ketahanan pangan/Hewani/Pengeluaran', [
            'unit' => $unit,
            'user' => $user,
            'expenses' => $expenses,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'jenis_biaya' => $jenisBiaya,
            ],
            'summary' => [
                'total_tetap' => $totalTetap,
                'total_variabel' => $totalVariabel,
                'total_pengeluaran' => $totalTetap + $totalVariabel,
            ],
        ]);
    }

    public function storePengeluaran(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string|max:255',
            'jenis_biaya' => 'required|in:tetap,variabel',
            'kategori_biaya' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $expense = KpExpense::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'],
                'jenis_biaya' => $validated['jenis_biaya'],
                'kategori_biaya' => $validated['kategori_biaya'],
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Auto-record ke Arus Kas
            $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
            $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
            $newSaldo = $currentSaldo - $validated['nominal'];

            KpCashFlow::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'] . ' (' . $validated['kategori_biaya'] . ')',
                'debit' => 0,
                'kredit' => $validated['nominal'],
                'saldo_berjalan' => $newSaldo,
                'sumber_tipe' => 'expense',
                'sumber_id' => $expense->id,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function updatePengeluaran(Request $request, $id)
    {
        $this->authorizeUnit();

        $expense = KpExpense::findOrFail($id);

        if ($expense->referensi_tipe) {
            return redirect()->back()->withErrors([
                'uraian' => 'Pengeluaran otomatis sistem tidak dapat diubah di sini. Silakan ubah langsung pada menu transaksi aslinya.'
            ]);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string|max:255',
            'jenis_biaya' => 'required|in:tetap,variabel',
            'kategori_biaya' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($expense, $validated) {
            $expense->update([
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'],
                'jenis_biaya' => $validated['jenis_biaya'],
                'kategori_biaya' => $validated['kategori_biaya'],
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $cash = KpCashFlow::where('sumber_tipe', 'expense')->where('sumber_id', $expense->id)->first();
            if ($cash) {
                $cash->update([
                    'tanggal' => $validated['tanggal'],
                    'uraian' => $validated['uraian'] . ' (' . $validated['kategori_biaya'] . ')',
                    'kredit' => $validated['nominal'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroyPengeluaran($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $expense = KpExpense::findOrFail($id);
            KpCashFlow::where('sumber_tipe', 'expense')->where('sumber_id', $expense->id)->delete();
            $expense->delete();

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    // List Pendapatan (Penjualan + Lain-lain)
    public function pendapatan(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $otherIncomes = KpOtherIncome::where('unit_kategori', 'hewani')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $penjualanRp = (float) \App\Models\KetahananPangan\KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('total_harga');
        $lainRp = (float) KpOtherIncome::where('unit_kategori', 'hewani')->whereBetween('tanggal', [$startDate, $endDate])->sum('nominal');

        return Inertia::render('ketahanan pangan/Hewani/Pendapatan', [
            'unit' => $unit,
            'user' => $user,
            'otherIncomes' => $otherIncomes,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'penjualan_rp' => $penjualanRp,
                'pendapatan_lain_rp' => $lainRp,
                'total_pendapatan_rp' => $penjualanRp + $lainRp,
            ],
        ]);
    }

    public function storePendapatanLain(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $income = KpOtherIncome::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'],
                'kategori' => $validated['kategori'] ?? 'Pendapatan Lain-Lain',
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Auto-record ke Arus Kas
            $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
            $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
            $newSaldo = $currentSaldo + $validated['nominal'];

            KpCashFlow::create([
                'unit_kategori' => 'hewani',
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'] . ' (' . ($validated['kategori'] ?? 'Pendapatan Lain') . ')',
                'debit' => $validated['nominal'],
                'kredit' => 0,
                'saldo_berjalan' => $newSaldo,
                'sumber_tipe' => 'other_income',
                'sumber_id' => $income->id,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pendapatan lain-lain berhasil dicatat.');
    }

    public function updatePendapatanLain(Request $request, $id)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'uraian' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $income = KpOtherIncome::findOrFail($id);

        DB::transaction(function () use ($income, $validated) {
            $income->update([
                'tanggal' => $validated['tanggal'],
                'uraian' => $validated['uraian'],
                'kategori' => $validated['kategori'] ?? 'Pendapatan Lain-Lain',
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $cash = KpCashFlow::where('sumber_tipe', 'other_income')->where('sumber_id', $income->id)->first();
            if ($cash) {
                $cash->update([
                    'tanggal' => $validated['tanggal'],
                    'uraian' => $validated['uraian'] . ' (' . ($validated['kategori'] ?? 'Pendapatan Lain') . ')',
                    'debit' => $validated['nominal'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pendapatan lain-lain berhasil diperbarui.');
    }

    public function destroyPendapatanLain($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $income = KpOtherIncome::findOrFail($id);
            KpCashFlow::where('sumber_tipe', 'other_income')->where('sumber_id', $income->id)->delete();
            $income->delete();

            KpCashFlow::syncAndRecalculate('hewani');
        });

        return redirect()->back()->with('success', 'Pendapatan lain-lain berhasil dihapus.');
    }
}
