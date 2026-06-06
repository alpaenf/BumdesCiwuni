<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\Angsuran;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Services\PinjamanStatusService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(PinjamanStatusService $statusService): Response
    {
        $totalTabunganReguler = Tabungan::where('jenis_tabungan', Tabungan::JENIS_REGULER)->sum('saldo');
        $totalTabunganSembako = Tabungan::where('jenis_tabungan', Tabungan::JENIS_SEMBAKO)->sum('saldo');
        $totalSaldoKas = Tabungan::sum('saldo');

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

            if ($status === 'kredit-macet') {
                $loanChart['kredit_macet']++;
                continue;
            }

            if ($status === 'menunggak') {
                $loanChart['menunggak']++;
                continue;
            }

            $loanChart['aktif']++;
        }

        $recentTransactions = TransaksiTabungan::with('tabungan.nasabah')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'nomor_transaksi' => $tx->nomor_transaksi,
                    'nasabah_nama' => $tx->tabungan->nasabah->nama ?? '-',
                    'jenis_transaksi' => $tx->jenis_transaksi,
                    'tanggal' => $tx->tanggal->format('d/m/Y'),
                    'nominal' => $tx->nominal,
                ];
            });

        return Inertia::render('SimpanPinjam/Dashboard', [
            'stats' => [
                'totalNasabah' => Nasabah::count(),
                'totalTabunganReguler' => $totalTabunganReguler,
                'totalTabunganSembako' => $totalTabunganSembako,
                'totalPinjamanAktif' => $pinjamanAktif->count(),
                'totalPiutangBerjalan' => Pinjaman::where('status', 'aktif')->sum('sisa_pinjaman'),
                'totalSaldoKas' => $totalSaldoKas,
                'transaksiHariIni' => TransaksiTabungan::whereDate('created_at', today())->count(),
                'angsuranHariIni' => Angsuran::whereDate('created_at', today())->count(),
            ],
            'loanChart' => $loanChart,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}

