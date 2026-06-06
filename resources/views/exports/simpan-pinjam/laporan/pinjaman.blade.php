@php
    $headerColor = '#1A237E';
    $reportTitle = 'Laporan Data Pinjaman';
    $periodLabel = ($filters['start_date'] ?? $filters['end_date'])
        ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
        : 'Semua periode';
    $summaryItems = [
        ['label' => 'Total Pinjaman', 'value' => $summary['total']],
        ['label' => 'Aktif',          'value' => $summary['aktif']],
        ['label' => 'Lunas',          'value' => $summary['lunas']],
        ['label' => 'Total Pokok',    'value' => 'Rp ' . number_format($summary['total_pokok'], 0, ',', '.')],
        ['label' => 'Sisa Tagihan',   'value' => 'Rp ' . number_format($summary['total_sisa'], 0, ',', '.')],
    ];
@endphp
@include('exports.laporan.layout')

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:24px">No</th>
            <th>Nasabah</th>
            <th>No. Rekening</th>
            <th>Tgl. Akad</th>
            <th class="text-right">Pokok (Rp)</th>
            <th class="text-right">Total Tagihan (Rp)</th>
            <th class="text-right">Sisa (Rp)</th>
            <th class="text-center">Tenor</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pinjaman as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row['nasabah']['nama'] ?? '-' }}</td>
            <td>{{ $row['nasabah']['nomor_rekening'] ?? '-' }}</td>
            <td>{{ $row['tanggal_akad'] ? \Carbon\Carbon::parse($row['tanggal_akad'])->format('d/m/Y') : '-' }}</td>
            <td class="text-right">{{ number_format($row['pinjaman_pokok'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['total_tagihan'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row['sisa_pinjaman'], 0, ',', '.') }}</td>
            <td class="text-center">{{ $row['tenor_bulan'] }} bln</td>
            <td class="text-center">{{ ucfirst($row['computed_status'] ?? $row['status']) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
