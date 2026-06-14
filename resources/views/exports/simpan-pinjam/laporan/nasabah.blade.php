@php
    $headerColor = '#1B5E20';
    $reportTitle = 'Laporan Data Nasabah';
    $periodLabel = $filters['start_date'] ?? $filters['end_date']
        ? 'Periode: ' . ($filters['start_date'] ?? '...') . ' s/d ' . ($filters['end_date'] ?? 'sekarang')
        : 'Semua periode';
    $summaryItems = [
        ['label' => 'Total Nasabah',   'value' => $summary['total']],
        ['label' => 'Aktif',           'value' => $summary['aktif']],
        ['label' => 'Tidak Aktif',     'value' => $summary['tidak_aktif']],
    ];
@endphp
@if(!isset($isExcel))
    @include('exports.simpan-pinjam.laporan.layout')
@else
    <table>
        <tr><td colspan="9"></td></tr>
        <tr><td colspan="9"></td></tr>
        <tr>
            <th colspan="9" style="font-size:14pt; font-weight:bold; text-align:center">BUMDes Dammar Wulan - Unit Simpan Pinjam</th>
        </tr>
        <tr>
            <th colspan="9" style="font-size:12pt; font-weight:bold; text-align:center">Laporan Data Nasabah</th>
        </tr>
        <tr><td colspan="9"></td></tr>
    </table>
@endif
<table>
    <thead>
        <tr>
            <th class="text-center" style="width:28px">No</th>
            <th>No. Registrasi</th>
            <th>No. Rekening</th>
            <th>Nama Lengkap</th>
            <th>NIK</th>
            <th>No. HP</th>
            <th>Alamat</th>
            <th>Tgl. Bergabung</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $row)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $row->nomor_registrasi }}</td>
            <td>{{ $row->nomor_rekening }}</td>
            <td>{{ $row->nama }}</td>
            <td style="mso-number-format:'\@'">{{ $row->nik }}</td>
            <td style="mso-number-format:'\@'">{{ $row->no_hp }}</td>
            <td>{{ $row->alamat }}</td>
            <td>{{ $row->tanggal_bergabung ? \Carbon\Carbon::parse($row->tanggal_bergabung)->format('d/m/Y') : '-' }}</td>
            <td class="text-center">{{ ucfirst($row->status) }}</td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>
