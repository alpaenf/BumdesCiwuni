<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi - {{ $kwitansi->nomor_kwitansi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', Arial, sans-serif; font-size: 13px; padding: 30px; background: #fff; }
        .kwitansi { max-width: 700px; margin: 0 auto; border: 2px solid #004c22; border-radius: 8px; overflow: hidden; }
        .header { background: #004c22; color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 11px; opacity: 0.85; margin-top: 2px; }
        .header-right { text-align: right; }
        .header-right .nomor { font-size: 13px; font-weight: bold; }
        .body { padding: 20px 24px; }
        .title { font-size: 16px; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; color: #004c22; }
        .field { display: flex; gap: 0; margin-bottom: 10px; align-items: baseline; }
        .field-label { min-width: 150px; color: #555; font-size: 12px; }
        .field-colon { margin-right: 8px; }
        .field-value { font-weight: 500; flex: 1; }
        .nominal-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px 16px; margin: 16px 0; }
        .nominal-label { font-size: 11px; color: #166534; text-transform: uppercase; letter-spacing: 1px; }
        .nominal-value { font-size: 20px; font-weight: bold; color: #15803d; margin-top: 2px; }
        .terbilang { font-style: italic; font-size: 12px; color: #166534; margin-top: 4px; text-transform: capitalize; }
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px dashed #ccc; }
        .sign-box { text-align: center; }
        .sign-box .title-sign { font-size: 12px; color: #555; }
        .sign-space { height: 60px; }
        .sign-name { font-size: 12px; font-weight: bold; border-top: 1px solid #333; padding-top: 4px; margin-top: 4px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 80px; color: rgba(0,76,34,0.05); font-weight: bold; pointer-events: none; z-index: 0; }
        .no-print { margin-top: 20px; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="watermark">BUMDes</div>

    <div class="kwitansi">
        <div class="header">
            <div>
                <h1>BUMDes Dammar Wulan</h1>
                <p>Unit Simpan Pinjam | Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap</p>
            </div>
            <div class="header-right">
                <div class="nomor">{{ $kwitansi->nomor_kwitansi }}</div>
                <div style="font-size: 11px; opacity: 0.85;">{{ \Carbon\Carbon::parse($kwitansi->tanggal)->isoFormat('D MMMM Y') }}</div>
            </div>
        </div>
        <div class="body">
            <div class="title">Kwitansi</div>

            <div class="field">
                <span class="field-label">Telah diterima dari</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $kwitansi->nasabah->nama }}</span>
            </div>
            <div class="field">
                <span class="field-label">No. Rekening</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $kwitansi->nasabah->nomor_rekening }}</span>
            </div>
            <div class="field">
                <span class="field-label">Alamat</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $kwitansi->nasabah->alamat ?? '-' }}</span>
            </div>

            <div class="nominal-box">
                <div class="nominal-label">Uang sejumlah</div>
                <div class="nominal-value">Rp {{ number_format($kwitansi->nominal, 0, ',', '.') }}</div>
                <div class="terbilang">{{ $terbilang }} rupiah</div>
            </div>

            <div class="field">
                <span class="field-label">Untuk keperluan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $kwitansi->keperluan }}</span>
            </div>

            <div class="footer">
                <div class="sign-box">
                    <div class="title-sign">Yang membayar,</div>
                    <div class="sign-space"></div>
                    <div class="sign-name">{{ $kwitansi->nasabah->nama }}</div>
                </div>
                <div style="text-align: center; font-size: 11px; color: #555;">
                    <div>Ciwuni, {{ \Carbon\Carbon::parse($kwitansi->tanggal)->isoFormat('D MMMM Y') }}</div>
                </div>
                <div class="sign-box">
                    <div class="title-sign">Bendahara BUMDes,</div>
                    <div class="sign-space"></div>
                    <div class="sign-name">________________________</div>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 24px; background: #004c22; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
            Print Kwitansi
        </button>
    </div>

    <script>window.onload = () => window.print();</script>
</body>
</html>
