<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Penjualan Telur - {{ $sale->nomor_transaksi }}</title>
    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style data-purpose="custom-typography">
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }
        .receipt-font {
            font-family: 'Poppins', sans-serif;
        }
        .field-label {
            width: 125px;
            display: inline-block;
            font-weight: 500;
            color: #374151;
        }
    </style>
    <style data-purpose="layout-styling">
        .page-container {
            width: 95%;
            max-width: 340px; /* Real thermal receipt standard width */
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px 18px;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            border-radius: 8px;
        }
        .header-underline {
            border-bottom: 3px double #111827;
            margin-top: 12px;
            margin-bottom: 14px;
        }
        .section-separator {
            border-top: 1px dashed #9ca3af;
            margin: 10px 0;
        }
        .transaction-block {
            margin-bottom: 12px;
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
                padding: 4px 6px;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
            }
        }
        @media (max-width: 640px) {
            body {
                padding: 1rem 0;
            }
            .page-container {
                padding: 16px;
                width: 95%;
            }
            .field-label {
                width: 120px;
            }
        }
    </style>
</head>
<body class="p-4">
    @php
        use Carbon\Carbon;

        // Hari dan tanggal
        $carbonTgl = Carbon::parse($sale->tanggal)->locale('id');
        $namaHari = $carbonTgl->isoFormat('dddd');
        $tglStr = $carbonTgl->isoFormat('D MMMM Y');

        // Satuan kalkulasi (Kg, Peti @10kg, Kwintal @100kg)
        $jmlKg = (float) $sale->jumlah_kg;
        $hargaPerKg = (float) $sale->harga_per_kg;

        $jmlPeti = round($jmlKg / 10, 2);
        $jmlKwintal = round($jmlKg / 100, 3);

        $hargaPeti = round($hargaPerKg * 10);
        $hargaKwintal = round($hargaPerKg * 100);

        // Logo BUMDes Dammar Wulan
        $logoSrc = asset('images/logo-dammar-wulan.png');
        if (!empty($unit?->logo)) {
            $logoSrc = asset('storage/' . $unit->logo);
        }

        // Nama Admin
        $namaAdmin = auth()->user()->nama ?? auth()->user()->username ?? 'Admin Pangan';

        // Metode Bayar
        $isCash = in_array(strtolower($sale->metode_pembayaran), ['tunai', 'cash']);
        $isTf = in_array(strtolower($sale->metode_pembayaran), ['transfer', 'tf', 'qris']);
        $noStruk = preg_replace('/^STR-?/i', '', $sale->nomor_transaksi);
    @endphp

    <!-- BEGIN: Tombol Cetak (No-Print) -->
    <div class="no-print max-w-[340px] mx-auto mb-3 flex gap-2">
        <button onclick="window.print()" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow transition-colors flex items-center justify-center gap-2 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            CETAK STRUK THERMAL
        </button>
        <button onclick="window.close()" class="py-2.5 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition-colors text-xs">
            Tutup
        </button>
    </div>

    <!-- BEGIN: MainContainer -->
    <div class="page-container receipt-font text-[11px] leading-relaxed text-gray-900" data-purpose="receipt-main-body">
        
        <!-- BEGIN: HeaderSection -->
        <header class="relative mb-3">
            <div class="flex flex-col items-center text-center">
                <!-- Logo BUMDes Dammar Wulan -->
                <img alt="Logo BUMDes Dammar Wulan" class="h-14 w-auto mb-2 object-contain" src="{{ $logoSrc }}" onerror="this.onerror=null; this.src='/images/logo-dammar-wulan.png';">
                
                <!-- Entity Info -->
                <div class="w-full">
                    <div class="uppercase text-[9px] tracking-wide text-gray-500 font-medium">
                        Badan Usaha Milik Desa (BUMDesa)
                    </div>
                    <div class="uppercase font-extrabold text-sm tracking-wider text-gray-900">
                        BUMDes Dammar Wulan
                    </div>
                    <div class="uppercase font-bold text-xs text-emerald-700 tracking-wide mt-0.5">
                        Unit Ketahanan Pangan
                    </div>
                    <div class="text-[9px] text-gray-600 mt-1">
                        Budidaya &amp; Distribusi Telur Ayam Ras
                    </div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>
        <!-- END: HeaderSection -->

        <!-- BEGIN: TransactionBlock -->
        <section class="transaction-block" data-purpose="transaction-detail">
            <div class="text-center mb-3">
                <span class="inline-block bg-emerald-50 text-emerald-800 font-bold text-[11px] px-2.5 py-0.5 rounded-full border border-emerald-200 uppercase tracking-wide">
                    Struk Penjualan Telur
                </span>
            </div>

            <!-- Customer & Waktu -->
            <div class="space-y-1 mb-2 text-[11px]">
                <div class="flex">
                    <span class="field-label">Hari</span>
                    <span class="mr-2">:</span>
                    <span class="font-bold text-gray-900">{{ $namaHari }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Tanggal</span>
                    <span class="mr-2">:</span>
                    <span class="text-gray-800">{{ $tglStr }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Nama Pembeli</span>
                    <span class="mr-2">:</span>
                    <span class="font-extrabold text-gray-900">{{ $sale->nama_pelanggan }} <span class="text-[9.5px] font-normal text-emerald-700">({{ $sale->customer_type }})</span></span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Rincian Harga Sesuai Gambar -->
            <div class="space-y-1 my-2 text-[11px]">
                <div class="flex items-start">
                    <span class="field-label font-bold text-gray-900">Harga</span>
                    <span class="mr-2">:</span>
                    <div class="flex-1 space-y-0.5">
                        <div class="flex justify-between font-semibold">
                            <span>- kg</span>
                            <span>Rp {{ number_format($hargaPerKg, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-600">
                            <span>- peti (10 kg)</span>
                            <span>Rp {{ number_format($hargaPeti, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-600">
                            <span>- kwintal (100 kg)</span>
                            <span>Rp {{ number_format($hargaKwintal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Rincian Jumlah Pembelian Sesuai Gambar -->
            <div class="space-y-1 my-2 text-[11px]">
                <div class="flex items-start">
                    <span class="field-label font-bold text-gray-900">Jml Pembelian</span>
                    <span class="mr-2">:</span>
                    <div class="flex-1 space-y-0.5">
                        <div class="flex justify-between font-bold text-emerald-800">
                            <span>- kg</span>
                            <span>{{ number_format($jmlKg, 2) }} kg</span>
                        </div>
                        @if($sale->sumber_stok === 'gabungan')
                        <div class="flex justify-between text-[9.5px] text-indigo-700 bg-indigo-50/80 px-1 py-0.5 rounded">
                            <span>* Kandang: {{ number_format($sale->ambil_kandang_kg, 1) }} kg | Rekanan: {{ number_format($sale->ambil_rekanan_kg, 1) }} kg</span>
                        </div>
                        @elseif($sale->sumber_stok === 'rekanan')
                        <div class="flex justify-between text-[9.5px] text-blue-700 bg-blue-50/80 px-1 py-0.5 rounded">
                            <span>* Pasokan Rekanan Luar</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-[10px] text-gray-600">
                            <span>- peti</span>
                            <span>{{ $jmlPeti }} peti</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-600">
                            <span>- kwintal</span>
                            <span>{{ $jmlKwintal }} kw</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Total & Metode Bayar Sesuai Gambar -->
            <div class="space-y-1.5 my-2 text-[11px]">
                <div class="flex justify-between items-center py-1 bg-emerald-50 px-2 rounded font-extrabold border border-emerald-100">
                    <span class="text-gray-900 uppercase">Total Pembayaran</span>
                    <span class="text-emerald-700 text-xs">Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-start pt-1">
                    <span class="field-label">Metode Pembayaran</span>
                    <span class="mr-2">:</span>
                    <div class="flex-1 space-y-0.5">
                        <div class="flex items-center gap-1.5 font-medium {{ $isCash ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                            <span>{!! $isCash ? '●' : '○' !!}</span>
                            <span>Cash / Tunai</span>
                        </div>
                        <div class="flex items-center gap-1.5 font-medium {{ $isTf ? 'text-emerald-700 font-bold' : 'text-gray-400' }}">
                            <span>{!! $isTf ? '●' : '○' !!}</span>
                            <span>Transfer @if($isTf) ({{ $sale->metode_pembayaran }}) @endif</span>
                        </div>
                    </div>
                </div>

                <div class="flex pt-1">
                    <span class="field-label">Nama Admin</span>
                    <span class="mr-2">:</span>
                    <span class="font-bold text-gray-900">{{ $namaAdmin }}</span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Ringkasan Transaksi Kasir -->
            <div class="space-y-1 text-[10px] text-gray-600">
                <div class="flex justify-between">
                    <span>No. Transaksi</span>
                    <span class="font-mono font-bold text-gray-800">#{{ $noStruk }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Jumlah Bayar</span>
                    <span>Rp {{ number_format($sale->jumlah_pembayaran, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Status Transaksi</span>
                    <span class="uppercase text-emerald-700">{{ $sale->status_pembayaran }}</span>
                </div>
            </div>
        </section>
        <!-- END: TransactionBlock -->

        <!-- Footer -->
        <div class="text-center text-[9px] mt-4 pt-2 border-t border-gray-300 space-y-0.5 text-gray-500">
            <p class="font-medium text-gray-700">Terima kasih atas kepercayaan Anda.</p>
            <p>Simpan struk ini sebagai bukti transaksi yang sah.</p>
            <p class="font-bold mt-1 text-[10px] text-emerald-700">-- BUMDes Dammar Wulan --</p>
        </div>
    </div>
    <!-- END: MainContainer -->

@php
    $receiptLines = [
        ['t'=>'center',    'text'=>'BADAN USAHA MILIK DESA (BUMDesa)'],
        ['t'=>'center_lg', 'text'=>'BUMDES DAMMAR WULAN'],
        ['t'=>'center',    'text'=>'UNIT KETAHANAN PANGAN'],
        ['t'=>'sep_dbl'],
        ['t'=>'title',     'text'=>'STRUK PENJUALAN TELUR'],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'HARI',           'value'=>strtoupper($namaHari)],
        ['t'=>'kv','label'=>'TANGGAL',        'value'=>$tglStr],
        ['t'=>'kv','label'=>'NAMA PEMBELI',   'value'=>strtoupper($sale->nama_pelanggan).' ('.$sale->customer_type.')'],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'HARGA - KG',     'value'=>'Rp.'.number_format($hargaPerKg,0,',','.')],
        ['t'=>'kv','label'=>'      - PETI',   'value'=>'Rp.'.number_format($hargaPeti,0,',','.')],
        ['t'=>'kv','label'=>'      - KWINTAL','value'=>'Rp.'.number_format($hargaKwintal,0,',','.')],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'JML - KG',       'value'=>number_format($jmlKg,2).' kg'],
        ['t'=>'kv','label'=>'    - PETI',     'value'=>$jmlPeti.' peti'],
        ['t'=>'kv','label'=>'    - KWINTAL',  'value'=>$jmlKwintal.' kw'],
        ['t'=>'sep_dot'],
        ['t'=>'kv_bold','label'=>'TOTAL BAYAR','value'=>'Rp.'.number_format($sale->total_harga,0,',','.')],
        ['t'=>'kv','label'=>'METODE',         'value'=>$sale->metode_pembayaran],
        ['t'=>'kv','label'=>'ADMIN',          'value'=>strtoupper($namaAdmin)],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'NO. STRUK',      'value'=>'#'.$noStruk],
        ['t'=>'kv','label'=>'BAYAR',          'value'=>'Rp.'.number_format($sale->jumlah_pembayaran,0,',','.')],
        ['t'=>'kv','label'=>'KEMBALI',        'value'=>'Rp.'.number_format($sale->kembalian,0,',','.')],
        ['t'=>'kv','label'=>'STATUS',         'value'=>strtoupper($sale->status_pembayaran)],
        ['t'=>'sep_dbl'],
        ['t'=>'center_sm','text'=>'Terima kasih atas pembelian Anda.'],
        ['t'=>'center_sm','text'=>'BUMDes Dammar Wulan'],
    ];

    $pdfUrl = route('kp.sale.struk', $sale->id);
@endphp

@include('exports.simpan-pinjam.partials.cetak-modal', [
    'pdfUrl'       => $pdfUrl,
    'receiptLines' => $receiptLines,
])

</body>
</html>
