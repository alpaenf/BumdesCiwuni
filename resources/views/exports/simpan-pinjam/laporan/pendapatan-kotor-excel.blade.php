@php
    $headerColor = '#1e3a8a';
    $cols = 7; // total columns in the widest table (detail pinjaman)
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
            BUMDes Dammar Wulan - Unit Simpan Pinjam
        </th>
    </tr>
    <tr>
        <th colspan="{{ $cols }}" style="font-size:12pt; font-weight:bold; text-align:center;">
            LAPORAN PENDAPATAN KOTOR
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
        <td colspan="3" style="font-weight:bold; border:1px solid #cbd5e1; background-color:#f1f5f9; padding:4px;">Item</td>
        <td colspan="{{ $cols - 3 }}" style="font-weight:bold; border:1px solid #cbd5e1; background-color:#f1f5f9; padding:4px;">Nilai</td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #cbd5e1; padding:4px;">Total Pendapatan Kotor</td>
        <td colspan="{{ $cols - 3 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; font-weight:bold;">Rp{{ number_format($pendapatanKotor, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #cbd5e1; padding:4px;">Bunga Pinjaman (Pendapatan)</td>
        <td colspan="{{ $cols - 3 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right;">Rp{{ number_format($bungaPinjaman, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #cbd5e1; padding:4px;">Biaya Promosi (Tabungan Reguler)</td>
        <td colspan="{{ $cols - 3 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right;">Rp{{ number_format($labaTabungan, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="3" style="border:1px solid #cbd5e1; padding:4px;">Biaya Promosi (Tabungan Sembako)</td>
        <td colspan="{{ $cols - 3 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right;">Rp{{ number_format($labaSembako, 0, ',', '.') }}</td>
    </tr>
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== RINCIAN PENGURANGAN PENDAPATAN ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            RINCIAN PENGURANGAN PENDAPATAN
        </th>
    </tr>
    {{-- Table header --}}
    <tr>
        <th colspan="3" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Nama Pengurangan / Biaya</th>
        <th colspan="2" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Nominal (Rp)</th>
        <th colspan="{{ $cols - 5 }}" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Persentase (%)</th>
    </tr>
    @foreach($distribusi as $item)
    @php
        $isLaba = $item['nama'] === 'Laba Bersih';
        $isTotal = $item['nama'] === 'Total Pengambilan';
        $bgColor = $isLaba ? '#dcfce7' : ($isTotal ? '#fef9c3' : '#ffffff');
        $fontWeight = ($isLaba || $isTotal) ? 'font-weight:bold;' : '';
        $amtColor = $isLaba && $item['nominal'] < 0 ? 'color:#dc2626;' : ($isLaba ? 'color:#15803d;' : '');
    @endphp
    <tr>
        <td colspan="3" style="border:1px solid #cbd5e1; padding:4px; background-color:{{ $bgColor }}; {{ $fontWeight }}">{{ $item['nama'] }}</td>
        <td colspan="2" style="border:1px solid #cbd5e1; padding:4px; text-align:right; background-color:{{ $bgColor }}; {{ $fontWeight }}{{ $amtColor }}">Rp{{ number_format($item['nominal'], 0, ',', '.') }}</td>
        <td colspan="{{ $cols - 5 }}" style="border:1px solid #cbd5e1; padding:4px; text-align:right; background-color:{{ $bgColor }}; {{ $fontWeight }}">{{ $item['persen'] }}%</td>
    </tr>
    @endforeach
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== DETAIL BUNGA PINJAMAN ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            DETAIL BUNGA PINJAMAN (DARI ANGSURAN)
        </th>
    </tr>
    <tr>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Tanggal</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Nama Nasabah</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Pokok Pinjaman</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Bunga (%)</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Angsuran Ke</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Bayar Pokok</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Pendapatan Bunga</th>
    </tr>
    @forelse($detailPinjaman as $p)
    <tr>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ \Carbon\Carbon::parse($p['tanggal'])->format('d/m/Y') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ $p['nasabah'] }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($p['pokok'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ $p['bunga_persen'] }}%</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ $p['angsuran_ke'] ?? '-' }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($p['angsuran_pokok'], 0, ',', '.') }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($p['bunga_nominal'], 0, ',', '.') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="{{ $cols }}" style="border:1px solid #e2e8f0; padding:4px; text-align:center; color:#64748b;">Tidak ada data pinjaman</td>
    </tr>
    @endforelse
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== DETAIL BIAYA PROMOSI REGULER ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            DETAIL BIAYA PROMOSI (TABUNGAN REGULER)
        </th>
    </tr>
    <tr>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Tanggal</th>
        <th colspan="3" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Nama Nasabah</th>
        <th colspan="2" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Keterangan</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Biaya Promosi</th>
    </tr>
    @forelse($detailTabungan as $t)
    <tr>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ \Carbon\Carbon::parse($t['tanggal'])->format('d/m/Y') }}</td>
        <td colspan="3" style="border:1px solid #e2e8f0; padding:4px;">{{ $t['nasabah'] }}</td>
        <td colspan="2" style="border:1px solid #e2e8f0; padding:4px;">{{ $t['keterangan'] ?: 'Biaya Administrasi' }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($t['laba'], 0, ',', '.') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="{{ $cols }}" style="border:1px solid #e2e8f0; padding:4px; text-align:center; color:#64748b;">Tidak ada data tabungan reguler</td>
    </tr>
    @endforelse
    <tr><td colspan="{{ $cols }}"></td></tr>

    {{-- ===== DETAIL BIAYA PROMOSI SEMBAKO ===== --}}
    <tr>
        <th colspan="{{ $cols }}" style="font-size:10pt; font-weight:bold; background-color:{{ $headerColor }}; color:#ffffff; border:1px solid {{ $headerColor }};">
            DETAIL BIAYA PROMOSI (TABUNGAN SEMBAKO)
        </th>
    </tr>
    <tr>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Tanggal</th>
        <th colspan="3" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Nama Nasabah</th>
        <th colspan="2" style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; padding:4px;">Keterangan</th>
        <th style="background-color:#dbeafe; border:1px solid #93c5fd; font-weight:bold; text-align:right; padding:4px;">Biaya Promosi</th>
    </tr>
    @forelse($detailSembako as $s)
    <tr>
        <td style="border:1px solid #e2e8f0; padding:4px;">{{ \Carbon\Carbon::parse($s['tanggal'])->format('d/m/Y') }}</td>
        <td colspan="3" style="border:1px solid #e2e8f0; padding:4px;">{{ $s['nasabah'] }}</td>
        <td colspan="2" style="border:1px solid #e2e8f0; padding:4px;">{{ $s['keterangan'] ?: 'Biaya Administrasi' }}</td>
        <td style="border:1px solid #e2e8f0; padding:4px; text-align:right;">{{ number_format($s['laba'], 0, ',', '.') }}</td>
    </tr>
    @empty
    <tr>
        <td colspan="{{ $cols }}" style="border:1px solid #e2e8f0; padding:4px; text-align:center; color:#64748b;">Tidak ada data tabungan sembako</td>
    </tr>
    @endforelse
</table>
