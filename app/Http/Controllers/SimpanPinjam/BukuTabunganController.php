<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\Nasabah;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BukuTabunganController extends Controller
{
    public function index(Request $request): Response
    {
        $transactions = $this->queryTransactions($request)->get();
        [$nasabah, $tabungan] = $this->resolveNasabahTabungan($request);
        $summary = $this->summaryFromTransactions($transactions, $tabungan);

        return Inertia::render('SimpanPinjam/BukuTabungan/Index', [
            'filters' => $request->only([
                'search',
                'nasabah_id',
                'nomor_rekening',
                'start_date',
                'end_date',
                'jenis_tabungan',
            ]),
            'nasabahOptions' => Nasabah::orderBy('nama')
                ->get(['id', 'nama', 'nomor_rekening', 'nomor_registrasi']),
            'nasabah' => $nasabah,
            'tabungan' => $tabungan,
            'summary' => $summary,
            'transactions' => $transactions->map(fn (TransaksiTabungan $transaksi) => [
                'id' => $transaksi->id,
                'tanggal' => $transaksi->tanggal->format('Y-m-d'),
                'nomor_transaksi' => $transaksi->nomor_transaksi,
                'keterangan' => $transaksi->keterangan ?: ($transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETOR ? 'Setoran' : 'Penarikan'),
                'setoran' => $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETOR ? $transaksi->nominal : 0,
                'penarikan' => $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_TARIK_TUNAI ? $transaksi->nominal : 0,
                'administrasi' => $transaksi->administrasi,
                'saldo' => $transaksi->saldo_setelah,
            ]),
        ]);
    }

    public function print(Request $request)
    {
        $transactions = $this->queryTransactions($request)->get();
        [$nasabah, $tabungan] = $this->resolveNasabahTabungan($request);
        $summary = $this->summaryFromTransactions($transactions, $tabungan);

        return view('exports.simpan-pinjam.buku-tabungan', [
            'nasabah' => $nasabah,
            'tabungan' => $tabungan,
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }

    public function pdf(Request $request)
    {
        return $this->print($request);
    }

    public function excel(Request $request)
    {
        $transactions = $this->queryTransactions($request)->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Tanggal',
                'Nomor Transaksi',
                'Keterangan',
                'Setoran',
                'Penarikan',
                'Administrasi',
                'Saldo',
            ]);

            foreach ($transactions as $transaksi) {
                fputcsv($handle, [
                    $transaksi->tanggal->format('Y-m-d'),
                    $transaksi->nomor_transaksi,
                    $transaksi->keterangan ?: ($transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETOR ? 'Setoran' : 'Penarikan'),
                    $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_SETOR ? $transaksi->nominal : 0,
                    $transaksi->jenis_transaksi === TransaksiTabungan::JENIS_TARIK_TUNAI ? $transaksi->nominal : 0,
                    $transaksi->administrasi,
                    $transaksi->saldo_setelah,
                ]);
            }

            fclose($handle);
        }, 'buku-tabungan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function queryTransactions(Request $request): Builder
    {
        $query = TransaksiTabungan::query()
            ->with(['tabungan.nasabah'])
            ->orderBy('tanggal');

        if ($request->filled('nasabah_id')) {
            $query->whereHas('tabungan', fn (Builder $builder) => $builder->where('nasabah_id', $request->nasabah_id));
        }

        if ($request->filled('nomor_rekening')) {
            $query->whereHas('tabungan.nasabah', fn (Builder $builder) => $builder->where('nomor_rekening', $request->nomor_rekening));
        }

        if ($request->filled('jenis_tabungan')) {
            // removed
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->whereHas('tabungan.nasabah', function (Builder $builder) use ($request) {
                $search = '%'.$request->search.'%';

                $builder->where('nama', 'like', $search)
                    ->orWhere('nomor_rekening', 'like', $search)
                    ->orWhere('nomor_registrasi', 'like', $search);
            });
        }

        return $query;
    }

    private function resolveNasabahTabungan(Request $request): array
    {
        $tabunganQuery = Tabungan::query()->with('nasabah');

        if ($request->filled('nasabah_id')) {
            $tabunganQuery->where('nasabah_id', $request->nasabah_id);
        }

        if ($request->filled('nomor_rekening')) {
            $tabunganQuery->whereHas('nasabah', fn (Builder $builder) => $builder->where('nomor_rekening', $request->nomor_rekening));
        }

        if ($request->filled('jenis_tabungan')) {
            // removed
        }

        $tabungan = $tabunganQuery->first();

        return [$tabungan?->nasabah, $tabungan];
    }

    private function summaryFromTransactions($transactions, ?Tabungan $tabungan = null): array
    {
        $totalSetoran = $transactions->where('jenis_transaksi', TransaksiTabungan::JENIS_SETOR)->sum('nominal');
        $totalPenarikan = $transactions->where('jenis_transaksi', TransaksiTabungan::JENIS_TARIK_TUNAI)->sum('nominal');
        $totalAdministrasi = $transactions->sum('administrasi');
        $saldoSaatIni = $transactions->last()?->saldo_setelah ?? ($tabungan?->saldo ?? 0);

        return [
            'total_setoran' => $totalSetoran,
            'total_penarikan' => $totalPenarikan,
            'total_administrasi' => $totalAdministrasi,
            'saldo_saat_ini' => $saldoSaatIni,
        ];
    }
}
