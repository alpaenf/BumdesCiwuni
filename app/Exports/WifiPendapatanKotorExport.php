<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;

class WifiPendapatanKotorExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.wifi.laporan.pendapatan-kotor-excel', $this->data);
    }
}
