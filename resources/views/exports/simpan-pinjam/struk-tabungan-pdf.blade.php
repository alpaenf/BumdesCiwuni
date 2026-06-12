<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Tabungan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000000;
            background: #ffffff;
            width: 100%;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .logo {
            text-align: center;
            margin-bottom: 4px;
        }
        .logo img {
            width: 36px;
            height: auto;
        }
        .org-name {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .org-bumdes {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .org-detail {
            font-size: 6.5pt;
            line-height: 1.4;
        }
        hr.solid {
            border: none;
            border-top: 1px solid #000;
            margin: 5px 0;
        }
        hr.double {
            border: none;
            border-top: 3px double #000;
            margin: 5px 0;
        }
        .title {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 5px 0;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            line-height: 1.6;
        }
        table.info td.label {
            width: 95px;
            vertical-align: top;
        }
        table.info td.colon {
            width: 10px;
            vertical-align: top;
        }
        table.info td.value {
            vertical-align: top;
            word-break: break-word;
        }
        .jenis-badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 0 4px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .footer-note {
            text-align: center;
            font-size: 7pt;
            margin-top: 8px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    @php
        $nasabah     = $transaksi->tabungan->nasabah;
        $isSetor     = $transaksi->jenis_transaksi === \App\Models\TransaksiTabungan::JENIS_SETOR;
        $nominal     = (float) $transaksi->nominal;
        $administrasi = (float) $transaksi->administrasi;
        $saldoAkhir  = (float) $transaksi->saldo_setelah;

        if ($isSetor) {
            $saldoAwal   = $saldoAkhir - $nominal;
            $tabungan    = $nominal;
            $pengambilan = 0;
        } else {
            $saldoAwal   = $saldoAkhir + $nominal + $administrasi;
            $tabungan    = 0;
            $pengambilan = $nominal;
        }
    @endphp

    {{-- ===== HEADER ===== --}}
    <div class="logo">
        <img src="{{ public_path('logo2.png') }}" alt="Logo BUMDes">
    </div>
    <div class="center org-name">Badan Usaha Milik Desa (BUMDesa)</div>
    <div class="center org-bumdes">DAMMAR WULAN</div>
    <div class="center org-name">DESA CIWUNI</div>
    <div class="center org-detail">Kecamatan Kesugihan Kabupaten Cilacap</div>
    <div class="center org-detail">Jl. Pasar Jagang RT 1 RW 4 Ciwuni</div>
    <div class="center org-detail">bumdesciwuni@gmail.com</div>

    <hr class="double">

    <div class="title">Struk Transaksi Tabungan</div>
    <div class="center" style="font-size:7.5pt; margin-bottom:3px;">
        <span class="jenis-badge">{{ $isSetor ? 'SETOR' : 'TARIK' }}</span>
    </div>

    <hr class="solid">

    {{-- ===== CUSTOMER INFO ===== --}}
    <table class="info">
        <tr>
            <td class="label">NOMER REKENING</td>
            <td class="colon">:</td>
            <td class="value">{{ $nasabah->nomor_rekening }}</td>
        </tr>
        <tr>
            <td class="label">NAMA</td>
            <td class="colon">:</td>
            <td class="value" style="text-transform:uppercase;">{{ $nasabah->nama }}</td>
        </tr>
        <tr>
            <td class="label">ALAMAT</td>
            <td class="colon">:</td>
            <td class="value">{{ $nasabah->alamat }}</td>
        </tr>
        <tr>
            <td class="label">NOMER WA</td>
            <td class="colon">:</td>
            <td class="value">{{ $nasabah->no_hp }}</td>
        </tr>
    </table>

    <hr class="solid">

    {{-- ===== TRANSACTION META ===== --}}
    <table class="info">
        <tr>
            <td class="label">NO. TRANSAKSI</td>
            <td class="colon">:</td>
            <td class="value">#{{ $transaksi->nomor_transaksi }}</td>
        </tr>
        <tr>
            <td class="label">TANGGAL</td>
            <td class="colon">:</td>
            <td class="value">{{ \Carbon\Carbon::parse($transaksi->tanggal)->isoFormat('D MMMM Y') }}</td>
        </tr>
    </table>

    <hr class="solid">

    {{-- ===== FINANCIAL DETAILS ===== --}}
    <table class="info">
        <tr>
            <td class="label">Saldo Awal</td>
            <td class="colon">:</td>
            <td class="value">{{ $saldoAwal == 0 ? 'Rp. 0' : 'Rp. ' . number_format($saldoAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Tabungan</td>
            <td class="colon">:</td>
            <td class="value">{{ $tabungan == 0 ? 'Rp. 0' : 'Rp. ' . number_format($tabungan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Pengambilan</td>
            <td class="colon">:</td>
            <td class="value">{{ $pengambilan == 0 ? 'Rp. 0' : 'Rp. ' . number_format($pengambilan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Administrasi</td>
            <td class="colon">:</td>
            <td class="value">{{ $administrasi == 0 ? 'Rp. 0' : 'Rp. ' . number_format($administrasi, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label bold">Saldo Akhir</td>
            <td class="colon bold">:</td>
            <td class="value bold">{{ $saldoAkhir == 0 ? 'Rp. 0' : 'Rp. ' . number_format($saldoAkhir, 0, ',', '.') }}</td>
        </tr>
    </table>

    <hr class="double">

    <div class="footer-note">
        Terima kasih atas kepercayaan Anda.<br>
        Simpan struk ini sebagai bukti transaksi.<br>
        Dicetak: {{ \Carbon\Carbon::now()->isoFormat('D MMM Y, HH:mm') }}
    </div>

</body>
</html>
