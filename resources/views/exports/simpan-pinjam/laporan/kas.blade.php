@php
    $headerColor = '#00695C';
    $reportTitle = 'Laporan Kas';
    
    $periodLabel = 'Periode: Semua Bulan ' . ($filters['tahun'] ?? date('Y'));
    if (!empty($filters['tanggal'])) {
        $periodLabel = 'Periode: ' . \Carbon\Carbon::parse($filters['tanggal'])->translatedFormat('d F Y');
    } elseif (!empty($filters['bulan'])) {
        $periodLabel = 'Periode: Bulan ' . $filters['bulan'] . ' Tahun ' . ($filters['tahun'] ?? date('Y'));
    }
    $uang_bumdes = $summary['saldo_reguler'] + $summary['saldo_sembako'];
    $pinjaman_pokok = $summary['pinjaman_aktif_pokok'];
    $angsuran_total = $summary['angsuran_aktif_total'];
    $selisih = $summary['selisih_aktif'];
    $uang_cash = $uang_bumdes - $selisih;

    $summaryItems = [
        ['label' => 'Total Uang BUMDes', 'value' => 'Rp ' . number_format($uang_bumdes, 0, ',', '.')],
        ['label' => 'Pinjaman Aktif (Pokok)',  'value' => 'Rp ' . number_format($pinjaman_pokok, 0, ',', '.')],
        ['label' => 'Angsuran (Total)',  'value' => 'Rp ' . number_format($angsuran_total, 0, ',', '.')],
        ['label' => 'Selisih Pinjaman',  'value' => 'Rp ' . number_format($selisih, 0, ',', '.')],
        ['label' => 'Total Uang Cash',   'value' => 'Rp ' . number_format($uang_cash, 0, ',', '.')],
    ];
@endphp
@extends('exports.simpan-pinjam.laporan.layout')

@section('content')

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
            <td style="padding-left:20px">Setoran Tabungan (Reguler & Sembako)</td>
            <td class="text-right">{{ number_format($summary['masuk_reguler'] + $summary['masuk_sembako'], 0, ',', '.') }}</td>
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
            <td style="padding-left:20px">Penarikan Tabungan (Reguler & Sembako) + Admin</td>
            <td class="text-right">{{ number_format($summary['keluar_reguler'] + $summary['keluar_sembako'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:20px">Pencairan Dana Pinjaman Baru</td>
            <td class="text-right">{{ number_format($summary['keluar_pinjaman'], 0, ',', '.') }}</td>
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
@endsection
