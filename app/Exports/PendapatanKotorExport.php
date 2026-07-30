<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Contracts\View\View;

class PendapatanKotorExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.simpan-pinjam.laporan.pendapatan-kotor-excel', $this->data);
    }
}
