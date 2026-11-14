<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Unit Perdagangan Besar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
            color: #555;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .meta-table td {
            padding: 2px 4px;
            font-size: 10px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.data th, table.data td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
        }
        table.data th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 10px;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer td {
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>BUMDES DAMMAR WULAN — DESA CIWUNI</h2>
        <p>UNIT PERDAGANGAN BESAR (Laku Pandai, Samsat Budiman, PBB, Transaksi Umum)</p>
        <p><strong>REKAPITULASI LAPORAN TRANSAKSI & PENDAPATAN JASA</strong></p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Periode</strong></td>
            <td width="35%">: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
            <td width="15%"><strong>Dicetak Pada</strong></td>
            <td width="35%">: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <div class="section-title">1. Ringkasan Pendapatan Jasa Per Layanan</div>
    <table class="data">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th>Nama Layanan</th>
                <th class="text-center" width="15%">Jml Transaksi</th>
                <th class="text-right" width="25%">Total Volume (Rp)</th>
                <th class="text-right" width="25%">Pendapatan Jasa BUMDes (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totTrx = $lpTrx->count() + $samsatTrx->count() + $pbbTrx->count() + $umumTrx->count();
                $lpJasa = $lpTrx->sum('pendapatan_jasa_cash') + $lpTrx->sum('pendapatan_jasa_rekening');
                $samsatJasa = $samsatTrx->sum('biaya_jasa_bumdes');
                $pbbJasa = $pbbTrx->sum('biaya_admin');
                $umumJasa = $umumTrx->sum('biaya_jasa_agen');
                $totJasa = $lpJasa + $samsatJasa + $pbbJasa + $umumJasa;
                $totBiaya = $biayaOperasional->sum('nominal');
            @endphp
            <tr>
                <td class="text-center">1</td>
                <td>Layanan Laku Pandai</td>
                <td class="text-center">{{ number_format($lpTrx->count()) }}</td>
                <td class="text-right">{{ number_format($lpTrx->sum('cash_masuk') + $lpTrx->sum('rekening_masuk'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($lpJasa, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Samsat Budiman (Pajak Kendaraan)</td>
                <td class="text-center">{{ number_format($samsatTrx->count()) }}</td>
                <td class="text-right">{{ number_format($samsatTrx->sum('total_pembayaran'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($samsatJasa, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Pajak Bumi dan Bangunan (PBB)</td>
                <td class="text-center">{{ number_format($pbbTrx->count()) }}</td>
                <td class="text-right">{{ number_format($pbbTrx->sum('total_bayar'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($pbbJasa, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Transaksi Umum & PPOB</td>
                <td class="text-center">{{ number_format($umumTrx->count()) }}</td>
                <td class="text-right">{{ number_format($umumTrx->sum('total_bayar'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($umumJasa, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f8fafc;">
                <td colspan="2" class="text-center">TOTAL PENDAPATAN KOTOR JASA</td>
                <td class="text-center">{{ number_format($totTrx) }}</td>
                <td class="text-right">-</td>
                <td class="text-right">Rp {{ number_format($totJasa, 0, ',', '.') }}</td>
            </tr>
            <tr class="font-bold" style="color: #dc2626;">
                <td colspan="4" class="text-center">TOTAL BEBAN BIAYA OPERASIONAL UNIT</td>
                <td class="text-right">(Rp {{ number_format($totBiaya, 0, ',', '.') }})</td>
            </tr>
            <tr class="font-bold" style="background-color: #ecfdf5; color: #047857; font-size: 11px;">
                <td colspan="4" class="text-center">LABA BERSIH OPERASIONAL UNIT PERDAGANGAN</td>
                <td class="text-right">Rp {{ number_format($totJasa - $totBiaya, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td width="50%">
                Mengetahui,<br>
                <strong>Direktur BUMDes Dammar Wulan</strong>
                <br><br><br><br>
                ( .................................................. )
            </td>
            <td width="50%">
                Ciwuni, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Admin Unit Perdagangan Besar</strong>
                <br><br><br><br>
                ( <strong>{{ auth()->user()->nama ?? 'Admin Unit' }}</strong> )
            </td>
        </tr>
    </table>
</body>
</html>

