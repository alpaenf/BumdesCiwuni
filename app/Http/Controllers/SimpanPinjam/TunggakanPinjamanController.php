<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\Pinjaman;
use App\Services\PinjamanStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunggakanPinjamanController extends Controller
{
    public function index(Request $request, PinjamanStatusService $statusService): Response
    {
        $items = $this->queryPinjaman($request)
            ->get()
            ->map(function (Pinjaman $pinjaman) use ($statusService) {
                $pinjaman->loadMissing('angsuran');
                $status = $statusService->status($pinjaman);
                $lastPayment = $pinjaman->angsuran->sortByDesc('tanggal')->first();

                return [
                    'id' => $pinjaman->id,
                    'nama' => $pinjaman->nasabah?->nama,
                    'nomor_rekening' => $pinjaman->nasabah?->nomor_rekening,
                    'tanggal_akad' => $pinjaman->tanggal_akad->format('Y-m-d'),
                    'pinjaman_pokok' => $pinjaman->pinjaman_pokok,
                    'bunga' => $pinjaman->bunga,
                    'total_tagihan' => $pinjaman->total_tagihan,
                    'tenor' => $pinjaman->jumlah_angsuran,
                    'angsuran_terbayar' => $pinjaman->angsuran->count(),
                    'sisa_pinjaman' => $pinjaman->sisa_pinjaman,
                    'pasaran_terakhir' => $lastPayment?->pasaran,
                    'tanggal_terakhir_bayar' => $lastPayment?->tanggal?->format('Y-m-d'),
                    'status' => $status,
                ];
            });

        if ($request->filled('status')) {
            $items = $items->where('status', $request->status)->values();
        }

        $summary = $this->summary($items);

        return Inertia::render('SimpanPinjam/Tunggakan/Index', [
            'filters' => $request->only(['search', 'status', 'start_date', 'end_date']),
            'summary' => $summary,
            'items' => $items,
        ]);
    }

    public function print(Request $request, PinjamanStatusService $statusService)
    {
        $items = $this->queryPinjaman($request)
            ->get()
            ->map(function (Pinjaman $pinjaman) use ($statusService) {
                $pinjaman->loadMissing('angsuran');
                $status = $statusService->status($pinjaman);
                $lastPayment = $pinjaman->angsuran->sortByDesc('tanggal')->first();

                return [
                    'nama' => $pinjaman->nasabah?->nama,
                    'nomor_rekening' => $pinjaman->nasabah?->nomor_rekening,
                    'tanggal_akad' => $pinjaman->tanggal_akad->format('Y-m-d'),
                    'pinjaman_pokok' => $pinjaman->pinjaman_pokok,
                    'bunga' => $pinjaman->bunga,
                    'total_tagihan' => $pinjaman->total_tagihan,
                    'tenor' => $pinjaman->jumlah_angsuran,
                    'angsuran_terbayar' => $pinjaman->angsuran->count(),
                    'sisa_pinjaman' => $pinjaman->sisa_pinjaman,
                    'pasaran_terakhir' => $lastPayment?->pasaran,
                    'tanggal_terakhir_bayar' => $lastPayment?->tanggal?->format('Y-m-d'),
                    'status' => $status,
                ];
            });

        if ($request->filled('status')) {
            $items = $items->where('status', $request->status)->values();
        }

        return view('exports.simpan-pinjam.tunggakan', [
            'items' => $items,
            'summary' => $this->summary($items),
        ]);
    }

    public function pdf(Request $request, PinjamanStatusService $statusService)
    {
        return $this->print($request, $statusService);
    }

    public function excel(Request $request, PinjamanStatusService $statusService)
    {
        $items = $this->queryPinjaman($request)
            ->get()
            ->map(function (Pinjaman $pinjaman) use ($statusService) {
                $pinjaman->loadMissing('angsuran');
                $status = $statusService->status($pinjaman);
                $lastPayment = $pinjaman->angsuran->sortByDesc('tanggal')->first();

                return [
                    'nama' => $pinjaman->nasabah?->nama,
                    'nomor_rekening' => $pinjaman->nasabah?->nomor_rekening,
                    'tanggal_akad' => $pinjaman->tanggal_akad->format('Y-m-d'),
                    'pinjaman_pokok' => $pinjaman->pinjaman_pokok,
                    'bunga' => $pinjaman->bunga,
                    'total_tagihan' => $pinjaman->total_tagihan,
                    'tenor' => $pinjaman->jumlah_angsuran,
                    'angsuran_terbayar' => $pinjaman->angsuran->count(),
                    'sisa_pinjaman' => $pinjaman->sisa_pinjaman,
                    'pasaran_terakhir' => $lastPayment?->pasaran,
                    'tanggal_terakhir_bayar' => $lastPayment?->tanggal?->format('Y-m-d'),
                    'status' => $status,
                ];
            });

        if ($request->filled('status')) {
            $items = $items->where('status', $request->status)->values();
        }

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Nama',
                'Nomor Rekening',
                'Tanggal Akad',
                'Pinjaman Pokok',
                'Bunga',
                'Total Tagihan',
                'Tenor',
                'Angsuran Terbayar',
                'Sisa Pinjaman',
                'Pasaran Terakhir',
                'Tanggal Terakhir Bayar',
                'Status',
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item['nama'],
                    $item['nomor_rekening'],
                    $item['tanggal_akad'],
                    $item['pinjaman_pokok'],
                    $item['bunga'],
                    $item['total_tagihan'],
                    $item['tenor'],
                    $item['angsuran_terbayar'],
                    $item['sisa_pinjaman'],
                    $item['pasaran_terakhir'],
                    $item['tanggal_terakhir_bayar'],
                    $item['status'],
                ]);
            }

            fclose($handle);
        }, 'tunggakan-pinjaman.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function queryPinjaman(Request $request): Builder
    {
        $query = Pinjaman::query()->with(['nasabah', 'angsuran']);

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_akad', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_akad', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->whereHas('nasabah', function (Builder $builder) use ($request) {
                $search = '%'.$request->search.'%';

                $builder->where('nama', 'like', $search)
                    ->orWhere('nomor_rekening', 'like', $search);
            });
        }

        return $query;
    }

    private function summary($items): array
    {
        $lancar = $items->where('status', 'lancar')->count();
        $menunggak = $items->where('status', 'menunggak')->count();
        $kreditMacet = $items->where('status', 'kredit-macet')->count();
        $totalPiutang = $items->filter(fn ($item) => in_array($item['status'], ['menunggak', 'kredit-macet'], true))
            ->sum('sisa_pinjaman');

        return [
            'lancar' => $lancar,
            'menunggak' => $menunggak,
            'kredit_macet' => $kreditMacet,
            'total_piutang_bermasalah' => $totalPiutang,
        ];
    }
}
