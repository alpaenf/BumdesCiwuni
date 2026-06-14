<?php

namespace App\Exports;

use App\Models\Pinjaman;
use App\Services\PinjamanStatusService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Contracts\View\View;

class PinjamanExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private $pinjaman;
    private $filters;
    private $summary;

    public function __construct(Request $request, private PinjamanStatusService $statusService)
    {
        $query = Pinjaman::with(['nasabah', 'angsuran']);
        if ($request->filled('start_date')) $query->whereDate('tanggal_akad', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal_akad', '<=', $request->end_date);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('bulan')) {
            $query->whereYear('tanggal_akad', substr($request->bulan, 0, 4))
                  ->whereMonth('tanggal_akad', substr($request->bulan, 5, 2));
        }

        $rawPinjaman = $query->orderByDesc('tanggal_akad')->get();
        $this->pinjaman = $rawPinjaman->map(fn($p) => array_merge($p->toArray(), ['computed_status' => $statusService->status($p)]));

        $this->filters = $request->only(['start_date', 'end_date', 'status', 'bulan']);
        $this->summary = [
            'total'         => $rawPinjaman->count(),
            'aktif'         => $rawPinjaman->where('status', 'aktif')->count(),
            'lunas'         => $rawPinjaman->where('status', 'lunas')->count(),
            'total_pokok'   => $rawPinjaman->sum('pinjaman_pokok'),
            'total_tagihan' => $rawPinjaman->sum('total_tagihan'),
            'total_sisa'    => $rawPinjaman->sum('sisa_pinjaman'),
        ];
    }

    public function view(): View
    {
        return view('exports.simpan-pinjam.laporan.pinjaman', [
            'pinjaman' => $this->pinjaman,
            'filters'  => $this->filters,
            'summary'  => $this->summary,
            'isExcel'  => true,
        ]);
    }
}
