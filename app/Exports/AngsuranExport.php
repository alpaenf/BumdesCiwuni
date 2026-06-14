<?php

namespace App\Exports;

use App\Models\Angsuran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Contracts\View\View;

class AngsuranExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private $angsuran;
    private $filters;
    private $summary;

    public function __construct(Request $request)
    {
        $query = Angsuran::with('pinjaman.nasabah');
        if ($request->filled('start_date')) $query->whereDate('tanggal', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal', '<=', $request->end_date);
        if ($request->filled('bulan')) {
            $query->whereYear('tanggal', substr($request->bulan, 0, 4))
                  ->whereMonth('tanggal', substr($request->bulan, 5, 2));
        }
        $this->angsuran = $query->orderByDesc('tanggal')->get();

        $this->filters = $request->only(['start_date', 'end_date', 'bulan']);
        $this->summary = [
            'total_transaksi' => $this->angsuran->count(),
            'total_bayar'     => $this->angsuran->sum('jumlah_bayar'),
        ];
    }

    public function view(): View
    {
        return view('exports.simpan-pinjam.laporan.angsuran', [
            'angsuran' => $this->angsuran,
            'filters'  => $this->filters,
            'summary'  => $this->summary,
            'isExcel'  => true,
        ]);
    }
}
