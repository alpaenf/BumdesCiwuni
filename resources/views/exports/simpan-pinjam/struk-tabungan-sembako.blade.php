<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Transaksi Tabungan Sembako - BUMDes Dammar Wulan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <style data-purpose="typography">
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body {
          font-family: 'Poppins', sans-serif;
          color: #000;
          font-size: 11px; /* Even smaller font */
          line-height: 1.3;
          background-color: #f3f4f6;
        }

        .receipt-container {
          width: 95%;
          max-width: 320px; /* Real thermal receipt width */
          padding: 16px;
          margin: 20px auto;
          background-color: white;
          box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Print settings */
        @media print {
          .no-print {
            display: none !important;
          }
          body { background: none; }
          .receipt-container { 
            margin: 0; 
            box-shadow: none;
            width: 100%;
            padding: 20px;
          }
        }
        @media (max-width: 640px) {
          body {
            padding: 1rem 0;
          }
          .receipt-container {
            padding: 20px;
            width: 100%;
          }
          .label-col {
            width: 130px;
            font-size: 0.875rem;
          }
          .colon-separator {
            width: 12px;
            font-size: 0.875rem;
          }
        }
    </style>
    <style data-purpose="layout">
        .header-underline {
          border-bottom: 3px solid black;
          position: relative;
          margin-top: 10px;
        }
        .header-underline::after {
          content: "";
          position: absolute;
          left: 0;
          right: 0;
          bottom: -4px;
          border-bottom: 1px solid black;
        }
        .transaction-divider {
          border-top: 1px solid #000;
          margin: 10px 0;
        }
        .label-col {
          width: 120px;
          display: inline-block;
        }
        .colon-separator {
          width: 20px;
          display: inline-block;
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

    <!-- BEGIN: ReceiptPage -->
    <div class="receipt-container mx-auto" data-purpose="main-receipt-document">
        <!-- BEGIN: MainHeader -->
        <header class="flex flex-col items-center mb-3 text-center">
            <div class="mb-2">
                <img alt="BUMDes Logo" class="h-10 w-auto" data-purpose="company-logo" src="{{ asset('logo2.png') }}">
            </div>
            <div class="w-full" data-purpose="header-text-block">
                <div class="uppercase font-bold text-xs leading-tight">BADAN USAHA MILIK DESA (BUMDesa)</div>
                <div class="uppercase font-extrabold text-sm tracking-wider">DAMMAR WULAN</div>
                <div class="uppercase font-semibold text-xs">DESA CIWUNI</div>
                <div class="uppercase text-[9px]">KECAMATAN KESUGIHAN KABUPATEN CILACAP</div>
                <div class="text-[9px] mt-1">Alamat : Jl. Pasar Jagang RT 1 RW 4 Ciwuni</div>
                <div class="text-[9px]">Email : <span class="text-blue-600 underline">bumdesciwuni@gmail.com</span></div>
            </div>
        </header>
        <div class="header-underline mb-8"></div>
        <!-- END: MainHeader -->

        <!-- BEGIN: TransactionSection -->
        <section class="mb-4" data-purpose="transaction-block">
            <h3 class="text-center font-bold text-xs mb-2">STRUK TRANSAKSI TABUNGAN SEMBAKO</h3>
            
            <!-- Customer Info -->
            <div class="mb-2">
                <div><span class="label-col">NOMER REKENING</span><span class="colon-separator">:</span>{{ $nasabah->nomor_rekening }}</div>
                <div><span class="label-col">NAMA</span><span class="colon-separator">:</span>{{ $nasabah->nama }}</div>
                <div><span class="label-col">ALAMAT</span><span class="colon-separator">:</span>{{ $nasabah->alamat }}</div>
                <div><span class="label-col">NOMER WA</span><span class="colon-separator">:</span>{{ $nasabah->no_hp }}</div>
            </div>
            
            <div class="transaction-divider"></div>
            
            <!-- Transaction Details -->
            <div class="mb-2">
                <div><span class="label-col">NO. TRANSAKSI</span><span class="colon-separator">:</span>#{{ $transaksi->nomor_transaksi }}</div>
                <div><span class="label-col">TANGGAL</span><span class="colon-separator">:</span>{{ \Carbon\Carbon::parse($transaksi->tanggal)->isoFormat('D MMMM Y') }}</div>
            </div>
            
            <div class="transaction-divider"></div>
            
            <!-- Financial Data -->
            <div class="mt-2 space-y-0.5">
                <div><span class="label-col">Saldo Awal</span><span class="colon-separator">:</span>{{ $saldoAwal == 0 ? '0' : 'Rp. ' . number_format($saldoAwal, 0, ',', '.') }}</div>
                <div><span class="label-col">Tabungan</span><span class="colon-separator">:</span>{{ $tabungan == 0 ? '0' : 'Rp. ' . number_format($tabungan, 0, ',', '.') }}</div>
                <div><span class="label-col">Pengambilan Tabungan</span><span class="colon-separator">:</span>{{ $pengambilan == 0 ? '0' : 'Rp. ' . number_format($pengambilan, 0, ',', '.') }}</div>
                <div><span class="label-col">Administrasi</span><span class="colon-separator">:</span>{{ $administrasi == 0 ? '0' : 'Rp. ' . number_format($administrasi, 0, ',', '.') }}</div>
                <div class="font-bold"><span class="label-col">Saldo Akhir</span><span class="colon-separator">:</span>{{ $saldoAkhir == 0 ? '0' : 'Rp. ' . number_format($saldoAkhir, 0, ',', '.') }}</div>
            </div>
        </section>
        <!-- END: TransactionSection -->

        <!-- Print Action Area -->
        <div class="no-print mt-4 flex justify-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-black hover:bg-gray-800 text-white text-[11px] font-medium rounded-sm shadow transition duration-200">
                Cetak Struk
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-white hover:bg-gray-100 text-black text-[11px] font-medium rounded-sm border border-black shadow transition duration-200">
                Tutup Halaman
            </button>
        </div>
    </div>
    <!-- END: ReceiptPage -->

    <script>
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>
