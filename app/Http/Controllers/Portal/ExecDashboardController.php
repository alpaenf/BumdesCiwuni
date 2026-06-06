<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\Unit;
use App\Services\PinjamanStatusService;
use Inertia\Inertia;
use Inertia\Response;

class ExecDashboardController extends Controller
{
    public function index(PinjamanStatusService $statusService): Response
    {
        // === Unit Overview ===
        $units = Unit::orderBy('urutan')->get();
        $unitAktif = $units->where('status', 'aktif')->count();

        // === Simpan Pinjam Data (from existing tables) ===
        $totalNasabah = Nasabah::count();
        $totalTabungan = Tabungan::sum('saldo');
        $totalPiutang = Pinjaman::where('status', 'aktif')->sum('sisa_pinjaman');
        $totalTransaksi = TransaksiTabungan::count();

        // Loan status breakdown
        $pinjamanAktif = Pinjaman::where('status', 'aktif')->get();
        $loanStats = [
            'aktif' => 0,
            'menunggak' => 0,
            'kredit_macet' => 0,
            'lunas' => Pinjaman::where('status', 'lunas')->count(),
        ];

        foreach ($pinjamanAktif as $pinjaman) {
            $pinjaman->loadMissing('angsuran');
            $status = $statusService->status($pinjaman);

            if ($status === 'kredit-macet') {
                $loanStats['kredit_macet']++;
            } elseif ($status === 'menunggak') {
                $loanStats['menunggak']++;
            } else {
                $loanStats['aktif']++;
            }
        }

        // Monthly transaction data for charts (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'bulan' => $month->translatedFormat('M Y'),
                'setor' => TransaksiTabungan::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('jenis_transaksi', 'setor')
                    ->sum('nominal'),
                'tarik' => TransaksiTabungan::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('jenis_transaksi', 'tarik')
                    ->sum('nominal'),
                'angsuran' => Angsuran::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('jumlah_bayar'),
            ];
        }

        // Per-unit summary
        $unitSummaries = [];
        foreach ($units->where('status', 'aktif') as $unit) {
            if ($unit->slug === 'simpan-pinjam') {
                $unitSummaries[] = [
                    'id' => $unit->id,
                    'nama' => $unit->nama_unit,
                    'slug' => $unit->slug,
                    'icon' => $unit->icon,
                    'tipe' => $unit->tipe,
                    'data' => [
                        'nasabah' => $totalNasabah,
                        'total_tabungan' => $totalTabungan,
                        'piutang' => $totalPiutang,
                        'tunggakan' => $loanStats['menunggak'],
                        'kredit_macet' => $loanStats['kredit_macet'],
                    ],
                ];
            } else {
                $unitSummaries[] = [
                    'id' => $unit->id,
                    'nama' => $unit->nama_unit,
                    'slug' => $unit->slug,
                    'icon' => $unit->icon,
                    'tipe' => $unit->tipe,
                    'data' => null, // External or no data yet
                ];
            }
        }

        return Inertia::render('Portal/ExecDashboard', [
            'ringkasan' => [
                'unitAktif' => $unitAktif,
                'totalNasabah' => $totalNasabah,
                'totalTabungan' => $totalTabungan,
                'totalPiutang' => $totalPiutang,
                'totalTunggakan' => $loanStats['menunggak'],
                'totalKreditMacet' => $loanStats['kredit_macet'],
                'totalTransaksi' => $totalTransaksi,
            ],
            'loanStats' => $loanStats,
            'monthlyData' => $monthlyData,
            'unitSummaries' => $unitSummaries,
        ]);
    }
}
