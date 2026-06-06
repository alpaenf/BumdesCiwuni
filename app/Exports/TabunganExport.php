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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TabunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        return ['No', 'No. Transaksi', 'Tanggal', 'Nasabah', 'No. Rekening', 'Jenis', 'Nominal (Rp)', 'Administrasi (Rp)', 'Saldo Setelah (Rp)', 'Keterangan'];
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
        $lastRow = $this->data->count() + 1;
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B5E20']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            "A1:J{$lastRow}" => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ],
        ];
    }
}
