<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendapatanController extends Controller
{
    public function index(Request $request): Response
    {
        $year  = $request->input('tahun', now()->year);
        $month = $request->input('bulan'); // null = semua bulan

        // Helper: apply date filters to query
        $applyDateFilter = function ($query, string $dateColumn) use ($year, $month) {
            $query->whereYear($dateColumn, $year);
            if ($month) {
                $query->whereMonth($dateColumn, $month);
            }
            return $query;
        };

        // === Laba Tabungan Reguler ===
        $labaTabungan = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER))
                ->where('administrasi', '>', 0),
            'tanggal'
        )->sum('administrasi');

        // === Laba Tabungan Sembako ===
        $labaSembako = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO))
                ->where('administrasi', '>', 0),
            'tanggal'
        )->sum('administrasi');

        // === Pendapatan Bunga Pinjaman ===
        $pinjamanQuery = Pinjaman::whereYear('tanggal_akad', $year);
        if ($month) {
            $pinjamanQuery->whereMonth('tanggal_akad', $month);
        }
        $bungaPinjaman = (float) $pinjamanQuery
            ->selectRaw('COALESCE(SUM(total_tagihan - pinjaman_pokok), 0) as total_bunga')
            ->value('total_bunga');

        $pendapatanKotor = $bungaPinjaman;

        // Distribusi pendapatan kotor
        $distribusi = [
            ['nama' => 'Biaya Gaji',         'persen' => 55],
            ['nama' => 'Biaya Operasional',  'persen' => 20],
            ['nama' => 'Biaya Asuransi',     'persen' => 5],
            ['nama' => 'Biaya ATK',          'persen' => 3],
            ['nama' => 'Biaya Perlengkapan', 'persen' => 2],
            ['nama' => 'Laba Bersih',        'persen' => 15],
        ];

        foreach ($distribusi as &$item) {
            $item['nominal'] = $pendapatanKotor * $item['persen'] / 100;
        }

        // Detail transaksi laba tabungan reguler
        $detailTabungan = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER))
                ->with('tabungan.nasabah')
                ->where('administrasi', '>', 0),
            'tanggal'
        )
            ->orderByDesc('tanggal')
            ->limit(20)
            ->get()
            ->map(fn ($t) => [
                'tanggal' => $t->tanggal->format('Y-m-d'),
                'nasabah' => $t->tabungan?->nasabah?->nama ?? '-',
                'keterangan' => $t->keterangan,
                'laba' => $t->administrasi,
            ]);

        // Detail transaksi laba sembako
        $detailSembako = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO))
                ->with('tabungan.nasabah')
                ->where('administrasi', '>', 0),
            'tanggal'
        )
            ->orderByDesc('tanggal')
            ->limit(20)
            ->get()
            ->map(fn ($t) => [
                'tanggal' => $t->tanggal->format('Y-m-d'),
                'nasabah' => $t->tabungan?->nasabah?->nama ?? '-',
                'keterangan' => $t->keterangan,
                'laba' => $t->administrasi,
            ]);

        // Detail pinjaman & bunga
        $detailPinjamanQuery = Pinjaman::with('nasabah')->whereYear('tanggal_akad', $year);
        if ($month) {
            $detailPinjamanQuery->whereMonth('tanggal_akad', $month);
        }
        $detailPinjaman = $detailPinjamanQuery
            ->orderByDesc('tanggal_akad')
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'tanggal' => $p->tanggal_akad->format('Y-m-d'),
                'nasabah' => $p->nasabah?->nama ?? '-',
                'pokok' => $p->pinjaman_pokok,
                'bunga_persen' => $p->bunga,
                'bunga_nominal' => $p->total_tagihan - $p->pinjaman_pokok,
                'status' => $p->status,
            ]);

        // Options
        $tahunOptions = collect(range(now()->year, now()->year - 5))->values();
        $bulanOptions = collect([
            ['value' => '',  'label' => 'Semua Bulan'],
            ['value' => 1,   'label' => 'Januari'],
            ['value' => 2,   'label' => 'Februari'],
            ['value' => 3,   'label' => 'Maret'],
            ['value' => 4,   'label' => 'April'],
            ['value' => 5,   'label' => 'Mei'],
            ['value' => 6,   'label' => 'Juni'],
            ['value' => 7,   'label' => 'Juli'],
            ['value' => 8,   'label' => 'Agustus'],
            ['value' => 9,   'label' => 'September'],
            ['value' => 10,  'label' => 'Oktober'],
            ['value' => 11,  'label' => 'November'],
            ['value' => 12,  'label' => 'Desember'],
        ]);

        return Inertia::render('SimpanPinjam/Pendapatan/Index', [
            'tahun' => (int) $year,
            'bulan' => $month ? (int) $month : null,
            'tahunOptions' => $tahunOptions,
            'bulanOptions' => $bulanOptions,
            'labaTabungan' => (float) $labaTabungan,
            'labaSembako' => (float) $labaSembako,
            'bungaPinjaman' => (float) $bungaPinjaman,
            'pendapatanKotor' => (float) $pendapatanKotor,
            'distribusi' => $distribusi,
            'detailTabungan' => $detailTabungan,
            'detailSembako' => $detailSembako,
            'detailPinjaman' => $detailPinjaman,
        ]);
    }
}
