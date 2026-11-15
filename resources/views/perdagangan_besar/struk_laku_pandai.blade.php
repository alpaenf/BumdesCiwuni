<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Laku Pandai - {{ $transaksi->nomor_transaksi }}</title>
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
            max-width: 340px;
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

        $carbonTgl = Carbon::parse($transaksi->tanggal)->locale('id');
        $namaHari = $carbonTgl->isoFormat('dddd');
        $tglStr = $carbonTgl->isoFormat('D MMMM Y');

        $hargaKonsumen = (float) $transaksi->harga_konsumen;
        $hargaAgen = (float) $transaksi->harga_agen;
        $jasa = max(0, $hargaKonsumen - $hargaAgen);

        // Jika transaksi tarik tunai / transfer, hitung nominal utamanya
        $nominalMutasi = max((float)$transaksi->cash_keluar, (float)$transaksi->rekening_keluar, (float)$transaksi->cash_masuk, (float)$transaksi->rekening_masuk);

        $logoSrc = asset('images/logo-dammar-wulan.png');
        if (!empty($unit?->logo)) {
            $logoSrc = asset('storage/' . $unit->logo);
        }

        $namaAdmin = $transaksi->nama_admin ?: (auth()->user()->nama ?? auth()->user()->username ?? 'Petugas Agen');
        $isCash = strtolower($transaksi->jenis_pembayaran) === 'cash';
        $noStruk = preg_replace('/^TRX-?/i', '', $transaksi->nomor_transaksi);
    @endphp

    <!-- Tombol Cetak (No-Print) -->
    <div class="no-print max-w-[340px] mx-auto mb-3 flex gap-2">
        <button onclick="window.print()" class="flex-1 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition-colors flex items-center justify-center gap-2 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            CETAK STRUK THERMAL
        </button>
        <button onclick="window.close()" class="py-2.5 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition-colors text-xs">
            Tutup
        </button>
    </div>

    <!-- MainContainer -->
    <div class="page-container receipt-font text-[11px] leading-relaxed text-gray-900">
        <!-- Header -->
        <header class="relative mb-3">
            <div class="flex flex-col items-center text-center">
                <img alt="Logo BUMDes Dammar Wulan" class="h-14 w-auto mb-2 object-contain" src="{{ $logoSrc }}" onerror="this.onerror=null; this.src='/images/logo-dammar-wulan.png';">
                <div class="w-full">
                    <div class="uppercase text-[9px] tracking-wide text-gray-500 font-medium">
                        Badan Usaha Milik Desa (BUMDesa)
                    </div>
                    <div class="uppercase font-extrabold text-sm tracking-wider text-gray-900">
                        BUMDes Dammar Wulan
                    </div>
                    <div class="uppercase font-bold text-xs text-blue-700 tracking-wide mt-0.5">
                        Agen Keuangan Laku Pandai
                    </div>
                    <div class="text-[9px] text-gray-600 mt-1">
                        Tarik Tunai, Transfer &amp; Pembayaran Digital
                    </div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>

        <!-- Detail Transaksi -->
        <section class="transaction-block">
            <div class="text-center mb-3">
                <span class="inline-block bg-blue-50 text-blue-800 font-bold text-[11px] px-2.5 py-0.5 rounded-full border border-blue-200 uppercase tracking-wide">
                    Struk Transaksi Laku Pandai
                </span>
            </div>

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
                    <span class="field-label">Nasabah</span>
                    <span class="mr-2">:</span>
                    <span class="font-extrabold text-gray-900">{{ $transaksi->nama_nasabah }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Layanan</span>
                    <span class="mr-2">:</span>
                    <span class="font-bold text-blue-700 uppercase">{{ $transaksi->jenis_transaksi }}</span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Rincian Biaya -->
            <div class="space-y-1 my-2 text-[11px]">
                @if($nominalMutasi > 0 && $nominalMutasi != $hargaKonsumen)
                <div class="flex justify-between">
                    <span class="text-gray-600">Nominal Transaksi</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($nominalMutasi, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Harga / Biaya Agen</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($hargaAgen, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jasa Pelayanan</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($jasa, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Total -->
            <div class="space-y-1.5 my-2 text-[11px]">
                <div class="flex justify-between items-center py-1.5 bg-blue-50 px-2 rounded font-extrabold border border-blue-100">
                    <span class="text-gray-900 uppercase">Total Bayar Konsumen</span>
                    <span class="text-blue-800 text-xs">Rp {{ number_format($hargaKonsumen, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-start pt-1">
                    <span class="field-label">Metode Pembayaran</span>
                    <span class="mr-2">:</span>
                    <div class="flex-1 font-semibold text-gray-800">
                        {{ $transaksi->jenis_pembayaran }} ({{ $transaksi->status_pembayaran }})
                    </div>
                </div>

                <div class="flex pt-1">
                    <span class="field-label">Petugas Kasir</span>
                    <span class="mr-2">:</span>
                    <span class="font-bold text-gray-900">{{ $namaAdmin }}</span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Ringkasan Struk -->
            <div class="space-y-1 text-[10px] text-gray-600">
                <div class="flex justify-between">
                    <span>No. Transaksi</span>
                    <span class="font-mono font-bold text-gray-800">#{{ $noStruk }}</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>Status</span>
                    <span class="uppercase text-emerald-700">{{ $transaksi->status_pembayaran }}</span>
                </div>
                @if($transaksi->keterangan)
                <div class="flex justify-between text-[9.5px]">
                    <span>Keterangan</span>
                    <span class="text-gray-700 text-right">{{ $transaksi->keterangan }}</span>
                </div>
                @endif
            </div>
        </section>

        <!-- Footer -->
        <div class="text-center text-[9px] mt-4 pt-2 border-t border-gray-300 space-y-0.5 text-gray-500">
            <p class="font-medium text-gray-700">Terima kasih telah bertransaksi di Agen BUMDes.</p>
            <p>Struk ini adalah bukti transaksi yang sah.</p>
            <p class="font-bold mt-1 text-[10px] text-blue-700">-- BUMDes Dammar Wulan --</p>
        </div>
    </div>

@php
    $receiptLines = [
        ['t'=>'center',    'text'=>'BADAN USAHA MILIK DESA (BUMDesa)'],
        ['t'=>'center_lg', 'text'=>'BUMDES DAMMAR WULAN'],
        ['t'=>'center',    'text'=>'AGEN KEUANGAN LAKU PANDAI'],
        ['t'=>'sep_dbl'],
        ['t'=>'title',     'text'=>'STRUK TRANSAKSI LAKU PANDAI'],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'HARI',          'value'=>strtoupper($namaHari)],
        ['t'=>'kv','label'=>'TANGGAL',       'value'=>$tglStr],
        ['t'=>'kv','label'=>'NASABAH',       'value'=>strtoupper($transaksi->nama_nasabah)],
        ['t'=>'kv','label'=>'LAYANAN',       'value'=>strtoupper($transaksi->jenis_transaksi)],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'HARGA AGEN',    'value'=>'Rp.'.number_format($hargaAgen,0,',','.')],
        ['t'=>'kv','label'=>'JASA PELAYANAN','value'=>'Rp.'.number_format($jasa,0,',','.')],
        ['t'=>'sep_dot'],
        ['t'=>'kv_bold','label'=>'TOTAL BAYAR', 'value'=>'Rp.'.number_format($hargaKonsumen,0,',','.')],
        ['t'=>'kv','label'=>'METODE',        'value'=>$transaksi->jenis_pembayaran],
        ['t'=>'kv','label'=>'STATUS',        'value'=>strtoupper($transaksi->status_pembayaran)],
        ['t'=>'kv','label'=>'KASIR',         'value'=>strtoupper($namaAdmin)],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'NO. STRUK',     'value'=>'#'.$noStruk],
        ['t'=>'sep_dbl'],
        ['t'=>'center_sm','text'=>'Terima kasih atas transaksi Anda.'],
        ['t'=>'center_sm','text'=>'BUMDes Dammar Wulan'],
    ];

    $pdfUrl = route('pb.laku-pandai.struk', $transaksi->id);
@endphp

@include('exports.simpan-pinjam.partials.cetak-modal', [
    'pdfUrl'       => $pdfUrl,
    'receiptLines' => $receiptLines,
])

</body>
</html>

