<?php

namespace App\Exports;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TabunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithDrawings
{
    private $data;

    public function __construct(private Request $request)
    {
        $query = TransaksiTabungan::with('tabungan.nasabah');

        if ($request->filled('start_date')) $query->whereDate('tanggal', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal', '<=', $request->end_date);

        $this->data = $query->orderBy('tanggal')->get();
    }

    public function collection() { return $this->data; }

    public function title(): string { return 'Laporan Tabungan'; }

    public function headings(): array
    {
        return [
            [''],
            [''],
            [''],
            [''],
            [''],
            ['No', 'No. Transaksi', 'Tanggal', 'Nasabah', 'No. Rekening', 'Jenis', 'Nominal (Rp)', 'Administrasi (Rp)', 'Saldo Setelah (Rp)', 'Keterangan']
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo BUMDes');
        $drawing->setPath(public_path('logo.png'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');
        return $drawing;
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->nomor_transaksi,
            $row->tanggal ? date('d/m/Y', strtotime($row->tanggal)) : '-',
            $row->tabungan?->nasabah?->nama ?? '-',
            $row->tabungan?->nasabah?->nomor_rekening ?? '-',
            $row->jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan',
            number_format($row->nominal, 0, ',', '.'),
            number_format($row->administrasi, 0, ',', '.'),
            number_format($row->saldo_setelah, 0, ',', '.'),
            $row->keterangan,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('B1:J1');
        $sheet->setCellValue('B1', 'BADAN USAHA MILIK DESA (BUMDesa)');
        
        $sheet->mergeCells('B2:J2');
        $sheet->setCellValue('B2', 'DAMMAR WULAN - UNIT SIMPAN PINJAM');
        
        $sheet->mergeCells('B3:J3');
        $sheet->setCellValue('B3', 'LAPORAN TABUNGAN');

        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B1:B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $lastRow = $this->data->count() + 6;
        return [
            6 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A6:J{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ],
        ];
    }
}
