<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Laporan Tunggakan Pinjaman</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: "Poppins", Arial, sans-serif; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px; font-size: 12px; text-align: left; }
        th { text-transform: uppercase; letter-spacing: 0.08em; font-size: 11px; color: #047857; }
        .summary { display: flex; gap: 16px; margin-top: 16px; }
        .summary div { padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; }
    </style>
</head>
<body>
    <h2>Laporan Tunggakan Pinjaman</h2>

    @php
        $statusLabels = [
            'lancar' => 'Lancar',
            'perhatian-khusus' => 'Perhatian Khusus',
            'menunggak' => 'Menunggak',
            'kredit-macet' => 'Kredit Macet',
            'lunas' => 'Lunas',
        ];
    @endphp

    <div class="summary">
        <div>Nasabah Lancar: {{ $summary['lancar'] ?? 0 }}</div>
        <div>Nasabah Menunggak: {{ $summary['menunggak'] ?? 0 }}</div>
        <div>Kredit Macet: {{ $summary['kredit_macet'] ?? 0 }}</div>
        <div>Total Piutang Bermasalah: Rp{{ number_format($summary['total_piutang_bermasalah'] ?? 0, 0, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nasabah</th>
                <th>Nomor Rekening</th>
                <th>Tanggal Akad</th>
                <th>Pinjaman Pokok</th>
                <th>Bunga</th>
                <th>Total Tagihan</th>
                <th>Tenor</th>
                <th>Angsuran Terbayar</th>
                <th>Sisa Pinjaman</th>
                <th>Pasaran Terakhir</th>
                <th>Tanggal Terakhir Bayar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['nomor_rekening'] }}</td>
                    <td>{{ $item['tanggal_akad'] }}</td>
                    <td>Rp{{ number_format($item['pinjaman_pokok'], 0, ',', '.') }}</td>
                    <td>{{ $item['bunga'] }}%</td>
                    <td>Rp{{ number_format($item['total_tagihan'], 0, ',', '.') }}</td>
                    <td>{{ $item['tenor'] }}</td>
                    <td>{{ $item['angsuran_terbayar'] }}</td>
                    <td>Rp{{ number_format($item['sisa_pinjaman'], 0, ',', '.') }}</td>
                    <td>{{ $item['pasaran_terakhir'] ?? '-' }}</td>
                    <td>{{ $item['tanggal_terakhir_bayar'] ?? '-' }}</td>
                    <td>{{ $statusLabels[$item['status']] ?? $item['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">Belum ada data tunggakan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
