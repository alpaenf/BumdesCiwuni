<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Umum & PPOB - {{ $periodLabel }}</title>
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
            border-bottom: 2.5px solid #4f46e5;
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
            color: #4f46e5;
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
            width: 20%;
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
            background-color: #eef2ff;
            border-color: #c7d2fe;
        }
        .summary-cell.highlight .value {
            color: #4338ca;
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
            background-color: #4338ca;
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
        .badge-succes {
            color: #15803d;
            font-weight: bold;
        }
        .badge-pending {
            color: #b45309;
            font-weight: bold;
        }
        .badge-cancel {
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
        <h3>UNIT PERDAGANGAN BESAR — TRANSAKSI UMUM & LAYANAN PPOB</h3>
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
                        <td>: <strong style="color: #4338ca;">{{ $periodLabel }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Total Transaksi</strong></td>
                        <td>: {{ $summary['total_transaksi'] }} transaksi ({{ $summary['total_sukses'] }} sukses)</td>
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
                        <td>: Perdagangan Besar (PPOB & Transaksi Umum)</td>
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
                <div class="label">Total Biaya Pokok</div>
                <div class="value">Rp {{ number_format($summary['total_biaya_pokok'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Biaya Admin Bank</div>
                <div class="value">Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="label">Total Pembayaran Pelanggan</div>
                <div class="value" style="color: #2563eb;">Rp {{ number_format($summary['total_bayar'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell highlight" colspan="2">
                <div class="label">PENDAPATAN JASA AGEN BUMDES</div>
                <div class="value" style="font-size: 13px;">Rp {{ number_format($summary['total_jasa_agen'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="8%">Tanggal</th>
                <th width="12%">No Transaksi</th>
                <th width="14%">Nama Pelanggan</th>
                <th width="11%">Layanan Transaksi</th>
                <th width="12%">Nomor Tujuan</th>
                <th width="8%" class="text-right">Biaya Pokok</th>
                <th width="6%" class="text-right">Admin</th>
                <th width="8%" class="text-right">Jasa Agen</th>
                <th width="8%" class="text-right">Total Bayar</th>
                <th width="5%" class="text-center">Metode</th>
                <th width="5%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }} {{ $item->waktu ? substr($item->waktu, 0, 5) : '' }}</td>
                    <td>{{ $item->nomor_transaksi }}</td>
                    <td class="font-bold">{{ $item->nama_pelanggan }}</td>
                    <td class="font-bold">{{ $item->jenis_transaksi }}</td>
                    <td>{{ $item->nomor_tujuan }}</td>
                    <td class="text-right">{{ number_format($item->biaya_pokok, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $item->biaya_admin > 0 ? number_format($item->biaya_admin, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold" style="color: #059669;">{{ number_format($item->biaya_jasa_agen, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->metode_pembayaran }}</td>
                    <td class="text-center">
                        <span class="{{ $item->status === 'SUCCES' ? 'badge-succes' : ($item->status === 'PENDING' ? 'badge-pending' : 'badge-cancel') }}">
                            {{ $item->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada catatan transaksi umum/PPOB untuk periode {{ $periodLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($transaksi->count() > 0)
        <tfoot>
            <tr style="background-color: #eef2ff; font-weight: bold;">
                <td colspan="6" class="text-center">TOTAL KESELURUHAN ({{ $transaksi->count() }} Transaksi)</td>
                <td class="text-right">Rp {{ number_format($summary['total_biaya_pokok'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($summary['total_jasa_agen'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #1e3a8a;">Rp {{ number_format($summary['total_bayar'], 0, ',', '.') }}</td>
                <td colspan="2" class="text-center">-</td>
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
