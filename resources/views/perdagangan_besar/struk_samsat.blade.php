<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Samsat Budiman - {{ $transaksi->nomor_transaksi }}</title>
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

        $pajak = (float) $transaksi->nominal_pajak;
        $denda = (float) ($transaksi->denda_pajak ?? 0);
        $admin = (float) $transaksi->biaya_admin;
        $jasa = (float) $transaksi->biaya_jasa_bumdes;
        $total = (float) $transaksi->total_pembayaran;

        $jatuhTempoStr = null;
        if (!empty($transaksi->tanggal_jatuh_tempo)) {
            $jatuhTempoStr = Carbon::parse($transaksi->tanggal_jatuh_tempo)->locale('id')->isoFormat('D MMMM Y');
        }

        $logoSrc = asset('images/logo-dammar-wulan.png');
        if (!empty($unit?->logo)) {
            $logoSrc = asset('storage/' . $unit->logo);
        }

        $namaAdmin = $transaksi->nama_admin ?: (auth()->user()->nama ?? auth()->user()->username ?? 'Petugas Samsat');
        $noStruk = preg_replace('/^TRX-?/i', '', $transaksi->nomor_transaksi);
    @endphp

    <!-- Tombol Cetak (No-Print) -->
    <div class="no-print max-w-[340px] mx-auto mb-3 flex flex-col gap-2">
        <div class="flex gap-2">
            <button onclick="window.print()" class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow transition-colors flex items-center justify-center gap-1.5 text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                CETAK STRUK
            </button>
            <button onclick="kirimStrukWa()" class="flex-1 py-2.5 px-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg shadow transition-colors flex items-center justify-center gap-1.5 text-xs">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                </svg>
                KIRIM KE WA
            </button>
            <button onclick="window.close()" class="py-2.5 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition-colors text-xs">
                Tutup
            </button>
        </div>
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
                        Layanan Samsat Budiman
                    </div>
                    <div class="text-[9px] text-gray-600 mt-1">
                        Pembayaran Pajak Kendaraan Bermotor Online
                    </div>
                </div>
            </div>
            <div class="header-underline"></div>
        </header>

        <!-- Detail Transaksi -->
        <section class="transaction-block">
            <div class="text-center mb-3">
                <span class="inline-block bg-blue-50 text-blue-800 font-bold text-[11px] px-2.5 py-0.5 rounded-full border border-blue-200 uppercase tracking-wide">
                    Tanda Terima Pajak Kendaraan
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
                    <span class="field-label">Wajib Pajak</span>
                    <span class="mr-2">:</span>
                    <span class="font-extrabold text-gray-900">{{ $transaksi->nama_wajib_pajak }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Nomor Polisi</span>
                    <span class="mr-2">:</span>
                    <span class="font-mono font-bold text-blue-800 text-xs">{{ $transaksi->nomor_polisi }}</span>
                </div>
                <div class="flex">
                    <span class="field-label">Kendaraan</span>
                    <span class="mr-2">:</span>
                    <span class="font-semibold text-gray-800">{{ $transaksi->jenis_kendaraan }}</span>
                </div>
                @if($jatuhTempoStr)
                <div class="flex">
                    <span class="field-label">Jatuh Tempo</span>
                    <span class="mr-2">:</span>
                    <span class="font-semibold {{ $denda > 0 ? 'text-red-700' : 'text-gray-800' }}">{{ $jatuhTempoStr }}</span>
                </div>
                @endif
            </div>

            <div class="section-separator"></div>

            <!-- Rincian Biaya -->
            <div class="space-y-1 my-2 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nominal Pajak (PKB)</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                </div>
                @if($denda > 0)
                <div class="flex justify-between text-red-600">
                    <span>Denda Pajak (Keterlambatan)</span>
                    <span class="font-bold">Rp {{ number_format($denda, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($admin > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Biaya Admin Samsat</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($admin, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-600">Jasa Pelayanan BUMDes</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($jasa, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="section-separator"></div>

            <!-- Total -->
            <div class="space-y-1.5 my-2 text-[11px]">
                <div class="flex justify-between items-center py-1.5 bg-blue-50 px-2 rounded font-extrabold border border-blue-100">
                    <span class="text-gray-900 uppercase">Total Pembayaran</span>
                    <span class="text-blue-800 text-xs">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-start pt-1">
                    <span class="field-label">Metode Pembayaran</span>
                    <span class="mr-2">:</span>
                    <div class="flex-1 font-semibold text-gray-800">
                        {{ $transaksi->jenis_pembayaran }}
                    </div>
                </div>

                <div class="flex pt-1">
                    <span class="field-label">Petugas Layanan</span>
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
                    <span>Status Transaksi</span>
                    <span class="uppercase text-emerald-700">{{ $transaksi->status_transaksi }}</span>
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
            <p class="font-medium text-gray-700">Terima kasih atas pembayaran pajak Anda.</p>
            <p>Struk ini adalah bukti pembayaran yang sah.</p>
            <p class="font-bold mt-1 text-[10px] text-blue-700">-- BUMDes Dammar Wulan --</p>
        </div>
    </div>

@php
    $receiptLines = [
        ['t'=>'center',    'text'=>'BADAN USAHA MILIK DESA (BUMDesa)'],
        ['t'=>'center_lg', 'text'=>'BUMDES DAMMAR WULAN'],
        ['t'=>'center',    'text'=>'SAMSAT BUDIMAN'],
        ['t'=>'sep_dbl'],
        ['t'=>'title',     'text'=>'TANDA TERIMA PAJAK KENDARAAN'],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'HARI',          'value'=>strtoupper($namaHari)],
        ['t'=>'kv','label'=>'TANGGAL',       'value'=>$tglStr],
        ['t'=>'kv','label'=>'WAJIB PAJAK',   'value'=>strtoupper($transaksi->nama_wajib_pajak)],
        ['t'=>'kv','label'=>'NO. POLISI',    'value'=>$transaksi->nomor_polisi],
        ['t'=>'kv','label'=>'KENDARAAN',     'value'=>strtoupper($transaksi->jenis_kendaraan)],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'PAJAK (PKB)',   'value'=>'Rp.'.number_format($pajak,0,',','.')],
    ];

    if ($admin > 0) {
        $receiptLines[] = ['t'=>'kv','label'=>'ADMIN SAMSAT',  'value'=>'Rp.'.number_format($admin,0,',','.')];
    }

    $receiptLines = array_merge($receiptLines, [
        ['t'=>'kv','label'=>'JASA BUMDES',   'value'=>'Rp.'.number_format($jasa,0,',','.')],
        ['t'=>'sep_dot'],
        ['t'=>'kv_bold','label'=>'TOTAL BAYAR', 'value'=>'Rp.'.number_format($total,0,',','.')],
        ['t'=>'kv','label'=>'METODE',        'value'=>$transaksi->jenis_pembayaran],
        ['t'=>'kv','label'=>'PETUGAS',       'value'=>strtoupper($namaAdmin)],
        ['t'=>'sep'],
        ['t'=>'kv','label'=>'NO. STRUK',     'value'=>'#'.$noStruk],
        ['t'=>'kv','label'=>'STATUS',        'value'=>strtoupper($transaksi->status_transaksi)],
        ['t'=>'sep_dbl'],
        ['t'=>'center_sm','text'=>'Terima kasih atas pembayaran Anda.'],
        ['t'=>'center_sm','text'=>'BUMDes Dammar Wulan'],
    ]);

    $pdfUrl = route('pb.samsat.struk', $transaksi->id);
@endphp

@include('exports.simpan-pinjam.partials.cetak-modal', [
    'pdfUrl'       => $pdfUrl,
    'receiptLines' => $receiptLines,
])

<script>
function kirimStrukWa() {
    let nomor = prompt('Masukkan nomor WhatsApp tujuan (contoh: 08123456789):', '');
    if (!nomor) return;

    nomor = nomor.replace(/[^0-9]/g, '');
    if (nomor.startsWith('0')) {
        nomor = '62' + nomor.slice(1);
    } else if (!nomor.startsWith('62')) {
        nomor = '62' + nomor;
    }

    const pesan = `*TANDA TERIMA PAJAK KENDARAAN (SAMSAT BUDIMAN)*
*BUMDes Dammar Wulan*
----------------------------------------
No. Transaksi : #{{ $noStruk }}
Tanggal       : {{ $tglStr }}
Wajib Pajak   : {{ $transaksi->nama_wajib_pajak }}
No. Polisi    : *{{ $transaksi->nomor_polisi }}*
Kendaraan     : {{ $transaksi->jenis_kendaraan }}
@if($jatuhTempoStr)
Jatuh Tempo   : {{ $jatuhTempoStr }}
@endif
----------------------------------------
Nominal Pajak : Rp {{ number_format($pajak, 0, ',', '.') }}
@if($denda > 0)
Denda Pajak   : Rp {{ number_format($denda, 0, ',', '.') }}
@endif
@if($admin > 0)
Biaya Admin   : Rp {{ number_format($admin, 0, ',', '.') }}
@endif
Jasa BUMDes   : Rp {{ number_format($jasa, 0, ',', '.') }}
----------------------------------------
*TOTAL BAYAR  : Rp {{ number_format($total, 0, ',', '.') }}*
Metode        : {{ $transaksi->jenis_pembayaran }}
Status        : *{{ strtoupper($transaksi->status_transaksi) }}*
Petugas       : {{ $namaAdmin }}
----------------------------------------
_Terima kasih telah membayar pajak kendaraan di Samsat Budiman BUMDes Dammar Wulan._`;

    const url = 'https://wa.me/' + nomor + '?text=' + encodeURIComponent(pesan);
    window.open(url, '_blank');
}
</script>

</body>
</html>

