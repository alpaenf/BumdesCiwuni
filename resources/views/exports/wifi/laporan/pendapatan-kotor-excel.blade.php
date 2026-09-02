@php
    $headerColor = '#1e3a8a';
    $cols = 11;
    $bulanNama = $bulanNama ?? 'Semua Bulan';
    $tahun     = $tahun ?? now()->year;
    $tanggal   = $tanggal ?? null;

    if (!empty($tanggal)) {
        $periodLabel = 'Tanggal: ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
    } else {
        $periodLabel = 'Periode: ' . $bulanNama . ' ' . $tahun;
    }
@endphp

<table>
    {{-- Logo placeholder rows (logo is placed at A1 by WithLogo) --}}
    <tr><td colspan="{{ $cols }}"></td></tr>
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- Header --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:14pt; font-weight:bold; text-align:center; color: {{ $headerColor }};">
            BUMDes Ciwuni - Unit Usaha WiFi & Internet Desa
        </th>
    </tr>
    <tr>
        <th colspan="{{ $cols }}" style="font-size:12pt; font-weight:bold; text-align:center;">
            LAPORAN PENDAPATAN KOTOR WIFI
        </th>
    </tr>
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; text-align:center; color:#475569;">
            {{ $periodLabel }}
        </th>
    </tr>
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== RINGKASAN PENDAPATAN ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            RINGKASAN PENDAPATAN
        </th>
    </tr>
    <tr>
        <td colspan="4" style="font-weight:bold; border:1px solid #cbd5e1; background-color:#f1f5f9; padding:4px;">Sumber Pendapatan</td>
        <td colspan="{{ $cols - 4 }}" style="font-weight:bold; border:1px solid #cbd5e1; background-color:#f1f5f9; padding:4px; text-align:right;">Nilai (Rp)</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; font-weight:bold;">Total Pendapatan Kotor BUMDes</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; font-weight:bold;">Rp{{ number_format($pendapatanKotor, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px;">Pendapatan Dari Skema Persentase (9%)</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right;">Rp{{ number_format($pendapatanPersentase, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px;">Pendapatan Dari Skema Admin Flat</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right;">Rp{{ number_format($pendapatanAdminFlat, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; color:#15803d; font-weight:bold;">Penerimaan Kas (Tunai / Cash)</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; color:#15803d; font-weight:bold;">Rp{{ number_format($totalTunai ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; color:#1d4ed8; font-weight:bold;">Penerimaan Transfer Bank</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; color:#1d4ed8; font-weight:bold;">Rp{{ number_format($totalTransfer ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; color:#475569; font-weight:bold;">Total Dasar Tarikan Non PPN</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; color:#475569; font-weight:bold;">Rp{{ number_format($totalDasarProvider ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; color:#64748b;">Total Tarikan Bruto Pelanggan (Omset)</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; color:#64748b;">Rp{{ number_format($totalTarikanBruto, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; color:#64748b;">Total Hak Provider ISP</td>
        <td colspan="{{ $cols - 4 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; color:#64748b;">Rp{{ number_format($totalHakProvider, 0, ',', '.') }}</td>
    </tr>
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== RINCIAN PENGURANGAN PENDAPATAN ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            RINCIAN PENGURANGAN PENDAPATAN
        </th>
    </tr>
    <tr>
        <th colspan="4" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Nama Pengurangan / Biaya</th>
        <th colspan="2" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Nominal (Rp)</th>
        <th colspan="{{ $cols - 6 }}" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Persentase (%)</th>
    </tr>
    @foreach($distribusi as $item)
    @php
        $isLaba = $item['nama'] === 'Laba Bersih BUMDes';
        $isTotal = $item['nama'] === 'Total Pengambilan';
        $bgColor = $isLaba ? '#dcfce7' : ($isTotal ? '#fef9c3' : '#ffffff');
        $fontWeight = ($isLaba || $isTotal) ? 'font-weight:bold;' : '';
        $amtColor = $isLaba && $item['nominal'] < 0 ? 'color:#dc2626;' : ($isLaba ? 'color:#15803d;' : '');
    @endphp
    <tr>
        <td colspan="4" style="border:1px solid #cbd5e1; padding:4px; background-color:{{ $bgColor }}; {{ $fontWeight }}">{{ $item['nama'] }}</td>
        <td colspan="2" style="border:1px solid #cbd5e1; padding:4px; text-align:right; background-color:{{ $bgColor }}; {{ $fontWeight }}{{ $amtColor }}">Rp{{ number_format($item['nominal'], 0, ',', '.') }}</td>
        <td colspan="{{ $cols - 6 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; background-color:{{ $bgColor }}; {{ $fontWeight }}">{{ $item['persen'] }}%</td>
    </tr>
    @endforeach
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== DETAIL SKEMA PERSENTASE (9%) ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            DETAIL PENDAPATAN DARI SKEMA PERSENTASE (9%)
        </th>
    </tr>
    <tr>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Tanggal</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">No. Struk</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">ID / Nama Pelanggan</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Provider ISP</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Paket</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Tarif Warga (Rp)</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Dasar Non PPN (Rp)</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:center; padding:4px;">Skema</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Hak BUMDes</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Hak Provider</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:center; padding:4px;">Metode Bayar</th>
    </tr>
    @forelse($detailPersentase as $p)
    <tr>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ \Carbon\Carbon::parse($p['tanggal'])->format('d/m/Y') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">#{{ $p['no_transaksi'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $p['pelanggan'] }} ({{ $p['no_id_pel'] }})</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $p['provider'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $p['paket'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($p['total_tarikan'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; color:#475569;">{{ number_format($p['dasar_provider'] ?? $p['total_tarikan'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:center;">{{ $p['nilai_skema'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; font-weight:bold; color:#15803d;">{{ number_format($p['hak_bumdes'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; font-weight:bold; color:#1d4ed8;">{{ number_format($p['hak_provider'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:center; font-weight:bold;">
            {{ in_array(strtoupper(trim($p['metode'] ?? 'TUNAI')), ['TRANSFER', 'BANK', 'QRIS']) ? 'TRANSFER' : 'TUNAI' }}
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="{{ $cols }}" style="border:1px solid #e2e8f0; padding:4px; text-align:center; color:#64748b;">Tidak ada data transaksi skema persentase</td>
    </tr>
    @endforelse
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== DETAIL SKEMA ADMIN FLAT ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            DETAIL PENDAPATAN DARI SKEMA ADMIN FLAT
        </th>
    </tr>
    <tr>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Tanggal</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">No. Struk</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">ID / Nama Pelanggan</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Provider ISP</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Paket</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Tarif Warga (Rp)</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Dasar Non PPN (Rp)</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:center; padding:4px;">Admin Flat</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Hak BUMDes</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Hak Provider</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:center; padding:4px;">Metode Bayar</th>
    </tr>
    @forelse($detailAdminFlat as $f)
    <tr>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ \Carbon\Carbon::parse($f['tanggal'])->format('d/m/Y') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">#{{ $f['no_transaksi'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $f['pelanggan'] }} ({{ $f['no_id_pel'] }})</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $f['provider'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $f['paket'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($f['total_tarikan'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; color:#475569;">{{ number_format($f['dasar_provider'] ?? $f['total_tarikan'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:center;">{{ $f['nilai_skema'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; font-weight:bold; color:#15803d;">{{ number_format($f['hak_bumdes'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right; font-weight:bold; color:#1d4ed8;">{{ number_format($f['hak_provider'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:center; font-weight:bold;">
            {{ in_array(strtoupper(trim($f['metode'] ?? 'TUNAI')), ['TRANSFER', 'BANK', 'QRIS']) ? 'TRANSFER' : 'TUNAI' }}
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="{{ $cols }}" style="border:1px solid #e2e8f0; padding:4px; text-align:center; color:#64748b;">Tidak ada data transaksi skema flat</td>
    </tr>
    @endforelse
</table>

