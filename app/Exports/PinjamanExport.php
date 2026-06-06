<?php

namespace App\Exports;

use App\Models\Pinjaman;
use App\Services\PinjamanStatusService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PinjamanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private $data;

    public function __construct(private Request $request, private PinjamanStatusService $statusService)
    {
        $query = Pinjaman::with(['nasabah', 'angsuran']);
        if ($request->filled('start_date')) $query->whereDate('tanggal_akad', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal_akad', '<=', $request->end_date);
        if ($request->filled('status'))     $query->where('status', $request->status);
        $this->data = $query->orderByDesc('tanggal_akad')->get();
    }

    public function collection() { return $this->data; }

    public function title(): string { return 'Laporan Pinjaman'; }

    public function headings(): array
    {
        return ['No', 'Nasabah', 'No. Rekening', 'Tgl. Akad', 'Pokok (Rp)', 'Total Tagihan (Rp)', 'Sisa Pinjaman (Rp)', 'Tenor (Bulan)', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->nasabah?->nama ?? '-',
            $row->nasabah?->nomor_rekening ?? '-',
            $row->tanggal_akad ? date('d/m/Y', strtotime($row->tanggal_akad)) : '-',
            number_format($row->pinjaman_pokok, 0, ',', '.'),
            number_format($row->total_tagihan, 0, ',', '.'),
            number_format($row->sisa_pinjaman, 0, ',', '.'),
            $row->tenor_bulan,
            ucfirst($this->statusService->status($row)),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 1;
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A237E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:I{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ],
        ];
    }
}
