<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Database Pelanggan WiFi</title>
</head>
<body>
    <table>
        <!-- Header Space for Logo -->
        <tr><td colspan="19"></td></tr>
        <tr><td colspan="19"></td></tr>

        <tr>
            <th colspan="19" style="font-size:14pt; font-weight:bold; text-align:center; color:#0F172A;">BUMDes Ciwuni — Unit Usaha WiFi &amp; Internet Desa</th>
        </tr>
        <tr>
            <th colspan="19" style="font-size:12pt; font-weight:bold; text-align:center; color:#2563EB;">LAPORAN DATABASE RINCI PELANGGAN WIFI</th>
        </tr>
        <tr>
            <th colspan="19" style="font-size:9pt; text-align:center; color:#64748B;">Tanggal Export: {{ date('d F Y, H:i') }} WIB</th>
        </tr>
        <tr><td colspan="19"></td></tr>

        <!-- Ringkasan Eksekutif -->
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Total Pelanggan Terdaftar:</td>
            <td colspan="16" style="font-weight:bold; text-align:left;">{{ $summary['total_pelanggan'] }} Warga</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Total Tarikan Bruto:</td>
            <td colspan="16" style="font-weight:bold; text-align:left; color:#0F172A;">Rp {{ number_format($summary['total_tarikan'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Pendapatan Bersih BUMDes:</td>
            <td colspan="16" style="font-weight:bold; text-align:left; color:#047857;">Rp {{ number_format($summary['total_hasil_bumdes'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#F1F5F9;">Total Setoran Hak Provider:</td>
            <td colspan="16" style="font-weight:bold; text-align:left; color:#1D4ED8;">Rp {{ number_format($summary['total_provider'], 0, ',', '.') }}</td>
        </tr>
        <tr><td colspan="19"></td></tr>

        <!-- Tabel Utama -->
        <thead>
            <tr style="background-color:#0F172A; color:#FFFFFF; font-weight:bold;">
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">No</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">No ID Pelanggan</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold;">Nama Lengkap</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">NIK</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">Tanggal Daftar</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold;">Provider / Mitra ISP</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">Paket Speed</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">No WhatsApp</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold;">Alamat Rumah</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">RT</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">RW</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right;">Total Tarikan (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">Skema Bagi Hasil</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right;">Hak BUMDes (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:right;">Hak Provider (Rp)</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">Status 1-15</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">Status 16-30</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">GPS Longitude</th>
                <th style="background-color:#0F172A; color:#FFFFFF; font-weight:bold; text-align:center;">GPS Latitude</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelangganList as $i => $row)
            <tr style="{{ $i % 2 === 1 ? 'background-color:#F8FAFC;' : '' }}">
                <td style="text-align:center;">{{ $row->no ?? ($i + 1) }}</td>
                <td style="mso-number-format:'\@'; text-align:center; font-weight:bold;">{{ $row->no_id_pel ?? '-' }}</td>
                <td style="font-weight:bold;">{{ $row->nama }}</td>
                <td style="mso-number-format:'\@'; text-align:center;">{{ $row->nik ?? '-' }}</td>
                <td style="text-align:center;">{{ $row->tanggal_daftar ? \Carbon\Carbon::parse($row->tanggal_daftar)->format('d/m/Y') : '-' }}</td>
                <td>{{ $row->provider ? $row->provider->nama_provider : 'Umum' }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $row->paket ?? '-' }}</td>
                <td style="mso-number-format:'\@'; text-align:center;">{{ $row->no_wa ?? '-' }}</td>
                <td>{{ $row->alamat ?? '-' }}</td>
                <td style="text-align:center;">{{ $row->rt ?? '-' }}</td>
                <td style="text-align:center;">{{ $row->rw ?? '-' }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold;">{{ $row->total_tarikan }}</td>
                <td style="text-align:center;">
                    @if($row->provider && $row->provider->tipe_bagi_hasil === 'FLAT_ADMIN')
                        FLAT Rp {{ number_format($row.provider.nilai_bagi_hasil, 0, ',', '.') }}
                    @else
                        {{ $row->bagi_hasil_bumdes ?? 9 }}%
                    @endif
                </td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#047857;">{{ $row->hasil_bumdes }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#1D4ED8;">{{ $row->total_provider }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $row->status_1_15 ?? '-' }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $row->status_16_30 ?? '-' }}</td>
                <td style="mso-number-format:'\@'; text-align:center;">{{ $row->gps_long ?? '-' }}</td>
                <td style="mso-number-format:'\@'; text-align:center;">{{ $row->gps_lat ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="19" style="text-align:center; color:#94A3B8; padding:15px;">Tidak ada data pelanggan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color:#E2E8F0; font-weight:bold;">
                <td colspan="11" style="text-align:right; font-weight:bold;">TOTAL KESELURUHAN:</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold;">{{ $summary['total_tarikan'] }}</td>
                <td></td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#047857;">{{ $summary['total_hasil_bumdes'] }}</td>
                <td style="mso-number-format:'\#\,\#\#0'; text-align:right; font-weight:bold; color:#1D4ED8;">{{ $summary['total_provider'] }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
