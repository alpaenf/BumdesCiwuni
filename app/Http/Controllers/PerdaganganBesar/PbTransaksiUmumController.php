<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbNasabahLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiUmum;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PbTransaksiUmumController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Daftar Transaksi Layanan Umum & PPOB (TRANSAKSI UMUM)
     */
    public function index(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');
        $date = $request->input('date');
        $status = $request->input('status');
        $jenisTransaksi = $request->input('jenis_transaksi');

        $query = PbTransaksiUmum::query()
            ->when($date, fn($q) => $q->where('tanggal', $date))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($jenisTransaksi, fn($q) => $q->where('jenis_transaksi', $jenisTransaksi))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_pelanggan', 'like', "%{$search}%")
                        ->orWhere('nomor_tujuan', 'like', "%{$search}%")
                        ->orWhere('nomor_transaksi', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $transaksi = $query->paginate(15)->withQueryString();

        $summary = [
            'total_transaksi' => PbTransaksiUmum::count(),
            'total_sukses' => PbTransaksiUmum::where('status', 'SUCCES')->count(),
            'total_biaya_pokok' => PbTransaksiUmum::where('status', 'SUCCES')->sum('biaya_pokok'),
            'total_jasa_agen' => PbTransaksiUmum::where('status', 'SUCCES')->sum('biaya_jasa_agen'),
            'total_bayar' => PbTransaksiUmum::where('status', 'SUCCES')->sum('total_bayar'),
        ];

        $nasabahList = PbNasabahLakuPandai::orderBy('nama')->get(['id', 'nama', 'no_hp', 'no_rekening', 'nama_bank', 'rekening_list']);

        return Inertia::render('Perdagangan Besar/TransaksiUmum/Index', [
            'unit' => $unit,
            'transaksi' => $transaksi,
            'summary' => $summary,
            'nasabah_list' => $nasabahList,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'status' => $status,
                'jenis_transaksi' => $jenisTransaksi,
            ],
        ]);
    }

    /**
     * Simpan Transaksi Umum
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable'],
            'nama_admin' => ['nullable', 'string', 'max:100'],
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'jenis_transaksi' => ['required', 'string'],
            'nomor_tujuan' => ['required', 'string', 'max:100'],
            'biaya_pokok' => ['required', 'numeric', 'min:0'],
            'biaya_admin' => ['nullable', 'numeric', 'min:0'],
            'biaya_jasa_agen' => ['nullable', 'numeric', 'min:0'],
            'metode_pembayaran' => ['required', 'in:CASH,TRANSFER'],
            'status' => ['required', 'in:SUCCES,PENDING,CANCEL'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $adminName = !empty($validated['nama_admin']) 
                ? trim($validated['nama_admin']) 
                : ($user ? $user->nama : 'Admin');

            // Prefix berdasarkan jenis transaksi
            $prefix = match ($validated['jenis_transaksi']) {
                'Pulsa', 'Pulsa data' => 'TRX-PLS-',
                'Pulsa listrik' => 'TRX-PLN-',
                'PDAM' => 'TRX-PDAM-',
                'BPJS' => 'TRX-BPJS-',
                'WIFI' => 'TRX-WF-',
                'Pajak kendaraan' => 'TRX-PKB-',
                'Pajak PBB' => 'TRX-PBB-',
                'Virtual account' => 'TRX-VA-',
                'Deposit' => 'TRX-DEPO-',
                'Transfer sesama' => 'TRX-TFS-',
                'Transfer antarbank' => 'TRX-TFB-',
                'Tarik tunai' => 'TRX-TRT-',
                'Pelunasan hutang' => 'TRX-HTG-',
                'Multi payment' => 'TRX-MULTI-',
                default => 'TRX-UMUM-',
            };

            $dateClean = str_replace('-', '', $validated['tanggal']);
            $countToday = PbTransaksiUmum::where('tanggal', $validated['tanggal'])->count() + 1;
            $nomorTransaksi = sprintf('%s%s-%04d', $prefix, $dateClean, $countToday);

            $pokok = (float)$validated['biaya_pokok'];
            $admin = (float)($validated['biaya_admin'] ?? 0);
            $jasa = (float)($validated['biaya_jasa_agen'] ?? 0);
            $total = $pokok + $admin + $jasa;

            PbTransaksiUmum::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'] ?? Carbon::now()->format('H:i:s'),
                'nama_admin' => $adminName,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'nomor_tujuan' => trim($validated['nomor_tujuan']),
                'biaya_pokok' => $pokok,
                'biaya_admin' => $admin,
                'biaya_jasa_agen' => $jasa,
                'total_bayar' => $total,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Catat ke kas masuk jika status SUCCES
            if ($validated['status'] === 'SUCCES') {
                PbKas::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_layanan' => 'Transaksi Umum',
                    'jenis' => 'masuk',
                    'nominal' => $total,
                    'tipe_kas' => $validated['metode_pembayaran'] === 'CASH' ? 'Tunai' : 'Rekening',
                    'nama_admin' => $adminName,
                    'keterangan' => 'Kas Masuk Transaksi Umum (' . $nomorTransaksi . ' - ' . $validated['jenis_transaksi'] . ')',
                ]);
            }
        });

        return back()->with('success', 'Transaksi Umum berhasil dicatat!');
    }

    /**
     * Hapus Transaksi Umum
     */
    public function destroy($id): RedirectResponse
    {
        $trx = PbTransaksiUmum::findOrFail($id);
        $trx->delete();
        return back()->with('success', 'Transaksi Umum berhasil dihapus!');
    }

    /**
     * Cetak Struk Transaksi Umum / PPOB
     */
    public function cetakStruk($id)
    {
        $unit = $this->getUnit();
        $transaksi = PbTransaksiUmum::findOrFail($id);

        return view('perdagangan_besar.struk', [
            'unit' => $unit,
            'transaksi' => $transaksi,
        ]);
    }
}

