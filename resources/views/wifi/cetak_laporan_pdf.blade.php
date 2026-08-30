<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Finansial WiFi Per Provider — BUMDes Ciwuni</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            background: #fff;
            padding: 10px;
        }

        /* ── KOP SURAT ─────────────────────────────────────────── */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .kop-title h1 {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-title p {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        .kop-meta {
            text-align: right;
            font-size: 8pt;
            color: #475569;
        }

        .kop-meta strong {
            color: #0f172a;
        }

        /* ── METRICS SUMMARY BOXES ──────────────────────────────── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .summary-card span {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }

        .summary-card strong {
            font-size: 12pt;
            font-weight: 900;
            color: #0f172a;
        }

        .summary-card.green strong { color: #047857; }
        .summary-card.blue strong  { color: #1d4ed8; }

        .section-title {
            font-size: 9pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 6px;
            border-left: 3px solid #2563eb;
            padding-left: 6px;
        }

        /* ── TABEL UTAMA DETAIL ─────────────────────────────────── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 15px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            text-align: left;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center !important; }
        .text-right  { text-align: right !important; white-space: nowrap !important; }
        .font-bold   { font-weight: bold !important; }
        .font-mono   { font-family: monospace, Courier, monospace !important; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
        }

        .badge-lunas     { background: #d1fae5; color: #047857; border: 1px solid #6ee7b7; }
        .badge-tunggakan { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .badge-isolir    { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-flat      { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-pct       { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── TANDA TANGAN ────────────────────────────────────────── */
        .ttd-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .ttd-box {
            width: 200px;
            text-align: center;
            font-size: 8.5pt;
        }

        .ttd-space {
            height: 50px;
        }

        .no-print-bar {
            background: #0f172a;
            color: white;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            border-radius: 6px;
        }

        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 6px 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Bar Kontrol Sebelum Print -->
    <div class="no-print-bar">
        <div>
            <strong>Preview Cetak Laporan Finansial WiFi Per Provider</strong>
            <span style="font-size:8pt; opacity:0.8; margin-left:8px;">(Orientasi Landscape A4)</span>
        </div>
        <div>
            <button onclick="window.print()" class="btn-print"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="vertical-align:middle; display:inline-block; margin-right:4px;"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg> Cetak / Simpan PDF</button>
            <button onclick="window.close()" style="background:#475569; color:white; border:none; padding:6px 10px; border-radius:4px; margin-left:4px; cursor:pointer;">Tutup</button>
        </div>
    </div>

    <!-- KOP SURAT BUMDES -->
    <div class="kop-surat">
        <div class="kop-brand">
            <img src="/logo2.png" alt="Logo WiFi" class="kop-logo" onerror="this.style.display='none'">
            <div class="kop-title">
                <h1>BUMDes Ciwuni — Unit Usaha WiFi &amp; Internet Desa</h1>
                <p>Alamat: Kantor Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap | WA: 085228357400</p>
            </div>
        </div>
        <div class="kop-meta">
            <p>Dokumen: <strong>LAPORAN REKAPITULASI PROVIDER</strong></p>
            <p>Periode: <strong>{{ ($tglDari && $tglSampai) ? 'Rentang '.$tglDari.' s/d '.$tglSampai : 'Bulan '.$bulan.' '.$tahun }}</strong></p>
            <p>Pencetak: <strong>{{ $user->nama }}</strong></p>
        </div>
    </div>

    <!-- METRICS RINGKASAN -->
    <div class="summary-grid">
        <div class="summary-card">
            <span>Total Tarikan Bruto</span>
            <strong>Rp {{ number_format($stats['total_tarikan_bruto'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-card green">
            <span>Pendapatan Bersih BUMDes</span>
            <strong>Rp {{ number_format($stats['total_hasil_bumdes'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-card blue">
            <span>Setoran Hak Provider</span>
            <strong>Rp {{ number_format($stats['total_hak_provider'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-card">
            <span>Total Pelanggan</span>
            <strong>{{ $stats['total_pelanggan'] }} <small style="font-weight:normal; font-size:8pt;">warga</small></strong>
        </div>
    </div>

    <!-- SECTION 1: REKAPITULASI PER PROVIDER -->
    <div class="section-title">I. Rekapitulasi Pembagian Hasil Per Provider / Mitra ISP</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:200px;">Provider / Mitra ISP</th>
                <th class="text-center" style="width:100px;">Skema Bagi Hasil</th>
                <th class="text-center" style="width:90px;">Pelanggan</th>
                <th class="text-right" style="width:110px;">Total Tarikan</th>
                <th class="text-right" style="width:110px;">Hasil BUMDes</th>
                <th class="text-right" style="width:110px;">Hak Provider</th>
                <th class="text-center" style="width:120px;">Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapPerProvider as $r)
            <tr>
                <td class="font-bold">{{ $r['nama_provider'] }}</td>
                <td class="text-center">
                    @if($r['tipe_bagi_hasil'] === 'FLAT_ADMIN')
                        <span class="badge badge-flat">FLAT Rp {{ number_format($r['nilai_bagi_hasil'], 0, ',', '.') }}</span>
                    @else
                        <span class="badge badge-pct">{{ $r['nilai_bagi_hasil'] }}%</span>
                    @endif
                </td>
                <td class="text-center font-bold">{{ $r['total_pelanggan'] }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($r['total_tarikan'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#047857;">Rp {{ number_format($r['total_hasil_bumdes'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#1d4ed8;">Rp {{ number_format($r['total_hak_provider'], 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="badge badge-lunas">{{ $r['lunas_count'] }} Lunas</span>
                    @if($r['tunggakan_count'] > 0)
                        <span class="badge badge-tunggakan" style="margin-left:3px;">{{ $r['tunggakan_count'] }} Tgk</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding:15px; color:#94a3b8;">
                    Tidak ada rekap provider untuk filter yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($rekapPerProvider) > 0)
        <tfoot>
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td colspan="2" class="text-right font-bold">TOTAL REKAPITULASI:</td>
                <td class="text-center font-bold">{{ $stats['total_pelanggan'] }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($stats['total_tarikan_bruto'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#047857;">Rp {{ number_format($stats['total_hasil_bumdes'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#1d4ed8;">Rp {{ number_format($stats['total_hak_provider'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- SECTION 2: RINCIAN PELANGGAN LENGKAP -->
    <div class="section-title">II. Rincian Tagihan Pelanggan Per Provider</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width:25px;">No</th>
                <th style="width:80px;">ID Pelanggan</th>
                <th>Nama Pelanggan &amp; NIK</th>
                <th style="width:85px;">No WA</th>
                <th>Alamat (RT/RW)</th>
                <th>Provider / ISP</th>
                <th style="width:70px;">Paket</th>
                <th class="text-center" style="width:55px;">Gel.</th>
                <th class="text-right" style="width:105px;">Tarikan (Rp)</th>
                <th class="text-right" style="width:100px;">Hasil BUMDes</th>
                <th class="text-right" style="width:100px;">Hak Provider</th>
                <th class="text-center" style="width:65px;">Status Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelangganList as $index => $p)
            @php $stVal = $p->current_status ?? $p->status_1_15; @endphp
            <tr>
                <td class="text-center font-mono">{{ $p->no ?? ($index + 1) }}</td>
                <td class="font-mono font-bold">{{ $p->no_id_pel ?? '-' }}</td>
                <td>
                    <div class="font-bold">{{ $p->nama }}</div>
                    @if($p->nik)
                        <div style="font-size:7pt; color:#64748b;" class="font-mono">NIK: {{ $p->nik }}</div>
                    @endif
                </td>
                <td class="font-mono">{{ $p->no_wa ?? '-' }}</td>
                <td>
                    <div>{{ $p->alamat ?? '-' }}</div>
                    <div style="font-size:7.5pt; color:#475569;">RT {{ $p->rt ?? '-' }} / RW {{ $p->rw ?? '-' }}</div>
                </td>
                <td>
                    @if($p->provider)
                        <div class="font-bold">{{ $p->provider->nama_provider }}</div>
                    @else
                        <span style="color:#94a3b8;">Umum</span>
                    @endif
                </td>
                <td class="font-mono font-bold">{{ $p->paket ?? '-' }}</td>
                <td class="text-center">
                    <span class="badge" style="background:#e0e7ff; color:#3730a3;">Tgl 1 - 10</span>
                </td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($p->total_tarikan, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#047857;">Rp {{ number_format($p->hasil_bumdes, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#1d4ed8;">Rp {{ number_format($p->total_provider, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($stVal === 'ISOLIR')
                        <span class="badge badge-isolir">ISOLIR</span>
                    @else
                        <span class="badge badge-lunas">AKTIF</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center" style="padding:15px; color:#94a3b8;">
                    Tidak ada data pelanggan untuk provider yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pelangganList) > 0)
        <tfoot>
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td colspan="8" class="text-right font-bold">TOTAL KESELURUHAN:</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($stats['total_tarikan_bruto'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#047857;">Rp {{ number_format($stats['total_hasil_bumdes'], 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold" style="color:#1d4ed8;">Rp {{ number_format($stats['total_hak_provider'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- LEMBAR PENGESAHAN TANDA TANGAN -->
    <div class="ttd-section">
        <div class="ttd-box">
            <p>Mengetahui,</p>
            <p><strong>Direktur BUMDes Ciwuni</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( ............................................ )</strong></p>
        </div>

        <div class="ttd-box">
            <p>Ciwuni, {{ date('d F Y') }}</p>
            <p><strong>Manajer Unit Usaha WiFi</strong></p>
            <div class="ttd-space"></div>
            <p><strong>( {{ $user->nama }} )</strong></p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
