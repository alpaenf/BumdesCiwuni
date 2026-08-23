<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan WiFi</title>
</head>
<body>
    <table>
        <!-- Header Space for Logo -->
        <tr><td colspan="11"></td></tr>
        <tr><td colspan="11"></td></tr>

        <tr>
            <th colspan="12" style="font-size:14pt; font-weight:bold; text-align:center; color:#0F172A;">BUMDes Ciwuni — Unit Usaha WiFi &amp; Internet Desa</th>
        </tr>
        <tr>
            <th colspan="12" style="font-size:12pt; font-weight:bold; text-align:center; color:#2563EB;">LAPORAN REKAPITULASI &amp; FINANSIAL PER PROVIDER</th>
        </tr>
        <tr>
            <th colspan="12" style="font-size:9pt; text-align:center; color:#64748B;">Periode: {{ (isset($filters['tanggal_dari']) && isset($filters['tanggal_sampai']) && $filters['tanggal_dari'] && $filters['tanggal_sampai']) ? 'Rentang '.$filters['tanggal_dari'].' s/d '.$filters['tanggal_sampai'] : 'Bulan '.$bulan.' '.$tahun }} | Tanggal Export: {{ date('d F Y, H:i') }} WIB</th>
        </tr>
        <tr><td colspan="12"></td></tr>

        <!-- Ringkasan Eksekutif -->
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Total Tarikan Bruto Warga:</td>
            <td colspan="9" style="font-weight:bold; text-align:left; color:#0F172A;">Rp {{ number_format($stats['total_tarikan_bruto'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Pendapatan Bersih BUMDes:</td>
            <td colspan="9" style="font-weight:bold; text-align:left; color:#047857;">Rp {{ number_format($stats['total_hasil_bumdes'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Setoran Hak Provider ISP:</td>
            <td colspan="9" style="font-weight:bold; text-align:left; color:#1D4ED8;">Rp {{ number_format($stats['total_hak_provider'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Total Pelanggan Terdaftar:</td>
            <td colspan="9" style="font-weight:bold; text-align:left;">{{ $stats['total_pelanggan'] }} Warga</td>
        </tr>
        <tr><td colspan="12"></td></tr>

        <!-- TABEL 1: REKAPITULASI PER PROVIDER -->
        <tr>
            <th colspan="12" style="font-size:10pt; font-weight:bold; text-align:left; color:#0F172A; background-color:#E2E8F0;">I. REKAPITULASI PEMBAGIAN HASIL PER PROVIDER / MITRA ISP</th>
        </tr>
        <thead>
            <tr style="background-color:#0F172A; color:#FFFFFF; font-weight:bold;">
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; width:220px;">Provider / Mitra ISP</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center; width:130px;">Skema Bagi Hasil</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center; width:110px;">Jumlah Pelanggan</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right; width:140px;">Total Tarikan (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right; width:140px;">Hasil BUMDes (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right; width:140px;">Setoran Provider (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center; width:130px;">Status Pembayaran</th>
                <th colspan="5" style="background-color:#0F172A;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapPerProvider as $i => $r)
            <tr style="{{ $i % 2 === 1 ? 'background-color:#F8FAFC;' : '' }}">
                <td style="font-weight:bold;">{{ $r['nama_provider'] }}</td>
                <td style="text-align:center;">
                    @if($r['tipe_bagi_hasil'] === 'FLAT_ADMIN')
                        FLAT Rp {{ number_format($r['nilai_bagi_hasil'], 0, ',', '.') }}
                    @else
                        {{ $r['nilai_bagi_hasil'] }}%
                    @endif
                </td>
                <td style="text-align:center; font-weight:bold;">{{ $r['total_pelanggan'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold;">{{ $r['total_tarikan'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#047857;">{{ $r['total_hasil_bumdes'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#1D4ED8;">{{ $r['total_hak_provider'] }}</td>
                <td style="text-align:center;">{{ $r['lunas_count'] }} Lunas / {{ $r['tunggakan_count'] }} Tunggakan</td>
                <td colspan="5"></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#94A3B8; padding:15px;">Tidak ada rekap provider.</td>
                <td colspan="5"></td>
            </tr>
            @endforelse
        </tbody>

        <tr><td colspan="12"></td></tr>
        <tr><td colspan="12"></td></tr>

        <!-- TABEL 2: RINCIAN TAGIHAN PELANGGAN -->
        <tr>
            <th colspan="12" style="font-size:10pt; font-weight:bold; text-align:left; color:#0F172A; background-color:#E2E8F0;">II. RINCIAN TAGIHAN PELANGGAN PER PROVIDER</th>
        </tr>
        <thead>
            <tr style="background-color:#1E293B; color:#FFFFFF; font-weight:bold;">
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:40px;">No</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:110px;">ID Pelanggan</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; width:180px;">Nama Pelanggan</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; width:160px;">Provider / ISP</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:90px;">Paket Speed</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:80px;">Gelombang</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:110px;">No WhatsApp</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; width:200px;">Alamat (RT/RW)</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:right; width:130px;">Tarikan Warga (Rp)</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:right; width:130px;">Hasil BUMDes (Rp)</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:right; width:130px;">Hak Provider (Rp)</th>
                <th style="background-color:#1E293B; color:#FFFFFF; font-weight:bold; text-align:center; width:100px;">Status Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelangganList as $idx => $p)
            @php $st = $p->current_status ?? $p->status_1_15; @endphp
            <tr style="{{ $idx % 2 === 1 ? 'background-color:#F8FAFC;' : '' }}">
                <td style="text-align:center;">{{ $p->no ?? ($idx + 1) }}</td>
                <td style="mso-number-format:'\@'; text-align:center; font-weight:bold;">{{ $p->no_id_pel ?? '-' }}</td>
                <td style="font-weight:bold;">{{ $p->nama }}</td>
                <td>{{ $p->provider ? $p->provider->nama_provider : 'Umum' }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $p->paket ?? '-' }}</td>
                <td style="text-align:center;">Tgl 1 - 10</td>
                <td style="mso-number-format:'\@'; text-align:center;">{{ $p->no_wa ?? '-' }}</td>
                <td>{{ $p->alamat }} (RT {{ $p->rt }}/RW {{ $p->rw }})</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold;">{{ $p->total_tarikan }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#047857;">{{ $p->hasil_bumdes }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#1D4ED8;">{{ $p->total_provider }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $st ?? 'Belum bayar' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align:center; color:#94A3B8; padding:15px;">Tidak ada data rincian pelanggan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color:#E2E8F0; font-weight:bold;">
                <td colspan="8" style="text-align:right; font-weight:bold;">TOTAL KESELURUHAN:</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold;">{{ $stats['total_tarikan_bruto'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#047857;">{{ $stats['total_hasil_bumdes'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#1D4ED8;">{{ $stats['total_hak_provider'] }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
