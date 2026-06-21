@php
    $headerColor = '#E65100';
    $reportTitle = 'Laporan Pembayaran Angsuran';
    if (isset($filters['tanggal']) && $filters['tanggal']) {
        $periodLabel = 'Tanggal: ' . \Carbon\Carbon::parse($filters['tanggal'])->format('d/m/Y');
    } elseif (isset($filters['bulan']) && $filters['bulan']) {
        $periodLabel = 'Bulan: ' . \Carbon\Carbon::createFromFormat('Y-m', $filters['bulan'])->translatedFormat('F Y');
    } else {
        $periodLabel = (isset($filters['start_date']) || isset($filters['end_date']))
            ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
            : 'Semua periode';
    }
    $summaryItems = [
        ['label' => 'Jumlah Transaksi', 'value' => $summary['total_transaksi']],
        ['label' => 'Total Dibayar',    'value' => 'Rp ' . number_format($summary['total_bayar'], 0, ',', '.')],
    ];
@endphp
@extends(isset($isExcel) ? 'exports.simpan-pinjam.laporan.excel_layout' : 'exports.simpan-pinjam.laporan.layout')

@section('content')
@if(isset($isExcel))
    <table>
        <tr><td colspan="8"></td></tr>
        <tr><td colspan="8"></td></tr>
        <tr>
            <th colspan="8" style="font-size:14pt; font-weight:bold; text-align:center">BUMDes Dammar Wulan - Unit Simpan Pinjam</th>
        </tr>
        <tr>
            <th colspan="8" style="font-size:12pt; font-weight:bold; text-align:center">{{ $reportTitle }}</th>
        </tr>
        <tr>
            <th colspan="8" style="font-size:10pt; text-align:center">{{ $periodLabel }}</th>
        </tr>
        <tr><td colspan="8"></td></tr>
        @if(isset($summaryItems) && count($summaryItems))
            @foreach($summaryItems as $item)
            <tr>
                <td colspan="2" style="font-weight:bold;">{{ $item['label'] }}</td>
                <td colspan="6" style="font-weight:bold;text-align:left;">: {{ $item['value'] }}</td>
            </tr>
            @endforeach
            <tr><td colspan="8"></td></tr>
        @endif
    </table>
@endif

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:24px">No</th>
            <th>Nasabah</th>
            <th>No. Rekening</th>
            <th>Tanggal</th>
            <th class="text-center">Angsuran Ke</th>
            <th>Hari Pasaran</th>
            <th class="text-right">Jumlah Bayar (Rp)</th>
            <th class="text-right">Sisa Pinjaman (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($angsuran as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row->pinjaman?->nasabah?->nama ?? '-' }}</td>
            <td style="mso-number-format:'\@'">{{ $row->pinjaman?->nasabah?->nomor_rekening ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
            <td class="text-center">{{ $row->angsuran_ke }}</td>
            <td>{{ ucfirst($row->pasaran ?? '-') }}</td>
            <td class="text-right">{{ number_format($row->jumlah_bayar, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row->sisa_pinjaman, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
