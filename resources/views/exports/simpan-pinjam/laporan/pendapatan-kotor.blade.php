<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Pendapatan Kotor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Poppins", Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            padding: 30px;
            background-color: #ffffff;
        }
        
        /* Header Kop Surat */
        header {
            margin-bottom: 20px;
        }
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            text-align: center;
        }
        .kop-logo {
            height: 65px;
            width: auto;
        }
        .kop-text {
            flex-grow: 1;
        }
        .kop-title-1 {
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .kop-title-2 {
            font-weight: 800;
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1e3a8a;
            margin: 2px 0;
        }
        .kop-title-3 {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            color: #2563eb;
            letter-spacing: 0.5px;
        }
        .kop-subtitle {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .header-underline {
            border-bottom: 3px double #0f172a;
            margin-top: 12px;
            margin-bottom: 20px;
        }

        /* Laporan Title */
        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .report-period {
            text-align: center;
            font-size: 11px;
            color: #475569;
            margin-bottom: 20px;
        }

        /* Summary Cards / Blocks */
        .summary-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e3a8a;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 25px;
            margin-bottom: 10px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            background-color: #f8fafc;
        }
        .summary-card-title {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .summary-card-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        .summary-card-subtext {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 7px 8px;
            font-size: 10px;
            text-align: left;
            word-break: break-word;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #334155;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        td.angka, th.angka {
            text-align: right;
            white-space: nowrap;
        }
        tr.highlight-row {
            background-color: #f8fafc;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef9c3; color: #a16207; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }

        /* Print Optimization */
        @media print {
            body {
                padding: 15px;
                font-size: 10px;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
        
        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }
        .action-bar {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    
    <!-- Action Bar (No Print) -->
    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Cetak Laporan PDF
        </button>
    </div>

    <!-- Kop Surat -->
    <header>
        <div class="kop-container">
            <img class="kop-logo" src="{{ asset('logo.png') }}" alt="Logo BUMDes">
            <div class="kop-text">
                <div class="kop-title-1">Badan Usaha Milik Desa (BUMDesa)</div>
                <div class="kop-title-2">Dammar Wulan</div>
                <div class="kop-title-3">Unit Simpan Pinjam</div>
                <div class="kop-subtitle">Desa Ciwuni Kecamatan Kesugihan Kabupaten Cilacap</div>
                <div class="kop-subtitle" style="font-size: 8px; margin-top: 1px;">Alamat : Jl. Pasar Jagang RT 1 RW 4 Ciwuni | Email: bumdesciwuni@gmail.com</div>
            </div>
        </div>
        <div class="header-underline"></div>
    </header>

    <!-- Judul Laporan -->
    <h1 class="report-title">Laporan Pendapatan Kotor</h1>
    <div class="report-period">Periode: {{ $bulanNama }} {{ $tahun }}</div>

    <!-- Ringkasan Pendapatan -->
    <div class="summary-section-title">Ringkasan Pendapatan</div>
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-card-title">Total Pendapatan Kotor</div>
            <div class="summary-card-value" style="color: #1e3a8a;">Rp{{ number_format($pendapatanKotor, 0, ',', '.') }}</div>
            <div class="summary-card-subtext">Akumulasi bunga pinjaman dikurangi biaya promosi</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Bunga Pinjaman (Pendapatan)</div>
            <div class="summary-card-value">Rp{{ number_format($bungaPinjaman, 0, ',', '.') }}</div>
            <div class="summary-card-subtext">Total pendapatan bunga dari akad pinjaman</div>
        </div>
    </div>

    <!-- Pengurangan Pendapatan -->
    <div class="summary-section-title">Rincian Pengurangan Pendapatan</div>
    <table>
        <thead>
            <tr>
                <th>Nama Pengurangan / Biaya</th>
                <th class="angka">Nominal</th>
                <th class="angka">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distribusi as $item)
                <tr class="{{ $item['nama'] === 'Laba Bersih' ? 'highlight-row' : '' }}">
                    <td style="{{ $item['nama'] === 'Laba Bersih' ? 'font-weight: 700;' : '' }}">{{ $item['nama'] }}</td>
                    <td class="angka" style="{{ $item['nama'] === 'Laba Bersih' && $item['nominal'] < 0 ? 'color: #ef4444; font-weight: 700;' : ($item['nama'] === 'Laba Bersih' ? 'color: #15803d; font-weight: 700;' : '') }}">
                        Rp{{ number_format($item['nominal'], 0, ',', '.') }}
                    </td>
                    <td class="angka" style="{{ $item['nama'] === 'Laba Bersih' ? 'font-weight: 700;' : '' }}">{{ $item['persen'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Kop Surat Halaman Kedua -->
    <header class="no-print-page2" style="margin-top: 15px;">
        <div class="kop-container">
            <img class="kop-logo" style="height: 45px;" src="{{ asset('logo.png') }}" alt="Logo BUMDes">
            <div class="kop-text">
                <div class="kop-title-1" style="font-size: 11px;">BUMDesa Dammar Wulan - Unit Simpan Pinjam</div>
                <div class="kop-subtitle" style="font-size: 7px;">Lampiran Detail Transaksi Pendapatan Periode {{ $bulanNama }} {{ $tahun }}</div>
            </div>
        </div>
        <div style="border-bottom: 2px solid #0f172a; margin-top: 8px; margin-bottom: 15px;"></div>
    </header>

    <!-- Detail Bunga Pinjaman -->
    <div class="summary-section-title" style="margin-top: 10px;">Detail Bunga Pinjaman</div>
    <table>
        <colgroup>
            <col style="width: 15%;" />
            <col style="width: 30%;" />
            <col style="width: 20%;" />
            <col style="width: 15%;" />
            <col style="width: 20%;" />
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Nasabah</th>
                <th class="angka">Pokok Pinjaman</th>
                <th class="angka">Bunga (%)</th>
                <th class="angka">Bunga Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailPinjaman as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $p['nasabah'] }}</td>
                    <td class="angka">Rp{{ number_format($p['pokok'], 0, ',', '.') }}</td>
                    <td class="angka">{{ $p['bunga_persen'] }}%</td>
                    <td class="angka">Rp{{ number_format($p['bunga_nominal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 12px;">Tidak ada transaksi pinjaman pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detail Biaya Promosi Reguler -->
    <div class="summary-section-title">Detail Biaya Promosi (Tabungan Reguler)</div>
    <table>
        <colgroup>
            <col style="width: 15%;" />
            <col style="width: 35%;" />
            <col style="width: 30%;" />
            <col style="width: 20%;" />
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Nasabah</th>
                <th>Keterangan</th>
                <th class="angka">Biaya Promosi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailTabungan as $t)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $t['nasabah'] }}</td>
                    <td>{{ $t['keterangan'] ?: 'Biaya Administrasi' }}</td>
                    <td class="angka">Rp{{ number_format($t['laba'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 12px;">Tidak ada transaksi tabungan reguler pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detail Biaya Promosi Sembako -->
    <div class="summary-section-title">Detail Biaya Promosi (Tabungan Sembako)</div>
    <table>
        <colgroup>
            <col style="width: 15%;" />
            <col style="width: 35%;" />
            <col style="width: 30%;" />
            <col style="width: 20%;" />
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Nasabah</th>
                <th>Keterangan</th>
                <th class="angka">Biaya Promosi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailSembako as $s)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($s['tanggal'])->format('d/m/Y') }}</td>
                    <td>{{ $s['nasabah'] }}</td>
                    <td>{{ $s['keterangan'] ?: 'Biaya Administrasi' }}</td>
                    <td class="angka">Rp{{ number_format($s['laba'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 12px;">Tidak ada transaksi tabungan sembako pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
