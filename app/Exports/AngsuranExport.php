<?php

namespace App\Exports;

use App\Models\Angsuran;
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

class AngsuranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private $data;

    public function __construct(private Request $request)
    {
        $query = Angsuran::with('pinjaman.nasabah');
        if ($request->filled('start_date')) $query->whereDate('tanggal', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal', '<=', $request->end_date);
        $this->data = $query->orderByDesc('tanggal')->get();
    }

    public function collection() { return $this->data; }

    public function title(): string { return 'Laporan Angsuran'; }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Nasabah', 'No. Rekening', 'Ke-', 'Jumlah Bayar (Rp)', 'Pokok (Rp)', 'Bunga (Rp)', 'Sisa Pinjaman (Rp)', 'Keterangan'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->tanggal ? date('d/m/Y', strtotime($row->tanggal)) : '-',
            $row->pinjaman?->nasabah?->nama ?? '-',
            $row->pinjaman?->nasabah?->nomor_rekening ?? '-',
            $row->ke,
            number_format($row->jumlah_bayar, 0, ',', '.'),
            number_format($row->pokok, 0, ',', '.'),
            number_format($row->bunga, 0, ',', '.'),
            number_format($row->sisa_pinjaman, 0, ',', '.'),
            $row->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 1;
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E65100']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:J{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ],
        ];
    }
}
