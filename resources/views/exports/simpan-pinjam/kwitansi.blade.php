<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi - {{ $kwitansi->nomor_kwitansi }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', Arial, sans-serif; 
            font-size: 12px; 
            padding: 20px; 
            background: #f8fafc; 
            color: #000000;
        }
        
        .kwitansi-wrapper {
            max-width: 700px; 
            margin: 0 auto;
        }

        .kwitansi { 
            background: #ffffff;
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden; 
            border: 2px solid #000000;
            position: relative;
        }

        /* Kop / Header - Monochrome style */
        .header { 
            background: #000000; 
            color: #ffffff; 
            padding: 20px; 
            display: flex; 
            flex-direction: row;
            align-items: center; 
            gap: 15px;
        }
        
        .logo-container {
            background: #ffffff;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .logo-img {
            height: 55px;
            width: auto;
        }

        .header-text {
            flex-grow: 1;
        }

        .header-text h1 { 
            font-size: 14px; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
            color: #ffffff;
        }
        
        .header-text h2 {
            font-size: 11px;
            font-weight: 600;
            color: #cccccc;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .header-text p { 
            font-size: 9px; 
            opacity: 0.85; 
            margin-top: 3px; 
            line-height: 1.3;
        }

        .header-right { 
            text-align: right; 
            flex-shrink: 0;
        }

        .header-right .nomor { 
            font-size: 11px; 
            font-weight: 700; 
            background: rgba(255, 255, 255, 0.15);
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-family: monospace;
            display: inline-block;
            color: #ffffff;
        }

        .header-right .tanggal {
            font-size: 10px;
            opacity: 0.9;
            margin-top: 6px;
        }

        .body { 
            padding: 24px; 
            position: relative;
            z-index: 1;
        }

        .title { 
            font-size: 16px; 
            font-weight: 700; 
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            margin-bottom: 20px; 
            color: #000000; 
            border-bottom: 2px solid #000000;
            padding-bottom: 4px;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
            position: relative;
        }

        /* Detail Fields - Grid style for perfect alignment */
        .fields-container {
            margin-bottom: 15px;
        }

        .field { 
            display: grid;
            grid-template-columns: 140px 15px 1fr;
            margin-bottom: 10px; 
            align-items: baseline; 
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 6px;
        }
        
        .field-label { 
            color: #475569; 
            font-size: 11px; 
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .field-colon { 
            color: #000000;
            font-weight: bold;
        }
        
        .field-value { 
            font-weight: 500; 
            color: #000000;
            font-size: 12px;
        }

        /* Nominal Box - Monochrome style */
        .nominal-box { 
            background: #f8fafc; 
            border: 1px dashed #000000; 
            border-radius: 6px; 
            padding: 14px; 
            margin: 15px 0; 
        }
        
        .nominal-label { 
            font-size: 9px; 
            color: #475569; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 600;
        }
        
        .nominal-value { 
            font-size: 22px; 
            font-weight: 700; 
            color: #000000; 
            margin-top: 2px; 
        }
        
        .terbilang { 
            font-style: italic; 
            font-size: 11px; 
            color: #000000; 
            margin-top: 4px; 
            background: #ffffff;
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            display: inline-block;
            width: 100%;
        }

        /* Footer / Tanda Tangan */
        .footer { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            margin-top: 30px; 
            padding-top: 15px; 
            border-top: 1px solid #000000; 
        }
        
        .sign-box { 
            text-align: center; 
            width: 180px;
        }
        
        .sign-box .title-sign { 
            font-size: 10px; 
            color: #475569; 
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 50px;
        }
        
        .sign-name { 
            font-size: 11px; 
            font-weight: 700; 
            color: #000000;
            border-top: 1px solid #000000; 
            padding-top: 4px; 
            display: inline-block;
            width: 100%;
        }

        .watermark { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%) rotate(-25deg); 
            font-size: 80px; 
            color: rgba(0, 0, 0, 0.02); 
            font-weight: 800; 
            pointer-events: none; 
            z-index: 0; 
            text-transform: uppercase;
            letter-spacing: 5px;
        }

        .action-bar { 
            margin-top: 20px; 
            text-align: center; 
        }

        .btn-print {
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn-print:hover {
            background-color: #1e293b;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 10px;
                background: #ffffff;
            }
            .kwitansi {
                box-shadow: none;
                border-radius: 0;
            }
            .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 10px;
                padding: 15px;
            }
            .header-right {
                text-align: center;
            }
            .field {
                grid-template-columns: 110px 10px 1fr;
            }
            .footer {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            .sign-box {
                width: 100%;
                max-width: 200px;
            }
        }

        @media print {
            body { 
                padding: 0; 
                background: white;
            }
            .kwitansi {
                box-shadow: none;
                border-radius: 0;
                border: 2px solid #000000;
            }
            .action-bar { 
                display: none; 
            }
        }
    </style>
</head>
<body>
    
    <div class="kwitansi-wrapper">
        <div class="kwitansi">
            <div class="watermark">BUMDES</div>
            
            <div class="header">
                <div class="logo-container">
                    <img class="logo-img" src="{{ asset('logo.png') }}" alt="Logo BUMDes">
                </div>
                <div class="header-text">
                    <h1>BUMDesa Dammar Wulan</h1>
                    <h2>Unit Simpan Pinjam</h2>
                    <p>Desa Ciwuni, Kec. Kesugihan, Kab. Cilacap<br>Alamat: Jl. Pasar Jagang RT 1 RW 4 Ciwuni | Email: bumdesciwuni@gmail.com</p>
                </div>
                <div class="header-right">
                    <div class="nomor">{{ $kwitansi->nomor_kwitansi }}</div>
                    <div class="tanggal">{{ \Carbon\Carbon::parse($kwitansi->tanggal)->isoFormat('D MMMM Y') }}</div>
                </div>
            </div>

            <div class="body">
                <div class="title">Kwitansi</div>

                <div class="fields-container">
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
                </div>

                <div class="nominal-box">
                    <div class="nominal-label">Uang sejumlah</div>
                    <div class="nominal-value">Rp {{ number_format($kwitansi->nominal, 0, ',', '.') }}</div>
                    <div class="terbilang">{{ $terbilang }} rupiah</div>
                </div>

                <div class="field" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">
                    <span class="field-label">Untuk keperluan</span>
                    <span class="field-colon">:</span>
                    <span class="field-value" style="font-weight: 600;">{{ $kwitansi->keperluan }}</span>
                </div>

                <div class="footer">
                    <div class="sign-box">
                        <div class="title-sign">Yang membayar,</div>
                        <div class="sign-name">{{ $kwitansi->nasabah->nama }}</div>
                    </div>
                    
                    <div class="sign-box">
                        <div class="title-sign">Bendahara BUMDes,</div>
                        <div class="sign-name">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-bar">
            <button onclick="window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Cetak Kwitansi
            </button>
        </div>
    </div>

</body>
</html>
