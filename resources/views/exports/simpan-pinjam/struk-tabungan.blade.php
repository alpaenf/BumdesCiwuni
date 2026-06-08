<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Transaksi Tabungan - BUMDes Dammar Wulan</title>
    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts for a clean, receipt-like sans-serif or mono look -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style data-purpose="custom-typography">
        body {
          font-family: 'Poppins', sans-serif;
          background-color: #f3f4f6;
        }
        .receipt-font {
          font-family: 'Poppins', sans-serif;
        }
        /* Precise alignment for the key-value pairs */
        .field-label {
          width: 120px;
          display: inline-block;
        }
    </style>
    <style data-purpose="layout-styling">
        .page-container {
          width: 95%;
          max-width: 320px; /* Real thermal receipt width */
          margin: 20px auto;
          background-color: #ffffff;
          padding: 16px;
          box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .header-underline {
          border-bottom: 4px double #000;
          margin-top: 10px;
          margin-bottom: 20px;
        }
        .section-separator {
          border-top: 1px solid #000;
          margin: 10px 0;
        }
        .transaction-block {
          margin-bottom: 20px;
        }
        @page {
            margin: 0;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }
            .page-container {
                box-shadow: none;
                margin: 0;
                padding: 0 5px;
                width: 100%;
                max-width: 100%;
            }
        }
        @media (max-width: 640px) {
          body {
            padding: 1rem 0;
          }
          .page-container {
            padding: 20px;
            width: 100%;
          }
          .field-label {
            width: 130px;
            font-size: 0.875rem;
          }
        }
    </style>
</head>
<body class="p-4">
    @php
        $nasabah = $transaksi->tabungan->nasabah;
        $isSetor = $transaksi->jenis_transaksi === \App\Models\TransaksiTabungan::JENIS_SETOR;
        $nominal = (float) $transaksi->nominal;
        $administrasi = (float) $transaksi->administrasi;
        $saldoAkhir = (float) $transaksi->saldo_setelah;

        if ($isSetor) {
            $saldoAwal = $saldoAkhir - $nominal;
            $tabungan = $nominal;
            $pengambilan = 0;
        } else {
            $saldoAwal = $saldoAkhir + $nominal + $administrasi;
            $tabungan = 0;
            $pengambilan = $nominal;
        }
    @endphp

    <!-- BEGIN: MainContainer -->
    <div class="page-container receipt-font text-[11px] leading-tight text-gray-900" data-purpose="receipt-main-body">
        <!-- BEGIN: HeaderSection -->
        <header class="relative mb-4">
            <div class="flex flex-col items-center text-center">
                <!-- Logo -->
                <img alt="BUMDes Dammar Wulan Logo" class="h-10 w-auto mb-2" src="{{ asset('logo2.png') }}">
                <!-- Entity Info -->
                <div class="w-full">
                    <div class="uppercase font-bold text-xs leading-tight">
                        BADAN USAHA MILIK DESA (BUMDesa)
                    </div>
                    <div class="uppercase font-extrabold text-sm tracking-wider">
                        DAMMAR WULAN
                    </div>
                    <div class="uppercase font-semibold text-xs">
                        DESA CIWUNI
                    </div>
                    <div class="uppercase text-[9px]">
                        KECAMATAN KESUGIHAN KABUPATEN CILACAP
                    </div>
                    <div class="text-[9px] mt-1">
                        Alamat : Jl. Pasar Jagang RT 1 RW 4 Ciwuni
                    </div>
                    <div class="text-[9px]">
                        Email : <span class="text-blue-600 underline">bumdesciwuni@gmail.com</span>
                    </div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>
        <!-- END: HeaderSection -->

        <!-- BEGIN: TransactionBlock -->
        <section class="transaction-block" data-purpose="transaction-detail">
            <h2 class="text-center font-bold text-xs mb-2">STRUK TRANSAKSI TABUNGAN</h2>
            
            <!-- Customer Information -->
            <div class="space-y-1 mb-2">
                <div><span class="field-label">NOMER REKENING</span>: {{ $nasabah->nomor_rekening }}</div>
                <div><span class="field-label">NAMA</span>: {{ $nasabah->nama }}</div>
                <div><span class="field-label">ALAMAT</span>: {{ $nasabah->alamat }}</div>
                <div><span class="field-label">NOMER WA</span>: {{ $nasabah->no_hp }}</div>
            </div>
            
            <div class="section-separator"></div>
            
            <!-- Transaction Meta -->
            <div class="space-y-1 mb-2">
                <div><span class="field-label">NO. TRANSAKSI</span>: #{{ $transaksi->nomor_transaksi }}</div>
                <div><span class="field-label">TANGGAL</span>: {{ \Carbon\Carbon::parse($transaksi->tanggal)->isoFormat('D MMMM Y') }}</div>
            </div>
            
            <div class="section-separator"></div>
            
            <!-- Financial Details -->
            <div class="space-y-0.5 mt-2">
                <div class="flex">
                    <span class="field-label">Saldo Awal</span>
                    <span class="mr-2">:</span>
                    <span>{{ $saldoAwal == 0 ? '0' : 'Rp. ' . number_format($saldoAwal, 0, ',', '.') }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Tabungan</span>
                    <span class="mr-2">:</span>
                    <span>{{ $tabungan == 0 ? '0' : 'Rp. ' . number_format($tabungan, 0, ',', '.') }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Pengambilan Tabungan</span>
                    <span class="mr-2">:</span>
                    <span>{{ $pengambilan == 0 ? '0' : 'Rp. ' . number_format($pengambilan, 0, ',', '.') }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Administrasi</span>
                    <span class="mr-2">:</span>
                    <span>{{ $administrasi == 0 ? '0' : 'Rp. ' . number_format($administrasi, 0, ',', '.') }}</span>
                </div>
                <div class="flex font-bold">
                    <span class="field-label">Saldo Akhir</span>
                    <span class="mr-2">:</span>
                    <span>{{ $saldoAkhir == 0 ? '0' : 'Rp. ' . number_format($saldoAkhir, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>
        <!-- END: TransactionBlock -->

        <!-- Print Action Area -->
        <div class="no-print mt-4 flex flex-col sm:flex-row justify-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded shadow transition duration-200">
                Cetak Struk
            </button>
            <button onclick="printBluetooth()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded shadow transition duration-200 flex items-center justify-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                Cetak Bluetooth
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded shadow transition duration-200">
                Tutup Halaman
            </button>
        </div>
    </div>
    <!-- END: MainContainer -->

    <script>
        function printBluetooth() {
            var url = window.location.href;
            window.location.href = "intent:" + url + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
        }
        window.onload = () => {
            // window.print();
        };
    </script>
</body>
</html>
