@php
    use App\Models\Tabungan;
    $headerColor = '#1B5E20';
    $jenisLabel  = 'Tabungan ' . ($jenis === 'gabungan' ? 'Gabungan' : ucfirst($jenis ?? 'reguler'));
    $reportTitle = 'Laporan Transaksi ' . $jenisLabel;
    if (isset($filters['tanggal']) && $filters['tanggal']) {
        $periodLabel = 'Tanggal: ' . \Carbon\Carbon::parse($filters['tanggal'])->format('d/m/Y');
    } elseif (isset($filters['bulan']) && $filters['bulan']) {
        $periodLabel = 'Bulan: ' . \Carbon\Carbon::createFromFormat('Y-m', $filters['bulan'])->translatedFormat('F Y');
    } else {
        $periodLabel = ($filters['start_date'] ?? $filters['end_date'])
            ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
            : 'Semua periode';
    }
    $summaryItems = [
        ['label' => 'Total Setoran',   'value' => 'Rp ' . number_format($summary['total_setoran'], 0, ',', '.')],
        ['label' => 'Total Penarikan', 'value' => 'Rp ' . number_format($summary['total_penarikan'], 0, ',', '.')],
        ['label' => 'Total Administrasi','value'=> 'Rp ' . number_format($summary['total_admin'], 0, ',', '.')],
    ];
@endphp
@include('exports.laporan.layout')

<table>
    <thead>
        <tr>
            <th class="text-center" style="width:24px">No</th>
            <th>No. Transaksi</th>
            <th>Tanggal</th>
            <th>Nasabah</th>
            <th>No. Rekening</th>
            <th class="text-center">Jenis</th>
            <th class="text-right">Nominal (Rp)</th>
            <th class="text-right">Laba Unit (Rp)</th>
            <th class="text-right">Saldo Sesudah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transaksi as $i => $trx)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $trx->nomor_transaksi }}</td>
            <td>{{ \Carbon\Carbon::parse($trx->tanggal)->format('d/m/Y') }}</td>
            <td>{{ $trx->tabungan?->nasabah?->nama ?? '-' }}</td>
            <td>{{ $trx->tabungan?->nasabah?->nomor_rekening ?? '-' }}</td>
            <td class="text-center">{{ $trx->jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan' }}</td>
            <td class="text-right">{{ number_format($trx->nominal, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($trx->administrasi, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($trx->saldo_setelah, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
