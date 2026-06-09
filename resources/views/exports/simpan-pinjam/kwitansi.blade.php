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
            background: #f1f5f9; 
            color: #1e293b;
        }
        
        .kwitansi-wrapper {
            max-width: 750px; 
            margin: 0 auto;
        }

        .kwitansi { 
            background: #ffffff;
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden; 
            border: 1px solid #cbd5e1;
            position: relative;
        }

        /* Kop / Header */
        .header { 
            background: #004c22; 
            color: white; 
            padding: 24px; 
            display: flex; 
            flex-direction: row;
            align-items: center; 
            gap: 20px;
        }
        
        .logo-container {
            background: white;
            padding: 6px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .logo-img {
            height: 60px;
            width: auto;
        }

        .header-text {
            flex-grow: 1;
        }

        .header-text h1 { 
            font-size: 16px; 
            font-weight: 700; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        
        .header-text h2 {
            font-size: 13px;
            font-weight: 600;
            color: #4ade80;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .header-text p { 
            font-size: 10px; 
            opacity: 0.85; 
            margin-top: 4px; 
            line-height: 1.4;
        }

        .header-right { 
            text-align: right; 
            flex-shrink: 0;
        }

        .header-right .nomor { 
            font-size: 12px; 
            font-weight: 700; 
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-family: monospace;
            display: inline-block;
        }

        .header-right .tanggal {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 8px;
        }

        .body { 
            padding: 24px; 
            position: relative;
            z-index: 1;
        }

        .title { 
            font-size: 18px; 
            font-weight: 700; 
            text-align: center; 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            margin-bottom: 24px; 
            color: #004c22; 
            border-bottom: 2px solid #004c22;
            padding-bottom: 6px;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
            position: relative;
        }

        /* Detail Fields */
        .fields-container {
            margin-bottom: 20px;
        }

        .field { 
            display: flex; 
            margin-bottom: 12px; 
            align-items: baseline; 
            border-bottom: 1px dashed #f1f5f9;
            padding-bottom: 8px;
        }
        
        .field-label { 
            width: 160px; 
            color: #64748b; 
            font-size: 11px; 
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }
        
        .field-colon { 
            margin-right: 12px; 
            color: #94a3b8;
            font-weight: bold;
        }
        
        .field-value { 
            font-weight: 500; 
            color: #0f172a;
            font-size: 12px;
            flex-grow: 1;
        }

        /* Nominal Box */
        .nominal-box { 
            background: #f0fdf4; 
            border: 1px solid #bbf7d0; 
            border-radius: 8px; 
            padding: 16px; 
            margin: 20px 0; 
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }
        
        .nominal-label { 
            font-size: 10px; 
            color: #166534; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            font-weight: 600;
        }
        
        .nominal-value { 
            font-size: 24px; 
            font-weight: 700; 
            color: #15803d; 
            margin-top: 4px; 
        }
        
        .terbilang { 
            font-style: italic; 
            font-size: 11px; 
            color: #166534; 
            margin-top: 6px; 
            background: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid rgba(22, 101, 52, 0.1);
            display: inline-block;
            width: 100%;
        }

        /* Footer / Tanda Tangan */
        .footer { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end; 
            margin-top: 35px; 
            padding-top: 20px; 
            border-top: 1px solid #e2e8f0; 
        }
        
        .sign-box { 
            text-align: center; 
            width: 180px;
        }
        
        .sign-box .title-sign { 
            font-size: 11px; 
            color: #64748b; 
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 45px;
        }
        
        .sign-name { 
            font-size: 12px; 
            font-weight: 700; 
            color: #0f172a;
            border-top: 1px solid #0f172a; 
            padding-top: 4px; 
        }

        .watermark { 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%) rotate(-25deg); 
            font-size: 90px; 
            color: rgba(0, 76, 34, 0.03); 
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
            background-color: #004c22;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: background 0.2s;
        }

        .btn-print:hover {
            background-color: #003316;
        }

        /* Responsive */
        @media (max-width: 640px) {
            body {
                padding: 10px;
            }
            .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
                padding: 20px;
            }
            .header-right {
                text-align: center;
            }
            .field {
                flex-direction: column;
                gap: 4px;
                padding-bottom: 10px;
            }
            .field-label {
                width: 100%;
            }
            .field-colon {
                display: none;
            }
            .footer {
                flex-direction: column;
                align-items: center;
                gap: 25px;
            }
            .sign-box {
                width: 100%;
                max-width: 220px;
            }
            .title {
                font-size: 15px;
                margin-bottom: 15px;
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
                border: 2px solid #004c22;
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

                <div class="field" style="border-bottom: none;">
                    <span class="field-label">Untuk keperluan</span>
                    <span class="field-colon">:</span>
                    <span class="field-value" style="font-weight: 600; color: #004c22;">{{ $kwitansi->keperluan }}</span>
                </div>

                <div class="footer">
                    <div class="sign-box">
                        <div class="title-sign">Yang membayar,</div>
                        <div class="sign-name">{{ $kwitansi->nasabah->nama }}</div>
                    </div>
                    
                    <div class="sign-box">
                        <div class="title-sign">Bendahara BUMDes,</div>
                        <div class="sign-name">________________________</div>
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

    <script>
        window.onload = () => {
            if (window.innerWidth > 640) {
                window.print();
            }
        };
    </script>
</body>
</html>
