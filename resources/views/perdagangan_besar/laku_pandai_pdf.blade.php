<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Laku Pandai - {{ $periodLabel }}</title>
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
            border-bottom: 2.5px solid #1e3a8a;
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
            color: #2563eb;
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
            background-color: #eff6ff;
            border-color: #93c5fd;
        }
        .summary-cell.highlight .value {
            color: #1e40af;
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
            background-color: #1e3a8a;
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
        .badge-lunas {
            color: #15803d;
            font-weight: bold;
        }
        .badge-hutang {
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
        <h3>UNIT PERDAGANGAN BESAR — LAYANAN LAKU PANDAI</h3>
        <p>Jl. Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap, Jawa Tengah • Kode Pos 53274</p>
    </div>

    <table class="meta-container">
        <tr>
            <td width="60%">
                <table class="meta-box">
                    <tr>
                        <td width="22%"><strong>Format Laporan</strong></td>
                        <td width="78%">: {{ $periodMode === 'month' ? 'Laporan Bulanan' : 'Laporan Harian' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Periode</strong></td>
                        <td>: <strong style="color: #1e40af;">{{ $periodLabel }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Transaksi</strong></td>
                        <td>: {{ $summary['total_transaksi'] }} transaksi tercatat</td>
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
                        <td><strong>Saldo Cash</strong></td>
                        <td>: Rp {{ number_format($saldo['saldo_cash'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Saldo Rekening</strong></td>
                        <td>: Rp {{ number_format($saldo['saldo_rekening'], 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Nilai -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="label">Total Rekening Masuk</div>
                <div class="value">Rp {{ number_format($summary['rekening_masuk'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Rekening Keluar</div>
                <div class="value">Rp {{ number_format($summary['rekening_keluar'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Cash Masuk</div>
                <div class="value">Rp {{ number_format($summary['cash_masuk'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Cash Keluar</div>
                <div class="value">Rp {{ number_format($summary['cash_keluar'], 0, ',', '.') }}</div>
            </td>
        </tr>
        <tr>
            <td class="summary-cell">
                <div class="label">Pendapatan Jasa Cash</div>
                <div class="value" style="color: #059669;">Rp {{ number_format($summary['pendapatan_cash'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Pendapatan Jasa Rekening</div>
                <div class="value" style="color: #059669;">Rp {{ number_format($summary['pendapatan_rekening'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell highlight" colspan="2">
                <div class="label" style="color: #1e40af;">TOTAL PENDAPATAN JASA BUMDES ({{ strtoupper($periodLabel) }})</div>
                <div class="value" style="font-size: 13px; color: #1e3a8a;">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="8%">Tanggal</th>
                <th width="10%">No Transaksi</th>
                <th width="13%">Nama Nasabah</th>
                <th width="10%">Layanan</th>
                <th width="8%" class="text-right">Rek Masuk</th>
                <th width="8%" class="text-right">Rek Keluar</th>
                <th width="8%" class="text-right">Cash Masuk</th>
                <th width="8%" class="text-right">Cash Keluar</th>
                <th width="7%" class="text-right">Konsumen</th>
                <th width="7%" class="text-right">Agen</th>
                <th width="6%" class="text-right">Jasa Cash</th>
                <th width="6%" class="text-right">Jasa Rek</th>
                <th width="5%" class="text-center">Status</th>
                <th width="11%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $item->nomor_transaksi }}</td>
                    <td class="font-bold">{{ $item->nama_nasabah }}</td>
                    <td>{{ $item->jenis_transaksi }}</td>
                    <td class="text-right">{{ $item->rekening_masuk > 0 ? number_format($item->rekening_masuk, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->rekening_keluar > 0 ? number_format($item->rekening_keluar, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->cash_masuk > 0 ? number_format($item->cash_masuk, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ $item->cash_keluar > 0 ? number_format($item->cash_keluar, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">{{ number_format($item->harga_konsumen, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga_agen, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #059669;">{{ $item->pendapatan_jasa_cash > 0 ? number_format($item->pendapatan_jasa_cash, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold" style="color: #059669;">{{ $item->pendapatan_jasa_rekening > 0 ? number_format($item->pendapatan_jasa_rekening, 0, ',', '.') : '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $item->status_pembayaran === 'Lunas' ? 'badge-lunas' : 'badge-hutang' }}">
                            {{ $item->status_pembayaran }}
                        </span>
                    </td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada transaksi Laku Pandai tercatat untuk periode {{ $periodLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($transaksi->count() > 0)
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="5" class="text-center">TOTAL KESELURUHAN ({{ $transaksi->count() }} Transaksi)</td>
                <td class="text-right">Rp {{ number_format($summary['rekening_masuk'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['rekening_keluar'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['cash_masuk'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['cash_keluar'], 0, ',', '.') }}</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($summary['pendapatan_cash'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($summary['pendapatan_rekening'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center" style="color: #1e3a8a;">Jasa: Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</td>
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
