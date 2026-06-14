@php
    $headerColor = '#E65100';
    $reportTitle = 'Laporan Pembayaran Angsuran';
    $periodLabel = (isset($filters['start_date']) || isset($filters['end_date']))
        ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
        : 'Semua periode';
    $summaryItems = [
        ['label' => 'Jumlah Transaksi', 'value' => $summary['total_transaksi']],
        ['label' => 'Total Dibayar',    'value' => 'Rp ' . number_format($summary['total_bayar'], 0, ',', '.')],
    ];
@endphp
@if(!isset($isExcel))
    @include('exports.simpan-pinjam.laporan.layout')
@else
    <table>
        <tr><td colspan="9"></td></tr>
        <tr><td colspan="9"></td></tr>
        <tr>
            <th colspan="9" style="font-size:14pt; font-weight:bold; text-align:center">BUMDes Dammar Wulan - Unit Simpan Pinjam</th>
        </tr>
        <tr>
            <th colspan="9" style="font-size:12pt; font-weight:bold; text-align:center">{{ $reportTitle }}</th>
        </tr>
        <tr>
            <th colspan="9" style="font-size:10pt; text-align:center">{{ $periodLabel }}</th>
        </tr>
        <tr><td colspan="9"></td></tr>
    </table>
@endif

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:24px">No</th>
            <th>Tanggal</th>
            <th>Nasabah</th>
            <th>No. Rekening</th>
            <th class="text-center">Angsuran Ke</th>
            <th class="text-right">Jumlah Bayar (Rp)</th>
            <th class="text-right">Pokok (Rp)</th>
            <th class="text-right">Bunga (Rp)</th>
            <th class="text-right">Sisa Pinjaman (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($angsuran as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
            <td>{{ $row->pinjaman?->nasabah?->nama ?? '-' }}</td>
            <td style="mso-number-format:'\@'">{{ $row->pinjaman?->nasabah?->nomor_rekening ?? '-' }}</td>
            <td class="text-center">{{ $row->ke }}</td>
            <td class="text-right">{{ number_format($row->jumlah_bayar, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row->pokok, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row->bunga, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row->sisa_pinjaman, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
