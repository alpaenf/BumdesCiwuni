<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendapatanController extends Controller
{
    public function index(Request $request): Response
    {
        $data = $this->getLaporanData($request, true);

        return Inertia::render('SimpanPinjam/Pendapatan/Index', [
            'tahun' => $data['tahun'],
            'bulan' => $data['bulan'],
            'tahunOptions' => $data['tahunOptions'],
            'bulanOptions' => $data['bulanOptions'],
            'labaTabungan' => $data['labaTabungan'],
            'labaSembako' => $data['labaSembako'],
            'bungaPinjaman' => $data['bungaPinjaman'],
            'pendapatanKotor' => $data['pendapatanKotor'],
            'distribusi' => $data['distribusi'],
            'detailTabungan' => $data['detailTabungan'],
            'detailSembako' => $data['detailSembako'],
            'detailPinjaman' => $data['detailPinjaman'],
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $this->getLaporanData($request, false);

        return view('exports.simpan-pinjam.laporan.pendapatan-kotor', $data);
    }

    public function excel(Request $request)
    {
        $data = $this->getLaporanData($request, false);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Header Info
            fputcsv($handle, ['LAPORAN PENDAPATAN KOTOR']);
            fputcsv($handle, ['BUMDesa Dammar Wulan - Unit Simpan Pinjam']);
            fputcsv($handle, ['Periode: ' . $data['bulanNama'] . ' ' . $data['tahun']]);
            fputcsv($handle, []);

            // Summary
            fputcsv($handle, ['RINGKASAN PENDAPATAN']);
            fputcsv($handle, ['Total Pendapatan Kotor', $data['pendapatanKotor']]);
            fputcsv($handle, ['Bunga Pinjaman', $data['bungaPinjaman']]);
            fputcsv($handle, ['Biaya Promosi (Tabungan Reguler)', $data['labaTabungan']]);
            fputcsv($handle, ['Biaya Promosi (Tabungan Sembako)', $data['labaSembako']]);
            fputcsv($handle, []);

            // Distribusi Pengurangan Pendapatan
            fputcsv($handle, ['RINCIAN PENGURANGAN PENDAPATAN']);
            fputcsv($handle, ['Nama Pengurangan', 'Nominal', 'Persentase (%)']);
            foreach ($data['distribusi'] as $item) {
                fputcsv($handle, [
                    $item['nama'],
                    $item['nominal'],
                    $item['persen'] . '%'
                ]);
            }
            fputcsv($handle, []);

            // Detail Bunga Pinjaman
            fputcsv($handle, ['DETAIL BUNGA PINJAMAN DARI ANGSURAN']);
            fputcsv($handle, ['Tanggal', 'Nasabah', 'Pokok Pinjaman', 'Bunga (%)', 'Angsuran Ke', 'Bayar Pokok', 'Pendapatan Bunga']);
            foreach ($data['detailPinjaman'] as $p) {
                fputcsv($handle, [
                    $p['tanggal'],
                    $p['nasabah'],
                    $p['pokok'],
                    $p['bunga_persen'],
                    $p['angsuran_ke'] ?? '-',
                    $p['angsuran_pokok'],
                    $p['bunga_nominal']
                ]);
            }
            fputcsv($handle, []);

            // Detail Biaya Promosi Reguler
            fputcsv($handle, ['DETAIL BIAYA PROMOSI (TABUNGAN REGULER)']);
            fputcsv($handle, ['Tanggal', 'Nasabah', 'Keterangan', 'Biaya Promosi']);
            foreach ($data['detailTabungan'] as $t) {
                fputcsv($handle, [
                    $t['tanggal'],
                    $t['nasabah'],
                    $t['keterangan'],
                    $t['laba']
                ]);
            }
            fputcsv($handle, []);

            // Detail Biaya Promosi Sembako
            fputcsv($handle, ['DETAIL BIAYA PROMOSI (TABUNGAN SEMBAKO)']);
            fputcsv($handle, ['Tanggal', 'Nasabah', 'Keterangan', 'Biaya Promosi']);
            foreach ($data['detailSembako'] as $s) {
                fputcsv($handle, [
                    $s['tanggal'],
                    $s['nasabah'],
                    $s['keterangan'],
                    $s['laba']
                ]);
            }

            fclose($handle);
        }, 'laporan-pendapatan-kotor.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function getLaporanData(Request $request, bool $limit = false): array
    {
        $year  = $request->input('tahun', now()->year);
        $month = $request->input('bulan');
        $tanggal = $request->input('tanggal');

        $applyDateFilter = function ($query, string $dateColumn) use ($year, $month, $tanggal) {
            if ($tanggal) {
                $query->whereDate($dateColumn, $tanggal);
            } else {
                $query->whereYear($dateColumn, $year);
                if ($month) {
                    $query->whereMonth($dateColumn, $month);
                }
            }
            return $query;
        };

        // === Laba Tabungan Reguler ===
        $labaTabungan = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER))
                ->where('administrasi', '>', 0),
            'tanggal'
        )->sum('administrasi');

        // === Laba Tabungan Sembako ===
        $labaSembako = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO))
                ->where('administrasi', '>', 0),
            'tanggal'
        )->sum('administrasi');

        // === Pendapatan Bunga Pinjaman (Dari Angsuran) ===
        $angsuranQuery = \App\Models\Angsuran::join('pinjaman', 'angsuran.pinjaman_id', '=', 'pinjaman.id');
        if ($tanggal) {
            $angsuranQuery->whereDate('angsuran.tanggal', $tanggal);
        } else {
            $angsuranQuery->whereYear('angsuran.tanggal', $year);
            if ($month) {
                $angsuranQuery->whereMonth('angsuran.tanggal', $month);
            }
        }
        $bungaPinjaman = (float) $angsuranQuery->sum(\Illuminate\Support\Facades\DB::raw('( (pinjaman.pinjaman_pokok * pinjaman.bunga / 100) / GREATEST(pinjaman.total_tagihan, 1) ) * angsuran.jumlah_bayar'));

        $pendapatanKotor = $bungaPinjaman;

        $biayaGaji = 560000;
        $biayaOps = 240000;
        $biayaAsuransi = 40000;

        $totalPengurangan = $biayaGaji + $biayaOps + $biayaAsuransi;
        $labaBersih = $pendapatanKotor - $totalPengurangan;

        $calcPersen = fn($val) => $pendapatanKotor > 0 ? round(($val / $pendapatanKotor) * 100, 1) : 0;

        $distribusi = [
            [
                'nama' => 'Biaya Gaji',
                'nominal' => $biayaGaji,
                'persen' => $calcPersen($biayaGaji),
            ],
            [
                'nama' => 'Biaya Operasional',
                'nominal' => $biayaOps,
                'persen' => $calcPersen($biayaOps),
            ],
            [
                'nama' => 'Biaya Asuransi',
                'nominal' => $biayaAsuransi,
                'persen' => $calcPersen($biayaAsuransi),
            ],
            [
                'nama' => 'Laba Bersih',
                'nominal' => $labaBersih,
                'persen' => $calcPersen($labaBersih),
            ],
        ];

        // Detail transaksi laba tabungan reguler
        $detailTabunganQuery = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_REGULER))
                ->with('tabungan.nasabah')
                ->where('administrasi', '>', 0),
            'tanggal'
        )->orderByDesc('tanggal');

        if ($limit) {
            $detailTabunganQuery->limit(20);
        }

        $detailTabungan = $detailTabunganQuery->get()
            ->map(fn ($t) => [
                'tanggal' => $t->tanggal->format('Y-m-d'),
                'nasabah' => $t->tabungan?->nasabah?->nama ?? '-',
                'keterangan' => $t->keterangan,
                'laba' => $t->administrasi,
            ]);

        // Detail transaksi laba sembako
        $detailSembakoQuery = $applyDateFilter(
            TransaksiTabungan::whereHas('tabungan', fn (Builder $q) => $q->where('jenis_tabungan', Tabungan::JENIS_SEMBAKO))
                ->with('tabungan.nasabah')
                ->where('administrasi', '>', 0),
            'tanggal'
        )->orderByDesc('tanggal');

        if ($limit) {
            $detailSembakoQuery->limit(20);
        }

        $detailSembako = $detailSembakoQuery->get()
            ->map(fn ($t) => [
                'tanggal' => $t->tanggal->format('Y-m-d'),
                'nasabah' => $t->tabungan?->nasabah?->nama ?? '-',
                'keterangan' => $t->keterangan,
                'laba' => $t->administrasi,
            ]);

        // Detail pinjaman & bunga
        $detailPinjamanQuery = \App\Models\Angsuran::join('pinjaman', 'angsuran.pinjaman_id', '=', 'pinjaman.id')
            ->join('nasabah', 'pinjaman.nasabah_id', '=', 'nasabah.id')
            ->select('angsuran.*', 'pinjaman.pinjaman_pokok', 'pinjaman.bunga', 'pinjaman.total_tagihan', 'pinjaman.status as p_status', 'nasabah.nama as nasabah_nama');
        
        if ($tanggal) {
            $detailPinjamanQuery->whereDate('angsuran.tanggal', $tanggal);
        } else {
            $detailPinjamanQuery->whereYear('angsuran.tanggal', $year);
            if ($month) {
                $detailPinjamanQuery->whereMonth('angsuran.tanggal', $month);
            }
        }
        $detailPinjamanQuery->orderByDesc('angsuran.tanggal');

        if ($limit) {
            $detailPinjamanQuery->limit(20);
        }

        $detailPinjaman = $detailPinjamanQuery->get()
            ->map(function ($a) {
                $t_tagihan = max($a->total_tagihan, 1);
                $t_bunga = ($a->pinjaman_pokok * $a->bunga) / 100;
                $p_bunga = ($t_bunga / $t_tagihan) * $a->jumlah_bayar;
                return [
                    'tanggal' => $a->tanggal->format('Y-m-d'),
                    'nasabah' => $a->nasabah_nama,
                    'pokok' => $a->pinjaman_pokok,
                    'bunga_persen' => $a->bunga,
                    'bunga_nominal' => $p_bunga,
                    'angsuran_pokok' => $a->jumlah_bayar - $p_bunga,
                    'status' => $a->p_status,
                    'angsuran_ke' => $a->angsuran_ke,
                ];
            });

        $tahunOptions = collect(range(now()->year, now()->year - 5))->values();
        
        $bulanOptions = collect([
            ['value' => '',  'label' => 'Semua Bulan'],
            ['value' => 1,   'label' => 'Januari'],
            ['value' => 2,   'label' => 'Februari'],
            ['value' => 3,   'label' => 'Maret'],
            ['value' => 4,   'label' => 'April'],
            ['value' => 5,   'label' => 'Mei'],
            ['value' => 6,   'label' => 'Juni'],
            ['value' => 7,   'label' => 'Juli'],
            ['value' => 8,   'label' => 'Agustus'],
            ['value' => 9,   'label' => 'September'],
            ['value' => 10,  'label' => 'Oktober'],
            ['value' => 11,  'label' => 'November'],
            ['value' => 12,  'label' => 'Desember'],
        ]);

        $bulanNama = $month ? $bulanOptions->firstWhere('value', (int)$month)['label'] : 'Semua Bulan';

        return [
            'tahun' => (int) $year,
            'bulan' => $month ? (int) $month : null,
            'tanggal' => $tanggal,
            'tahunOptions' => $tahunOptions,
            'bulanOptions' => $bulanOptions,
            'labaTabungan' => (float) $labaTabungan,
            'labaSembako' => (float) $labaSembako,
            'bungaPinjaman' => (float) $bungaPinjaman,
            'pendapatanKotor' => (float) $pendapatanKotor,
            'distribusi' => $distribusi,
            'detailTabungan' => $detailTabungan,
            'detailSembako' => $detailSembako,
            'detailPinjaman' => $detailPinjaman,
            'bulanNama' => $bulanNama,
        ];
    }
}
