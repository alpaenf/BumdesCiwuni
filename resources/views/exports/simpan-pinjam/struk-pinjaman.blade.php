@php
use Carbon\Carbon;
$p          = $angsuran->pinjaman;
$t          = $p->total_tagihan > 0 ? $p->total_tagihan : 1;
$porsiPokok = ($p->pinjaman_pokok / $t) * $angsuran->jumlah_bayar;
$porsiBunga = (($p->pinjaman_pokok * $p->bunga / 100) / $t) * $angsuran->jumlah_bayar;
$porsiBiaya = ($p->biaya_tambahan / $t) * $angsuran->jumlah_bayar;
$tgl        = Carbon::parse($angsuran->tanggal)->isoFormat('D MMMM Y')
              . ($angsuran->pasaran ? ' ('.ucfirst($angsuran->pasaran).')' : '');
$pdfUrl     = route('angsuran.struk.pdf', $angsuran);

$receiptLines = [
    ['t'=>'center',    'text'=>'BADAN USAHA MILIK DESA (BUMDesa)'],
    ['t'=>'center_lg', 'text'=>'DAMMAR WULAN'],
    ['t'=>'center',    'text'=>'DESA CIWUNI'],
    ['t'=>'center_sm', 'text'=>'Kec. Kesugihan Kab. Cilacap'],
    ['t'=>'center_sm', 'text'=>'Jl. Pasar Jagang RT 1 RW 4 Ciwuni'],
    ['t'=>'sep_dbl'],
    ['t'=>'title',  'text'=>'STRUK TRANSAKSI PINJAMAN'],
    ['t'=>'sep'],
    ['t'=>'kv', 'label'=>'NO. REKENING', 'value'=>$p->nasabah->nomor_rekening],
    ['t'=>'kv', 'label'=>'NAMA',         'value'=>strtoupper($p->nasabah->nama)],
    ['t'=>'kv', 'label'=>'ALAMAT',       'value'=>$p->nasabah->alamat],
    ['t'=>'kv', 'label'=>'NO. WA',       'value'=>$p->nasabah->no_hp],
    ['t'=>'sep'],
    ['t'=>'kv', 'label'=>'NO. TRANSAKSI','value'=>'#'.($angsuran->nomor_transaksi ?: 'St.'.sprintf('%04d',$angsuran->id))],
    ['t'=>'kv', 'label'=>'TANGGAL',      'value'=>$tgl],
    ['t'=>'sep'],
    ['t'=>'kv', 'label'=>'Pinjaman+Bunga','value'=>'Rp.'.number_format($p->pinjaman_pokok+($p->pinjaman_pokok*$p->bunga/100),0,',','.')],
    ...($p->biaya_tambahan > 0 ? [['t'=>'kv','label'=>'Biaya Tambahan','value'=>'Rp.'.number_format($p->biaya_tambahan,0,',','.')]] : []),
    ['t'=>'kv', 'label'=>'Total Tagihan','value'=>'Rp.'.number_format($p->total_tagihan,0,',','.')],
    ['t'=>'kv', 'label'=>'Setoran ke',  'value'=>$angsuran->angsuran_ke.' / '.$p->jumlah_angsuran],
    ['t'=>'kv_bold','label'=>'Jumlah Setoran','value'=>'Rp.'.number_format($angsuran->jumlah_bayar,0,',','.')],
    ['t'=>'kv_sm','label'=>'  - Pokok', 'value'=>'Rp.'.number_format($porsiPokok,0,',','.')],
    ['t'=>'kv_sm','label'=>'  - Bunga', 'value'=>'Rp.'.number_format($porsiBunga,0,',','.')],
    ...($p->biaya_tambahan > 0 ? [['t'=>'kv_sm','label'=>'  - Biaya Tmb','value'=>'Rp.'.number_format($porsiBiaya,0,',','.')]] : []),
    ['t'=>'sep_dot'],
    ['t'=>'kv_bold','label'=>'Sisa Pinjaman','value'=>'Rp.'.number_format($angsuran->sisa_pinjaman,0,',','.')
        .($angsuran->sisa_pinjaman<=0?' [LUNAS]':'')],
    ['t'=>'sep_dbl'],
    ['t'=>'center_sm','text'=>'Terima kasih atas kepercayaan Anda.'],
    ['t'=>'center_sm','text'=>'Simpan struk ini sebagai bukti pembayaran.'],
];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Struk Transaksi Pinjaman - BUMDes Dammar Wulan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
          theme: {
            extend: {
              fontFamily: {
                sans: ['Poppins', 'Arial', 'sans-serif'],
              },
            },
          },
        }
    </script>
    <style data-purpose="layout-styling">
        body {
          background-color: #f3f4f6; /* Light gray background for the workspace */
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 2rem 0;
        }
        .page-container {
          width: 95%;
          max-width: 320px; /* Real thermal receipt width */
          background-color: #ffffff;
          padding: 16px;
          box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .receipt-section {
          margin-bottom: 2rem;
        }
        /* Fixed width for labels to align colons perfectly */
        .label-col {
          width: 120px;
          display: inline-block;
        }
        .colon-col {
          width: 20px;
          display: inline-block;
        }
        .no-print { }
        @page { margin: 0; }
        @media print {
            body { background-color: #ffffff; padding: 0; margin: 0; }
            .page-container { box-shadow: none; padding: 0 5px; width: 100%; max-width: 100%; margin: 0; }
            .no-print { display: none !important; }
        }
        @media (max-width: 640px) {
          body {
            padding: 1rem 0;
          }
          .page-container {
            padding: 20px;
            width: 100%;
          }
          .label-col {
            width: 130px;
            font-size: 0.875rem;
          }
          .colon-col {
            width: 12px;
            font-size: 0.875rem;
          }
          .data-text, .label-text {
            font-size: 0.875rem;
          }
        }
    </style>
    <style data-purpose="typography">
        .header-main { font-weight: 700; font-size: 0.75rem; }
        .header-sub { font-weight: 700; font-size: 0.9rem; }
        .header-details { font-size: 0.65rem; }
        .title-receipt { font-weight: 600; font-size: 0.85rem; letter-spacing: 0.025em; }
        .data-text { font-weight: 500; font-size: 0.65rem; }
        .label-text { font-weight: 500; font-size: 0.65rem; }
    </style>
</head>
<body>
<!-- BEGIN: Page Layout -->
<div class="page-container" id="printable-area">
    <!-- BEGIN: Main Header (Only at the top) -->
    <header class="flex flex-col items-center border-b-2 border-black pb-2 mb-3 text-center">
        <div class="mb-2" data-purpose="logo-container">
            <img alt="logo2.png" class="h-10 w-auto object-contain" src="{{ asset('logo2.png') }}">
        </div>
        <div class="w-full" data-purpose="organization-info">
            <h1 class="header-main uppercase">Badan Usaha Milik Desa (BUMDesa)</h1>
            <h2 class="header-sub uppercase">DAMMAR WULAN</h2>
            <h3 class="header-main uppercase">DESA CIWUNI</h3>
            <p class="header-details uppercase">Kecamatan Kesugihan Kabupaten Cilacap</p>
            <p class="header-details">Alamat : Jl. Pasar Jagang RT 1 RW 4 Ciwuni</p>
            <p class="header-details">Email : <span class="text-blue-700 underline">bumdesciwuni@gmail.com</span></p>
        </div>
    </header>
    <!-- END: MainHeader -->

    <!-- BEGIN: Receipt Section -->
    <section class="receipt-section" data-purpose="receipt-unit">
        <div class="text-center mb-3">
            <h2 class="title-receipt uppercase">Struk Transaksi Pinjaman</h2>
        </div>
        <div class="space-y-0.5 mb-2" data-purpose="customer-info">
            <div class="flex">
                <span class="label-col label-text">NOMER REKENING</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $angsuran->pinjaman->nasabah->nomor_rekening }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">NAMA</span>
                <span class="colon-col">:</span>
                <span class="data-text uppercase">{{ $angsuran->pinjaman->nasabah->nama }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">ALAMAT</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $angsuran->pinjaman->nasabah->alamat }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">NOMER WA</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $angsuran->pinjaman->nasabah->no_hp }}</span>
            </div>
        </div>
        <hr class="border-black border-t my-2">
        <div class="space-y-0.5 mb-2" data-purpose="transaction-meta">
            <div class="flex">
                <span class="label-col label-text">NO. TRANSAKSI</span>
                <span class="colon-col">:</span>
                <span class="data-text">#{{ $angsuran->nomor_transaksi ?: 'St.' . sprintf('%04d', $angsuran->id) }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">TANGGAL</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $tgl }}</span>
            </div>
        </div>
        <hr class="border-black border-t my-2">
        <div class="space-y-0.5" data-purpose="financial-details">
            <div class="flex">
                <span class="label-col label-text">Pinjaman + Bunga</span>
                <span class="colon-col">:</span>
                <span class="data-text">Rp. {{ number_format($angsuran->pinjaman->pinjaman_pokok + ($angsuran->pinjaman->pinjaman_pokok * $angsuran->pinjaman->bunga / 100), 0, ',', '.') }}</span>
            </div>
            @if($angsuran->pinjaman->biaya_tambahan > 0)
            <div class="flex">
                <span class="label-col label-text">Biaya Tambahan</span>
                <span class="colon-col">:</span>
                <span class="data-text">Rp. {{ number_format($angsuran->pinjaman->biaya_tambahan, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex">
                <span class="label-col label-text">Total Tagihan</span>
                <span class="colon-col">:</span>
                <span class="data-text font-bold">Rp. {{ number_format($angsuran->pinjaman->total_tagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">Setoran ke</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $angsuran->angsuran_ke }} / {{ $angsuran->pinjaman->jumlah_angsuran }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text font-bold">Jumlah Setoran</span>
                <span class="colon-col font-bold">:</span>
                <span class="data-text font-bold">Rp. {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            
            {{-- Variabel $porsiPokok, $porsiBunga, $porsiBiaya sudah dihitung di @php atas --}}
            <div class="flex pl-4">
                <span class="label-col label-text text-[10px] italic text-gray-600 w-[110px]">- Pokok</span>
                <span class="colon-col text-[10px] italic text-gray-600">:</span>
                <span class="data-text text-[10px] italic text-gray-600">Rp. {{ number_format($porsiPokok, 0, ',', '.') }}</span>
            </div>
            <div class="flex pl-4">
                <span class="label-col label-text text-[10px] italic text-gray-600 w-[110px]">- Bunga</span>
                <span class="colon-col text-[10px] italic text-gray-600">:</span>
                <span class="data-text text-[10px] italic text-gray-600">Rp. {{ number_format($porsiBunga, 0, ',', '.') }}</span>
            </div>
            @if($p->biaya_tambahan > 0)
            <div class="flex pl-4">
                <span class="label-col label-text text-[10px] italic text-gray-600 w-[110px]">- Biaya Tambahan</span>
                <span class="colon-col text-[10px] italic text-gray-600">:</span>
                <span class="data-text text-[10px] italic text-gray-600">Rp. {{ number_format($porsiBiaya, 0, ',', '.') }}</span>
            </div>
            @endif
            <hr class="border-black border-dashed border-t my-1">
            <div class="flex">
                <span class="label-col label-text">Sisa Pinjaman</span>
                <span class="colon-col">:</span>
                <span class="data-text font-bold {{ $angsuran->sisa_pinjaman <= 0 ? 'text-blue-700' : 'text-red-600' }}">
                    Rp. {{ number_format($angsuran->sisa_pinjaman, 0, ',', '.') }}
                    @if($angsuran->sisa_pinjaman <= 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">LUNAS</span>
                    @endif
                </span>
            </div>
        </div>
    </section>
    <!-- END: Receipt Section -->
</div>
<!-- END: Page Layout -->

{{-- Semua variabel ($receiptLines, $pdfUrl, $tgl, dll) sudah disiapkan di @php awal file --}}

@include('exports.simpan-pinjam.partials.cetak-modal', [
    'pdfUrl'       => $pdfUrl,
    'receiptLines' => $receiptLines,
])

</body>
</html>


