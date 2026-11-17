<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Kas Unit - {{ $periodLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 15mm 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #0f172a;
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
            color: #0f172a;
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
            padding: 5px 8px;
            background-color: #f8fafc;
            width: 25%;
        }
        .summary-cell .label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .summary-cell .value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }
        .summary-cell.highlight {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 8px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 7.5px;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge-masuk {
            color: #15803d;
            font-weight: bold;
        }
        .badge-keluar {
            color: #b91c1c;
            font-weight: bold;
        }
        .footer-sign {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .footer-sign td {
            text-align: center;
            font-size: 9px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>BUMDES DAMMAR WULAN — DESA CIWUNI</h2>
        <h3>UNIT PERDAGANGAN BESAR — BUKU KAS & REKAPITULASI SALDO</h3>
        <p>Jl. Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap, Jawa Tengah • Kode Pos 53274</p>
    </div>

    <table class="meta-container">
        <tr>
            <td width="60%">
                <table class="meta-box">
                    <tr>
                        <td width="22%"><strong>Format Laporan</strong></td>
                        <td width="78%">: {{ $periodMode === 'month' ? 'Laporan Bulanan' : 'Laporan Harian / Kustom' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Periode</strong></td>
                        <td>: <strong style="color: #0f172a;">{{ $periodLabel }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Mutasi</strong></td>
                        <td>: {{ $kasList->count() }} catatan kas</td>
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
                        <td><strong>Kas Tunai (Fisik)</strong></td>
                        <td>: Rp {{ number_format($summary['saldo_tunai'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kas Bank (Rekening)</strong></td>
                        <td>: Rp {{ number_format($summary['saldo_rekening'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Nilai -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="label">Total Kas Masuk</div>
                <div class="value" style="color: #15803d;">Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Kas Keluar</div>
                <div class="value" style="color: #b91c1c;">Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell highlight" colspan="2">
                <div class="label">SURPLUS / SALDO AKHIR KAS ({{ strtoupper($periodLabel) }})</div>
                <div class="value" style="font-size: 13px; color: #0f172a;">Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Kas -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="9%">Tanggal</th>
                <th width="14%">Layanan / Sumber</th>
                <th width="7%" class="text-center">Arus</th>
                <th width="8%" class="text-center">Tipe Kas</th>
                <th width="12%" class="text-right">Kas Masuk</th>
                <th width="12%" class="text-right">Kas Keluar</th>
                <th width="23%">Keterangan Mutasi</th>
                <th width="12%">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasList as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td class="font-bold">{{ $item->kategori_layanan }}</td>
                    <td class="text-center">
                        <span class="{{ $item->jenis === 'masuk' ? 'badge-masuk' : 'badge-keluar' }}">
                            {{ strtoupper($item->jenis) }}
                        </span>
                    </td>
                    <td class="text-center">{{ $item->tipe_kas }}</td>
                    <td class="text-right font-bold" style="color: #15803d;">
                        {{ $item->jenis === 'masuk' ? number_format($item->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold" style="color: #b91c1c;">
                        {{ $item->jenis === 'keluar' ? number_format($item->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td>{{ $item->keterangan }}</td>
                    <td>{{ $item->nama_admin }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada catatan arus kas untuk periode {{ $periodLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($kasList->count() > 0)
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="5" class="text-center">TOTAL ARUS KAS ({{ $kasList->count() }} Catatan)</td>
                <td class="text-right" style="color: #15803d;">Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #b91c1c;">Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="color: #0f172a;">Saldo: Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-sign">
        <tr>
            <td width="33%">
                <p>Mengetahui,</p>
                <p><strong>Direktur BUMDes Dammar Wulan</strong></p>
                <br><br><br><br>
                <p><strong>( .................................................. )</strong></p>
            </td>
            <td width="34%"></td>
            <td width="33%">
                <p>Desa Ciwuni, {{ now()->translatedFormat('d F Y') }}</p>
                <p><strong>Kepala Unit Perdagangan Besar</strong></p>
                <br><br><br><br>
                <p><strong>( .................................................. )</strong></p>
            </td>
        </tr>
    </table>

</body>
</html>
