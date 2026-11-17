<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Biaya Operasional - {{ $periodLabel }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 12mm 15mm 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #e11d48;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 2px 0 0 0;
            color: #e11d48;
            font-size: 11px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #64748b;
        }
        .meta-container {
            width: 100%;
            margin-bottom: 10px;
        }
        .meta-box {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-box td {
            font-size: 9px;
            padding: 2px 0;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-cell {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            background-color: #fff1f2;
            border-color: #fecdd3;
        }
        .summary-cell .label {
            font-size: 8px;
            text-transform: uppercase;
            color: #9f1239;
            font-weight: bold;
        }
        .summary-cell .value {
            font-size: 14px;
            font-weight: bold;
            color: #be123c;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8.5px;
        }
        table.data-table th {
            background-color: #be123c;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 8px;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer-sign {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .footer-sign td {
            text-align: center;
            font-size: 9.5px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>BUMDES DAMMAR WULAN — DESA CIWUNI</h2>
        <h3>UNIT PERDAGANGAN BESAR — REKAPITULASI BIAYA OPERASIONAL</h3>
        <p>Jl. Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap, Jawa Tengah • Kode Pos 53274</p>
    </div>

    <table class="meta-container">
        <tr>
            <td width="60%">
                <table class="meta-box">
                    <tr>
                        <td width="24%"><strong>Format Laporan</strong></td>
                        <td width="76%">: {{ $periodMode === 'date' ? 'Laporan Harian' : 'Laporan Bulanan' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Periode</strong></td>
                        <td>: <strong style="color: #be123c;">{{ $periodLabel }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Catatan</strong></td>
                        <td>: {{ $biayaList->count() }} pengeluaran operasional</td>
                    </tr>
                </table>
            </td>
            <td width="40%">
                <table class="meta-box">
                    <tr>
                        <td width="35%"><strong>Dicetak Pada</strong></td>
                        <td width="65%">: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td><strong>Unit Usaha</strong></td>
                        <td>: Perdagangan Besar (Operasional)</td>
                    </tr>
                    <tr>
                        <td><strong>Dicetak Oleh</strong></td>
                        <td>: {{ auth()->user()->nama ?? 'Admin Unit' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Nilai -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="label">TOTAL BEBAN & PENGELUARAN OPERASIONAL ({{ strtoupper($periodLabel) }})</div>
                <div class="value">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Biaya -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="12%">Tanggal</th>
                <th width="18%">Kategori Biaya</th>
                <th width="28%">Uraian Kebutuhan</th>
                <th width="15%" class="text-right">Nominal (Rp)</th>
                <th width="13%">Petugas</th>
                <th width="10%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($biayaList as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td class="font-bold">{{ $item->kategori_biaya }}</td>
                    <td>{{ $item->uraian }}</td>
                    <td class="text-right font-bold" style="color: #be123c;">
                        {{ number_format($item->nominal, 0, ',', '.') }}
                    </td>
                    <td>{{ $item->nama_admin }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada catatan pengeluaran operasional untuk periode {{ $periodLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($biayaList->count() > 0)
        <tfoot>
            <tr style="background-color: #fff1f2; font-weight: bold;">
                <td colspan="4" class="text-center">TOTAL BIAYA OPERASIONAL ({{ $biayaList->count() }} Transaksi)</td>
                <td class="text-right" style="color: #be123c; font-size: 9.5px;">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</td>
                <td colspan="2">-</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-sign">
        <tr>
            <td width="50%">
                <p>Mengetahui,</p>
                <p><strong>Direktur BUMDes Dammar Wulan</strong></p>
                <br><br><br><br>
                <p><strong>( .................................................. )</strong></p>
            </td>
            <td width="50%">
                <p>Desa Ciwuni, {{ now()->translatedFormat('d F Y') }}</p>
                <p><strong>Kepala Unit Perdagangan Besar</strong></p>
                <br><br><br><br>
                <p><strong>( .................................................. )</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
