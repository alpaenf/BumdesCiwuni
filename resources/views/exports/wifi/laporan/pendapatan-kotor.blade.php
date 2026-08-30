<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Pendapatan Kotor WiFi - BUMDes Ciwuni</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Poppins", Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            padding: 20px;
            background-color: #f8fafc;
        }
        
        .report-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }

        /* Header Kop Surat */
        header {
            margin-bottom: 20px;
        }
        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
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
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
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
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 8px;
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

        /* Print Optimization */
        @media print {
            body {
                padding: 0;
                background-color: #ffffff;
                font-size: 10px;
            }
            .report-wrapper {
                padding: 0;
                border: none;
                box-shadow: none;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .table-responsive {
                overflow-x: visible;
                border: none;
                border-radius: 0;
            }
            .table-responsive table {
                min-width: 100% !important;
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
            background-color: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto 20px auto;
            border: 1px solid #e2e8f0;
        }

        /* Tanda Tangan */
        .ttd-container {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .ttd-box {
            text-align: center;
            width: 200px;
            font-size: 10px;
        }
        .ttd-space {
            height: 60px;
        }
    </style>
</head>
<body>

    @php
        $periodLabel = !empty($tanggal) 
            ? 'Tanggal: ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y')
            : 'Periode: ' . ($bulanNama ?? 'Semua Bulan') . ' ' . $tahun;
    @endphp

    <div class="action-bar no-print">
        <div style="font-weight: 600; font-size: 12px; color: #334155;">Preview Laporan Pendapatan Kotor WiFi</div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Cetak Dokumen
            </button>
            <button onclick="window.close()" style="background:#64748b; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-weight:600; cursor:pointer;">
                Tutup
            </button>
        </div>
    </div>

    <div class="report-wrapper">
        <!-- Header Kop Surat -->
        <header>
            <div class="kop-container">
                <img src="/logo2.png" alt="Logo BUMDes" class="kop-logo" onerror="this.src='/logo.png'" />
                <div class="kop-text">
                    <div class="kop-title-1">BADAN USAHA MILIK DESA (BUMDesa)</div>
                    <div class="kop-title-2">CIWUNI</div>
                    <div class="kop-title-3">UNIT USAHA WIFI & INTERNET DESA</div>
                    <div class="kop-subtitle">Kecamatan Kesugihan, Kabupaten Cilacap | WA: 085228357400</div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>

        <div class="report-title">LAPORAN PENDAPATAN KOTOR</div>
        <div class="report-period">{{ $periodLabel }}</div>

        <!-- Summary Grid -->
        <div class="summary-grid">
            <div class="summary-card" style="background-color: #eff6ff; border-color: #bfdbfe;">
                <div class="summary-card-title" style="color: #1e40af;">Total Pendapatan Kotor BUMDes</div>
                <div class="summary-card-value" style="color: #1e3a8a;">Rp {{ number_format($pendapatanKotor, 0, ',', '.') }}</div>
                <div class="summary-card-subtext">Total pendapatan kotor unit usaha WiFi sebelum potongan</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Rincian Sumber Pendapatan</div>
                <div style="font-size: 11px; margin-top: 4px; line-height: 1.6;">
                    <div>• Skema Persentase (9%): <strong>Rp {{ number_format($pendapatanPersentase, 0, ',', '.') }}</strong></div>
                    <div>• Skema Admin Flat: <strong>Rp {{ number_format($pendapatanAdminFlat, 0, ',', '.') }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Rincian Pengurangan Pendapatan -->
        <div class="summary-section-title">Rincian Pengurangan Pendapatan</div>
        <div class="table-responsive">
            <table>
                <colgroup>
                    <col style="width: 50%;" />
                    <col style="width: 30%;" />
                    <col style="width: 20%;" />
                </colgroup>
                <thead>
                    <tr>
                        <th>Komponen / Keterangan</th>
                        <th class="angka">Nominal (Rp)</th>
                        <th class="angka">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($distribusi as $d)
                        @php
                            $isLaba = $d['nama'] === 'Laba Bersih BUMDes';
                            $isTotal = $d['nama'] === 'Total Pengambilan';
                        @endphp
                        <tr style="{{ ($isLaba || $isTotal) ? 'font-weight: 700; background-color: ' . ($isLaba ? '#f0fdf4' : '#fef9c3') . ';' : '' }}">
                            <td>{{ $d['nama'] }}</td>
                            <td class="angka" style="{{ $isLaba ? ($d['nominal'] < 0 ? 'color:#dc2626;' : 'color:#15803d;') : '' }}">
                                Rp {{ number_format($d['nominal'], 0, ',', '.') }}
                            </td>
                            <td class="angka">{{ $d['persen'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Detail Skema Persentase -->
        <div class="summary-section-title">Detail Pendapatan Skema Persentase (9%)</div>
        <div class="table-responsive">
            <table>
                <colgroup>
                    <col style="width: 12%;" />
                    <col style="width: 28%;" />
                    <col style="width: 20%;" />
                    <col style="width: 15%;" />
                    <col style="width: 10%;" />
                    <col style="width: 15%;" />
                </colgroup>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Provider</th>
                        <th class="angka">Total Tarikan</th>
                        <th style="text-align: center;">Skema</th>
                        <th class="angka">Hak BUMDes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailPersentase as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p['tanggal'])->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $p['pelanggan'] }}</strong>
                                <div style="font-size: 8px; color: #64748b;">{{ $p['no_id_pel'] }}</div>
                            </td>
                            <td>{{ $p['provider'] }}</td>
                            <td class="angka">Rp {{ number_format($p['total_tarikan'], 0, ',', '.') }}</td>
                            <td style="text-align: center;">{{ $p['nilai_skema'] }}</td>
                            <td class="angka" style="font-weight: 600; color: #15803d;">Rp {{ number_format($p['hak_bumdes'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 12px;">Tidak ada transaksi skema persentase pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Detail Skema Admin Flat -->
        <div class="summary-section-title">Detail Pendapatan Skema Admin Flat</div>
        <div class="table-responsive">
            <table>
                <colgroup>
                    <col style="width: 12%;" />
                    <col style="width: 28%;" />
                    <col style="width: 20%;" />
                    <col style="width: 15%;" />
                    <col style="width: 10%;" />
                    <col style="width: 15%;" />
                </colgroup>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Provider</th>
                        <th class="angka">Total Tarikan</th>
                        <th style="text-align: center;">Biaya Admin</th>
                        <th class="angka">Hak BUMDes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailAdminFlat as $f)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($f['tanggal'])->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $f['pelanggan'] }}</strong>
                                <div style="font-size: 8px; color: #64748b;">{{ $f['no_id_pel'] }}</div>
                            </td>
                            <td>{{ $f['provider'] }}</td>
                            <td class="angka">Rp {{ number_format($f['total_tarikan'], 0, ',', '.') }}</td>
                            <td style="text-align: center;">{{ $f['nilai_skema'] }}</td>
                            <td class="angka" style="font-weight: 600; color: #15803d;">Rp {{ number_format($f['hak_bumdes'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 12px;">Tidak ada transaksi skema admin flat pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Lembar Tanda Tangan -->
        <div class="ttd-container">
            <div class="ttd-box">
                <p>Mengetahui,</p>
                <p style="font-weight: 700;">Direktur BUMDes Ciwuni</p>
                <div class="ttd-space"></div>
                <p style="font-weight: 700;">( ............................................ )</p>
            </div>

            <div class="ttd-box">
                <p>Ciwuni, {{ date('d F Y') }}</p>
                <p style="font-weight: 700;">Manajer Unit Usaha WiFi</p>
                <div class="ttd-space"></div>
                <p style="font-weight: 700;">( {{ $user->nama ?? 'Admin Unit' }} )</p>
            </div>
        </div>
    </div>

</body>
</html>
