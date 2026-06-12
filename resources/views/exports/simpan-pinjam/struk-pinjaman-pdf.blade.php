<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Pinjaman</title>
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
        .small {
            font-size: 7.5pt;
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
        hr.dashed {
            border: none;
            border-top: 1px dashed #000;
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
            line-height: 1.5;
        }
        table.info td.label {
            width: 90px;
            vertical-align: top;
            font-weight: normal;
        }
        table.info td.colon {
            width: 10px;
            vertical-align: top;
        }
        table.info td.value {
            vertical-align: top;
            word-break: break-word;
        }
        .amount-row {
            font-weight: bold;
        }
        .lunas-badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 0 4px;
            font-size: 7pt;
            font-weight: bold;
        }
        .italic-note {
            font-style: italic;
            font-size: 7pt;
            color: #333;
        }
        .footer-note {
            text-align: center;
            font-size: 7pt;
            margin-top: 8px;
            line-height: 1.5;
        }
        .sisa-lunas {
            font-weight: bold;
        }
    </style>
</head>
<body>

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

    <div class="title">Struk Transaksi Pinjaman</div>

    <hr class="solid">

    {{-- ===== CUSTOMER INFO ===== --}}
    <table class="info">
        <tr>
            <td class="label">NOMER REKENING</td>
            <td class="colon">:</td>
            <td class="value">{{ $angsuran->pinjaman->nasabah->nomor_rekening }}</td>
        </tr>
        <tr>
            <td class="label">NAMA</td>
            <td class="colon">:</td>
            <td class="value" style="text-transform:uppercase;">{{ $angsuran->pinjaman->nasabah->nama }}</td>
        </tr>
        <tr>
            <td class="label">ALAMAT</td>
            <td class="colon">:</td>
            <td class="value">{{ $angsuran->pinjaman->nasabah->alamat }}</td>
        </tr>
        <tr>
            <td class="label">NOMER WA</td>
            <td class="colon">:</td>
            <td class="value">{{ $angsuran->pinjaman->nasabah->no_hp }}</td>
        </tr>
    </table>

    <hr class="solid">

    {{-- ===== TRANSACTION META ===== --}}
    <table class="info">
        <tr>
            <td class="label">NO. TRANSAKSI</td>
            <td class="colon">:</td>
            <td class="value">#{{ $angsuran->nomor_transaksi ?: 'St.' . sprintf('%04d', $angsuran->id) }}</td>
        </tr>
        <tr>
            <td class="label">TANGGAL</td>
            <td class="colon">:</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($angsuran->tanggal)->isoFormat('D MMMM Y') }}
                @if($angsuran->pasaran)
                    ({{ ucfirst($angsuran->pasaran) }})
                @endif
            </td>
        </tr>
    </table>

    <hr class="solid">

    {{-- ===== FINANCIAL DETAILS ===== --}}
    @php
        $p = $angsuran->pinjaman;
        $t = $p->total_tagihan > 0 ? $p->total_tagihan : 1;
        $porsiPokok = ($p->pinjaman_pokok / $t) * $angsuran->jumlah_bayar;
        $porsiBunga = (($p->pinjaman_pokok * $p->bunga / 100) / $t) * $angsuran->jumlah_bayar;
        $porsiBiaya = ($p->biaya_tambahan / $t) * $angsuran->jumlah_bayar;
    @endphp
    <table class="info">
        <tr>
            <td class="label">Pinjaman + Bunga</td>
            <td class="colon">:</td>
            <td class="value">Rp. {{ number_format($p->pinjaman_pokok + ($p->pinjaman_pokok * $p->bunga / 100), 0, ',', '.') }}</td>
        </tr>
        @if($p->biaya_tambahan > 0)
        <tr>
            <td class="label">Biaya Tambahan</td>
            <td class="colon">:</td>
            <td class="value">Rp. {{ number_format($p->biaya_tambahan, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Total Tagihan</td>
            <td class="colon">:</td>
            <td class="value bold">Rp. {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Setoran ke</td>
            <td class="colon">:</td>
            <td class="value">{{ $angsuran->angsuran_ke }} / {{ $p->jumlah_angsuran }}</td>
        </tr>
        <tr>
            <td class="label bold">Jumlah Setoran</td>
            <td class="colon bold">:</td>
            <td class="value bold">Rp. {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label italic-note">&nbsp;&nbsp;- Pokok</td>
            <td class="colon italic-note">:</td>
            <td class="value italic-note">Rp. {{ number_format($porsiPokok, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label italic-note">&nbsp;&nbsp;- Bunga</td>
            <td class="colon italic-note">:</td>
            <td class="value italic-note">Rp. {{ number_format($porsiBunga, 0, ',', '.') }}</td>
        </tr>
        @if($p->biaya_tambahan > 0)
        <tr>
            <td class="label italic-note">&nbsp;&nbsp;- Biaya Tambahan</td>
            <td class="colon italic-note">:</td>
            <td class="value italic-note">Rp. {{ number_format($porsiBiaya, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <hr class="dashed">

    <table class="info">
        <tr>
            <td class="label">Sisa Pinjaman</td>
            <td class="colon">:</td>
            <td class="value sisa-lunas">
                Rp. {{ number_format($angsuran->sisa_pinjaman, 0, ',', '.') }}
                @if($angsuran->sisa_pinjaman <= 0)
                    <span class="lunas-badge">LUNAS</span>
                @endif
            </td>
        </tr>
    </table>

    <hr class="double">

    <div class="footer-note">
        Terima kasih atas kepercayaan Anda.<br>
        Simpan struk ini sebagai bukti pembayaran.<br>
        Dicetak: {{ \Carbon\Carbon::now()->isoFormat('D MMM Y, HH:mm') }}
    </div>

</body>
</html>
