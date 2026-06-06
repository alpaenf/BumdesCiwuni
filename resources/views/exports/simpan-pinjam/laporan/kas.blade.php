@php
    $headerColor = '#00695C';
    $reportTitle = 'Laporan Kas';
    $periodLabel = ($filters['start_date'] ?? $filters['end_date'])
        ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
        : 'Semua periode';
    $summaryItems = [];
@endphp
@include('exports.laporan.layout')

<table style="margin-bottom:16px">
    <thead>
        <tr>
            <th style="width:50%">Uraian</th>
            <th class="text-right">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight:bold;background:#e8f5e9;color:#1B5E20">KAS MASUK</td>
            <td class="text-right" style="background:#e8f5e9"></td>
        </tr>
        <tr>
            <td style="padding-left:20px">Setoran Tabungan</td>
            <td class="text-right">{{ number_format($summary['masuk_tabungan'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Pembayaran Angsuran Pinjaman</td>
            <td class="text-right">{{ number_format($summary['masuk_angsuran'], 0, ',', '.') }}</td>
        </tr>
        <tr style="font-weight:bold;background:#c8e6c9">
            <td>Total Kas Masuk</td>
            <td class="text-right">{{ number_format($summary['total_masuk'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;background:#fce4ec;color:#b71c1c">KAS KELUAR</td>
            <td class="text-right" style="background:#fce4ec"></td>
        </tr>
        <tr>
            <td style="padding-left:20px">Penarikan Tabungan + Administrasi</td>
            <td class="text-right">{{ number_format($summary['keluar_tabungan'], 0, ',', '.') }}</td>
        </tr>
        <tr style="font-weight:bold;background:#ffcdd2">
            <td>Total Kas Keluar</td>
            <td class="text-right">{{ number_format($summary['total_keluar'], 0, ',', '.') }}</td>
        </tr>
        <tr style="font-weight:bold;font-size:10pt;background:#{{ $summary['saldo_kas'] >= 0 ? 'b2dfdb' : 'ffcdd2' }}">
            <td>SALDO KAS BERSIH</td>
            <td class="text-right" style="color:#{{ $summary['saldo_kas'] >= 0 ? '00695C' : 'b71c1c' }}">
                {{ number_format($summary['saldo_kas'], 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>
