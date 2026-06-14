<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\Angsuran;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\PinjamanStatusService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, PinjamanStatusService $statusService): Response
    {
        $totalTabunganReguler = Tabungan::where('jenis_tabungan', 'reguler')->sum('saldo');
        $totalTabunganSembako = Tabungan::where('jenis_tabungan', 'sembako')->sum('saldo');

        $pinjamanAktif = Pinjaman::where('status', 'aktif')->get();
        $pinjamanLunas = Pinjaman::where('status', 'lunas')->count();

        $loanChart = [
            'aktif' => 0,
            'lunas' => $pinjamanLunas,
            'menunggak' => 0,
            'kredit_macet' => 0,
        ];

        foreach ($pinjamanAktif as $pinjaman) {
            $pinjaman->loadMissing('angsuran');
            $status = $statusService->status($pinjaman);

            if ($status === 'kredit-macet') { $loanChart['kredit_macet']++; continue; }
            if ($status === 'menunggak')    { $loanChart['menunggak']++;    continue; }
            $loanChart['aktif']++;
        }

        // Filter bulan (format: YYYY-MM)
        $bulan = $request->filled('bulan') ? $request->bulan : null;
        [$filterYear, $filterMonth] = $bulan ? explode('-', $bulan) : [null, null];

        // Query transaksi tabungan sesuai filter
        $transaksiQuery = TransaksiTabungan::query();
        $angsuranQuery  = Angsuran::query();
        if ($bulan) {
            $transaksiQuery->whereYear('tanggal', $filterYear)->whereMonth('tanggal', $filterMonth);
            $angsuranQuery->whereYear('tanggal', $filterYear)->whereMonth('tanggal', $filterMonth);
        } else {
            $transaksiQuery->whereDate('tanggal', today());
            $angsuranQuery->whereDate('tanggal', today());
        }

        $recentTransactions = TransaksiTabungan::with('tabungan.nasabah')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                return [
                    'id'               => $tx->id,
                    'nomor_transaksi'  => $tx->nomor_transaksi,
                    'nasabah_nama'     => $tx->tabungan->nasabah->nama ?? '-',
                    'jenis_transaksi'  => $tx->jenis_transaksi,
                    'tanggal'          => $tx->tanggal->format('d/m/Y'),
                    'nominal'          => $tx->nominal,
                ];
            });

        // Available bulan untuk dropdown
        $availableBulan = TransaksiTabungan::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan")
            ->groupBy('bulan')->orderByDesc('bulan')->pluck('bulan');

        return Inertia::render('SimpanPinjam/Dashboard', [
            'stats' => [
                'totalNasabah'        => Nasabah::count(),
                'totalTabunganReguler'=> $totalTabunganReguler,
                'totalTabunganSembako'=> $totalTabunganSembako,
                'totalPinjamanAktif'  => $pinjamanAktif->count(),
                'totalPiutangBerjalan'=> Pinjaman::where('status', 'aktif')->sum('sisa_pinjaman'),
                'transaksiPeriode'    => $transaksiQuery->count(),
                'angsuranPeriode'     => $angsuranQuery->count(),
            ],
            'loanChart'          => $loanChart,
            'recentTransactions' => $recentTransactions,
            'availableBulan'     => $availableBulan,
            'filters'            => $request->only(['bulan']),
        ]);
    }
}
