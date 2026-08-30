<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Pembayaran WiFi - {{ $pembayaran->no_transaksi }}</title>
    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts Poppins -->
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
        .field-label {
          width: 110px;
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
          margin-bottom: 16px;
        }
        .section-separator {
          border-top: 1px solid #000;
          margin: 10px 0;
        }
        .transaction-block {
          margin-bottom: 16px;
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
            padding: 16px;
            width: 100%;
          }
          .field-label {
            width: 115px;
            font-size: 0.875rem;
          }
        }
    </style>
</head>
<body class="p-4">
    @php
        $pelanggan = $pembayaran->pelanggan;
        $provider  = $pelanggan ? $pelanggan->provider : null;

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bln = $namaBulan[$pembayaran->periode_bulan] ?? $pembayaran->periode_bulan;
        $tglStr = $pembayaran->tanggal_bayar ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM Y') : date('d/m/Y');
        $gel = 'Masa Bayar (Tgl 1 - 10)';
        $noStruk = preg_replace('/^TRX-?/i', '', $pembayaran->no_transaksi);
    @endphp

    <!-- BEGIN: MainContainer -->
    <div class="page-container receipt-font text-[11px] leading-tight text-gray-900" data-purpose="receipt-main-body">
        <!-- BEGIN: HeaderSection -->
        <header class="relative mb-4">
            <div class="flex flex-col items-center text-center">
                <!-- Logo -->
                <img alt="Logo BUMDes WiFi" class="h-10 w-auto mb-2" src="{{ asset('logo2.png') }}" onerror="this.src='/logo2.png'">
                <!-- Entity Info -->
                <div class="w-full">
                    <div class="uppercase font-bold text-xs leading-tight">
                        BADAN USAHA MILIK DESA (BUMDesa)
                    </div>
                    <div class="uppercase font-extrabold text-sm tracking-wider">
                        CIWUNI
                    </div>
                    <div class="uppercase font-semibold text-xs text-blue-800">
                        UNIT USAHA WIFI &amp; INTERNET DESA
                    </div>
                    <div class="uppercase text-[9px] mt-0.5">
                        KECAMATAN KESUGIHAN KABUPATEN CILACAP
                    </div>
                    <div class="text-[9px] mt-1">
                        Alamat : Kantor Desa Ciwuni, Kesugihan, Cilacap
                    </div>
                    <div class="text-[9px]">
                        WA : <span class="font-bold">085228357400</span>
                    </div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>
        <!-- END: HeaderSection -->

        <!-- BEGIN: TransactionBlock -->
        <section class="transaction-block" data-purpose="transaction-detail">
            <h2 class="text-center font-bold text-xs mb-2 tracking-wide uppercase">STRUK PEMBAYARAN WIFI</h2>
            
            <!-- Customer Information -->
            <div class="space-y-1 mb-2">
                <div><span class="field-label">NO. ID PEL</span>: <span class="font-bold">{{ $pelanggan->no_id_pel ?? '-' }}</span></div>
                <div><span class="field-label">NAMA</span>: <span class="font-bold">{{ $pelanggan->nama ?? '-' }}</span></div>
                <div><span class="field-label">PAKET SPEED</span>: <span class="font-bold">{{ $pelanggan->paket ?? '-' }}</span></div>
                <div><span class="field-label">ALAMAT</span>: {{ $pelanggan->alamat ?? '-' }} RT {{ $pelanggan->rt ?? '-' }}/RW {{ $pelanggan->rw ?? '-' }}</div>
            </div>
            
            <div class="section-separator"></div>
            
            <!-- Transaction Meta -->
            <div class="space-y-1 mb-2">
                <div><span class="field-label">NO. STRUK</span>: <span class="font-mono font-bold">#{{ $noStruk }}</span></div>
                <div><span class="field-label">TANGGAL BAYAR</span>: {{ $tglStr }}</div>
                <div><span class="field-label">KASIR / OPERATOR</span>: {{ $pembayaran->kasir ? $pembayaran->kasir->nama : 'Admin' }}</div>
            </div>
            
            <div class="section-separator"></div>
            
            <!-- Payment Details -->
            <div class="space-y-1 mt-2">
                <div class="flex">
                    <span class="field-label">PERIODE TAGIHAN</span>
                    <span class="mr-2">:</span>
                    <span class="font-bold">{{ $bln }} {{ $pembayaran->periode_tahun }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">GELOMBANG</span>
                    <span class="mr-2">:</span>
                    <span>{{ $gel }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">STATUS</span>
                    <span class="mr-2">:</span>
                    <span class="font-extrabold text-emerald-700 uppercase">{{ $pembayaran->status ?? 'LUNAS' }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">METODE BAYAR</span>
                    <span class="mr-2">:</span>
                    <span>{{ $pembayaran->metode_pembayaran }}</span>
                </div>
                <div class="flex font-extrabold text-xs mt-2 pt-1 border-t border-dashed border-gray-400">
                    <span class="field-label">TOTAL BAYAR</span>
                    <span class="mr-2">:</span>
                    <span class="text-emerald-700">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
            </div>
        </section>
        <!-- END: TransactionBlock -->

        <div class="text-center text-[9px] mt-4 pt-2 border-t border-gray-300 space-y-0.5">
            <p class="font-medium">Terima kasih atas pembayaran Anda.</p>
            <p>Simpan struk ini sebagai bukti pembayaran yang sah.</p>
            <p class="font-bold mt-1 text-[10px]">-- BUMDes Ciwuni --</p>
        </div>
    </div>
    <!-- END: MainContainer -->

@php
    $receiptLines = [
        ['t'=>'center',    'text'=>'BADAN USAHA MILIK DESA (BUMDesa)'],
        ['t'=>'center_lg', 'text'=>'CIWUNI'],
        ['t'=>'center',    'text'=>'UNIT USAHA WIFI & INTERNET DESA'],
        ['t'=>'center_sm', 'text'=>'Kec. Kesugihan Kab. Cilacap'],
        ['t'=>'sep_dbl'],
        ['t'=>'title',     'text'=>'STRUK PEMBAYARAN WIFI'],
        ['t'=>'center_sm', 'text'=>'[ '.$bln.' '.$pembayaran->periode_tahun.' ]'],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'NO. STRUK',  'value'=>'#'.$noStruk],
        ['t'=>'kv','label'=>'TGL BAYAR',  'value'=>$tglStr],
        ['t'=>'kv','label'=>'KASIR',      'value'=>strtoupper($pembayaran->kasir ? $pembayaran->kasir->nama : 'ADMIN')],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'NAMA',       'value'=>strtoupper($pelanggan->nama ?? '-')],
        ['t'=>'kv','label'=>'ID PEL',     'value'=>$pelanggan->no_id_pel ?? '-'],
        ['t'=>'kv','label'=>'PAKET',      'value'=>$pelanggan->paket ?? '-'],
        ['t'=>'kv','label'=>'ALAMAT',     'value'=>($pelanggan->alamat ?? '-').' RT '.($pelanggan->rt ?? '-').'/RW '.($pelanggan->rw ?? '-')],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'PERIODE',    'value'=>$bln.' '.$pembayaran->periode_tahun],
        ['t'=>'kv','label'=>'GELOMBANG',  'value'=>$gel],
        ['t'=>'kv','label'=>'STATUS',     'value'=>strtoupper($pembayaran->status ?? 'LUNAS')],
        ['t'=>'kv','label'=>'METODE',     'value'=>$pembayaran->metode_pembayaran],
        ['t'=>'sep_dot'],
        ['t'=>'kv_bold','label'=>'TOTAL BAYAR', 'value'=>'Rp.'.number_format($pembayaran->jumlah_bayar,0,',','.')],
        ['t'=>'sep_dbl'],
        ['t'=>'center_sm','text'=>'Terima kasih atas pembayaran Anda.'],
        ['t'=>'center_sm','text'=>'Simpan struk ini sebagai bukti pembayaran sah.'],
    ];

    $pdfUrl = route('wifi.pembayaran.struk', $pembayaran);
@endphp

@include('exports.simpan-pinjam.partials.cetak-modal', [
    'pdfUrl'       => $pdfUrl,
    'receiptLines' => $receiptLines,
])

</body>
</html>
