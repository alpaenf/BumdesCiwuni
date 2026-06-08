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
                <span class="data-text">#St.{{ sprintf('%04d', $angsuran->id) }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">TANGGAL</span>
                <span class="colon-col">:</span>
                <span class="data-text">
                    {{ \Carbon\Carbon::parse($angsuran->tanggal)->isoFormat('D MMMM Y') }}
                    @if($angsuran->pasaran)
                        ({{ ucfirst($angsuran->pasaran) }})
                    @endif
                </span>
            </div>
        </div>
        <hr class="border-black border-t my-2">
        <div class="space-y-0.5" data-purpose="financial-details">
            <div class="flex">
                <span class="label-col label-text">Total Pinjaman + Bunga</span>
                <span class="colon-col">:</span>
                <span class="data-text">Rp. {{ number_format($angsuran->pinjaman->total_tagihan, 0, ',', '.') }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">Setoran ke</span>
                <span class="colon-col">:</span>
                <span class="data-text">{{ $angsuran->angsuran_ke }} / {{ $angsuran->pinjaman->jumlah_angsuran }}</span>
            </div>
            <div class="flex">
                <span class="label-col label-text">Jumlah Setoran</span>
                <span class="colon-col">:</span>
                <span class="data-text">Rp. {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
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

<div class="no-print flex flex-col sm:flex-row justify-center gap-2" style="margin-top: 15px;">
    <button onclick="window.print()" style="padding: 6px 16px; background: #004c22; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Cetak Biasa</button>
    <button onclick="printBluetooth()" style="padding: 6px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 4px;">
        <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; width: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
        Cetak Bluetooth
    </button>
    <button onclick="window.close()" style="padding: 6px 16px; background: #4b5563; color: white; border: none; border-radius: 4px; font-weight: bold; font-size: 12px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Tutup Halaman</button>
</div>

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
