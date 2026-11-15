<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbNasabahLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiLakuPandai;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PbLakuPandaiController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Daftar Transaksi Harian Laku Pandai (INPUT TRANS HAR)
     */
    public function index(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');
        $date = $request->input('date', Carbon::today()->toDateString());
        $jenisTransaksi = $request->input('jenis_transaksi');
        $statusPembayaran = $request->input('status_pembayaran');

        $query = PbTransaksiLakuPandai::with('nasabah')
            ->when($date, fn($q) => $q->where('tanggal', $date))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_nasabah', 'like', "%{$search}%")
                        ->orWhere('nomor_transaksi', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($jenisTransaksi, fn($q) => $q->where('jenis_transaksi', $jenisTransaksi))
            ->when($statusPembayaran, fn($q) => $q->where('status_pembayaran', $statusPembayaran))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $transaksi = $query->paginate(15)->withQueryString();

        // Saldo Terakhir Keseluruhan
        $latest = PbTransaksiLakuPandai::orderByDesc('tanggal')->orderByDesc('id')->first();
        $saldoSnapshot = [
            'saldo_rekening' => $latest ? (float)$latest->saldo_rekening : 0,
            'saldo_cash' => $latest ? (float)$latest->saldo_cash : 0,
            'total_saldo' => $latest ? (float)$latest->total_saldo : 0,
        ];

        // Ringkasan Hari Ini
        $daySummary = [
            'rekening_masuk' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('rekening_masuk'),
            'rekening_keluar' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('rekening_keluar'),
            'cash_masuk' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('cash_masuk'),
            'cash_keluar' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('cash_keluar'),
            'pendapatan_cash' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('pendapatan_jasa_cash'),
            'pendapatan_rekening' => PbTransaksiLakuPandai::where('tanggal', $date)->sum('pendapatan_jasa_rekening'),
            'total_transaksi' => PbTransaksiLakuPandai::where('tanggal', $date)->count(),
        ];

        // Nasabah list for selector dropdown
        $nasabahList = PbNasabahLakuPandai::orderBy('nama')->get(['id', 'nama', 'no_rekening', 'nama_bank', 'no_hp', 'rekening_list']);

        return Inertia::render('Perdagangan Besar/LakuPandai/Index', [
            'unit' => $unit,
            'transaksi' => $transaksi,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'jenis_transaksi' => $jenisTransaksi,
                'status_pembayaran' => $statusPembayaran,
            ],
            'saldo' => $saldoSnapshot,
            'day_summary' => $daySummary,
            'nasabah_list' => $nasabahList,
        ]);
    }

    /**
     * Simpan Transaksi Harian
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nasabah_id' => ['nullable', 'exists:pb_nasabah_laku_pandai,id'],
            'nama_nasabah' => ['required', 'string', 'max:255'],
            'jenis_transaksi' => ['required', 'string'],
            'rekening_masuk' => ['nullable', 'numeric', 'min:0'],
            'rekening_keluar' => ['nullable', 'numeric', 'min:0'],
            'cash_masuk' => ['nullable', 'numeric', 'min:0'],
            'cash_keluar' => ['nullable', 'numeric', 'min:0'],
            'harga_agen' => ['nullable', 'numeric', 'min:0'],
            'harga_konsumen' => ['nullable', 'numeric', 'min:0'],
            'status_pembayaran' => ['required', 'in:Lunas,Hutang'],
            'jenis_pembayaran' => ['required', 'in:Cash,Transfer'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $adminName = $user ? $user->nama : 'Admin';

            // Auto Generate Nomor Transaksi: LP-YYYYMMDD-XXXX
            $dateClean = str_replace('-', '', $validated['tanggal']);
            $countToday = PbTransaksiLakuPandai::where('tanggal', $validated['tanggal'])->count() + 1;
            $nomorTransaksi = sprintf('LP-%s-%04d', $dateClean, $countToday);

            // Hitung Pendapatan Jasa
            $hargaAgen = (float)($validated['harga_agen'] ?? 0);
            $hargaKonsumen = (float)($validated['harga_konsumen'] ?? 0);
            $selisih = max(0, $hargaKonsumen - $hargaAgen);

            $pendapatanCash = 0;
            $pendapatanRekening = 0;
            if ($validated['jenis_pembayaran'] === 'Cash') {
                $pendapatanCash = $selisih;
            } else {
                $pendapatanRekening = $selisih;
            }

            // Dapatkan Saldo Terakhir sebelum transaksi ini
            $lastTrx = PbTransaksiLakuPandai::orderByDesc('tanggal')->orderByDesc('id')->first();
            $prevSaldoRek = $lastTrx ? (float)$lastTrx->saldo_rekening : 0;
            $prevSaldoCash = $lastTrx ? (float)$lastTrx->saldo_cash : 0;

            $rekMasuk = (float)($validated['rekening_masuk'] ?? 0);
            $rekKeluar = (float)($validated['rekening_keluar'] ?? 0);
            $cashMasuk = (float)($validated['cash_masuk'] ?? 0);
            $cashKeluar = (float)($validated['cash_keluar'] ?? 0);

            $currentSaldoRek = $prevSaldoRek + $rekMasuk - $rekKeluar;
            $currentSaldoCash = $prevSaldoCash + $cashMasuk - $cashKeluar;
            $currentTotalSaldo = $currentSaldoRek + $currentSaldoCash;

            PbTransaksiLakuPandai::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'nama_admin' => $adminName,
                'nasabah_id' => $validated['nasabah_id'] ?? null,
                'nama_nasabah' => $validated['nama_nasabah'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'rekening_masuk' => $rekMasuk,
                'rekening_keluar' => $rekKeluar,
                'cash_masuk' => $cashMasuk,
                'cash_keluar' => $cashKeluar,
                'harga_agen' => $hargaAgen,
                'harga_konsumen' => $hargaKonsumen,
                'pendapatan_jasa_cash' => $pendapatanCash,
                'pendapatan_jasa_rekening' => $pendapatanRekening,
                'status_pembayaran' => $validated['status_pembayaran'],
                'jenis_pembayaran' => $validated['jenis_pembayaran'],
                'saldo_rekening' => $currentSaldoRek,
                'saldo_cash' => $currentSaldoCash,
                'total_saldo' => $currentTotalSaldo,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Sinkronisasi otomatis ke PbKas jika ada pergerakan kas tunai
            if ($cashMasuk > 0) {
                PbKas::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_layanan' => 'Laku Pandai',
                    'jenis' => 'masuk',
                    'nominal' => $cashMasuk,
                    'tipe_kas' => 'Tunai',
                    'nama_admin' => $adminName,
                    'keterangan' => 'Kas Masuk Laku Pandai (' . $nomorTransaksi . ' - ' . $validated['nama_nasabah'] . ')',
                ]);
            }
            if ($cashKeluar > 0) {
                PbKas::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_layanan' => 'Laku Pandai',
                    'jenis' => 'keluar',
                    'nominal' => $cashKeluar,
                    'tipe_kas' => 'Tunai',
                    'nama_admin' => $adminName,
                    'keterangan' => 'Kas Keluar Laku Pandai (' . $nomorTransaksi . ' - ' . $validated['nama_nasabah'] . ')',
                ]);
            }
        });

        return back()->with('success', 'Transaksi Laku Pandai berhasil dicatat!');
    }

    /**
     * Hapus Transaksi Harian
     */
    public function destroy($id): RedirectResponse
    {
        $trx = PbTransaksiLakuPandai::findOrFail($id);
        $trx->delete();
        return back()->with('success', 'Transaksi Laku Pandai berhasil dihapus!');
    }

    /**
     * Cetak Struk Transaksi Laku Pandai
     */
    public function cetakStruk($id)
    {
        $unit = $this->getUnit();
        $transaksi = PbTransaksiLakuPandai::findOrFail($id);

        return view('perdagangan_besar.struk_laku_pandai', [
            'unit' => $unit,
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Master Data Nasabah Laku Pandai
     */
    public function nasabahIndex(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');

        $query = PbNasabahLakuPandai::withCount('transaksi')
            ->when($search, function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('no_rekening', 'like', "%{$search}%")
                    ->orWhere('nama_bank', 'like', "%{$search}%");
            })
            ->orderBy('nama');

        $nasabah = $query->paginate(15)->withQueryString();

        return Inertia::render('Perdagangan Besar/LakuPandai/Nasabah', [
            'unit' => $unit,
            'nasabah' => $nasabah,
            'filters' => ['search' => $search],
        ]);
    }

    public function nasabahStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'rekening_list' => ['nullable', 'array'],
            'rekening_list.*.nama_bank' => ['nullable', 'string', 'max:100'],
            'rekening_list.*.no_rekening' => ['nullable', 'string', 'max:100'],
            'rekening_list.*.atas_nama' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // Jika rekening_list diisi, pastikan no_rekening & nama_bank utama terisi item pertama jika kosong
        if (!empty($validated['rekening_list']) && is_array($validated['rekening_list'])) {
            $first = $validated['rekening_list'][0];
            if (empty($validated['nama_bank']) && !empty($first['nama_bank'])) {
                $validated['nama_bank'] = $first['nama_bank'];
            }
            if (empty($validated['no_rekening']) && !empty($first['no_rekening'])) {
                $validated['no_rekening'] = $first['no_rekening'];
            }
        }

        PbNasabahLakuPandai::create($validated);
        return back()->with('success', 'Data nasabah berhasil ditambahkan!');
    }

    public function nasabahUpdate(Request $request, $id): RedirectResponse
    {
        $nasabah = PbNasabahLakuPandai::findOrFail($id);
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'rekening_list' => ['nullable', 'array'],
            'rekening_list.*.nama_bank' => ['nullable', 'string', 'max:100'],
            'rekening_list.*.no_rekening' => ['nullable', 'string', 'max:100'],
            'rekening_list.*.atas_nama' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (!empty($validated['rekening_list']) && is_array($validated['rekening_list'])) {
            $first = $validated['rekening_list'][0];
            if (!empty($first['nama_bank'])) {
                $validated['nama_bank'] = $first['nama_bank'];
            }
            if (!empty($first['no_rekening'])) {
                $validated['no_rekening'] = $first['no_rekening'];
            }
        }

        $nasabah->update($validated);
        return back()->with('success', 'Data nasabah berhasil diperbarui!');
    }

    public function nasabahDestroy($id): RedirectResponse
    {
        $nasabah = PbNasabahLakuPandai::findOrFail($id);
        $nasabah->delete();
        return back()->with('success', 'Data nasabah berhasil dihapus!');
    }
}

