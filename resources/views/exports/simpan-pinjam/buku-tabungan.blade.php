<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Buku Tabungan Nasabah</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Poppins", Arial, sans-serif;
            color: #000000;
            font-size: 12px;
            padding: 20px;
        }
        h2 { font-size: 16px; margin-bottom: 10px; }
        p { font-size: 12px; margin-bottom: 4px; }

        /* Summary: grid 2 kolom agar tidak meluber */
        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 14px;
            margin-bottom: 4px;
        }
        .summary div {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Tabel dengan lebar kolom eksplisit */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            table-layout: fixed;
        }
        colgroup col.col-tanggal    { width: 10%; }
        colgroup col.col-no         { width: 15%; }
        colgroup col.col-nama       { width: 15%; }
        colgroup col.col-ket        { width: 15%; }
        colgroup col.col-setoran    { width: 12%; }
        colgroup col.col-penarikan  { width: 12%; }
        colgroup col.col-admin      { width: 10%; }
        colgroup col.col-saldo      { width: 11%; }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 10px;
            text-align: left;
            color: #000000;
            word-break: break-word;
        }
        th {
            background-color: #f5f5f5;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        /* Kolom angka: rata kanan dan nowrap */
        td.angka, th.angka {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h2>Buku Tabungan Nasabah</h2>
    <p>Nomor Registrasi: {{ $nasabah?->nomor_registrasi ?? '-' }}</p>
    <p>Nomor Rekening: {{ $nasabah?->nomor_rekening ?? '-' }}</p>
    <p>Nama: {{ $nasabah?->nama ?? '-' }}</p>
    <p>Alamat: {{ $nasabah?->alamat ?? '-' }}</p>
    <p>Nomor WhatsApp: {{ $nasabah?->no_hp ?? '-' }}</p>

    <div class="summary">
        <div>Total Setoran: <strong>Rp{{ number_format($summary['total_setoran'] ?? 0, 0, ',', '.') }}</strong></div>
        <div>Total Penarikan: <strong>Rp{{ number_format($summary['total_penarikan'] ?? 0, 0, ',', '.') }}</strong></div>
        <div>Total Administrasi: <strong>Rp{{ number_format($summary['total_administrasi'] ?? 0, 0, ',', '.') }}</strong></div>
        <div>Saldo Saat Ini: <strong>Rp{{ number_format($summary['saldo_saat_ini'] ?? 0, 0, ',', '.') }}</strong></div>
    </div>

    <table>
        <colgroup>
            <col class="col-tanggal" />
            <col class="col-no" />
            <col class="col-nama" />
            <col class="col-ket" />
            <col class="col-setoran" />
            <col class="col-penarikan" />
            <col class="col-saldo" />
            <col class="col-admin" />
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Transaksi</th>
                <th>Nama Nasabah</th>
                <th>Uraian</th>
                <th class="angka">Masuk</th>
                <th class="angka">Keluar</th>
                <th class="angka">Saldo</th>
                <th class="angka">Laba</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaksi)
                <tr>
                    <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $transaksi->nomor_transaksi }}</td>
                    <td>{{ $transaksi->tabungan?->nasabah?->nama ?? '-' }}</td>
                    <td>{{ $transaksi->keterangan ?: ($transaksi->jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan') }}</td>
                    <td class="angka">Rp{{ number_format($transaksi->jenis_transaksi === 'setor' ? $transaksi->nominal : 0, 0, ',', '.') }}</td>
                    <td class="angka">Rp{{ number_format(in_array($transaksi->jenis_transaksi, ['tarik_tunai', 'tarik_sembako', 'tutup_periode']) ? $transaksi->nominal : 0, 0, ',', '.') }}</td>
                    <td class="angka">Rp{{ number_format($transaksi->saldo_setelah, 0, ',', '.') }}</td>
                    <td class="angka">Rp{{ number_format($transaksi->administrasi, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 12px;">Belum ada transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
