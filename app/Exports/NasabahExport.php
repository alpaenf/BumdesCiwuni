<?php

namespace App\Exports;

use App\Models\Nasabah;
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

class NasabahExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private $data;

    public function __construct(private Request $request)
    {
        $query = Nasabah::query();
        if ($request->filled('start_date')) $query->whereDate('tanggal_bergabung', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal_bergabung', '<=', $request->end_date);
        $this->data = $query->orderBy('nama')->get();
    }

    public function collection() { return $this->data; }

    public function title(): string { return 'Laporan Nasabah'; }

    public function headings(): array
    {
        return ['No', 'No. Registrasi', 'No. Rekening', 'Nama Lengkap', 'NIK', 'No. HP', 'Alamat', 'Tanggal Bergabung', 'Status'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->nomor_registrasi,
            $row->nomor_rekening,
            $row->nama,
            $row->nik,
            $row->no_hp,
            $row->alamat,
            $row->tanggal_bergabung ? date('d/m/Y', strtotime($row->tanggal_bergabung)) : '-',
            ucfirst($row->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 1;
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:I{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ],
        ];
    }
}
