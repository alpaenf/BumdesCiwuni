<?php

namespace App\Exports;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Contracts\View\View;

class NasabahExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private $data;
    private $filters;
    private $summary;

    public function __construct(Request $request)
    {
        $query = Nasabah::query();
        if ($request->filled('start_date')) $query->whereDate('tanggal_bergabung', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal_bergabung', '<=', $request->end_date);
        $this->data = $query->orderBy('nomor_rekening')->get();

        $this->filters = $request->only(['start_date', 'end_date']);
        $this->summary = [
            'total'       => $this->data->count(),
            'aktif'       => $this->data->where('status', 'aktif')->count(),
            'tidak_aktif' => $this->data->where('status', 'tidak aktif')->count(),
        ];
    }

    public function view(): View
    {
        return view('exports.simpan-pinjam.laporan.nasabah', [
            'data'    => $this->data,
            'filters' => $this->filters,
            'summary' => $this->summary,
            'isExcel' => true,
        ]);
    }
}
