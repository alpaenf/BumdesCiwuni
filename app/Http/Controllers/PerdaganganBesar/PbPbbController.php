<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbTransaksiPbb;
use App\Models\PerdaganganBesar\PbWajibPajakPbb;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PbPbbController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Daftar Transaksi PBB (TRANSAKSI PBB)
     */
    public function index(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');
        $date = $request->input('date');
        $status = $request->input('status');
        $tahun = $request->input('tahun');

        $query = PbTransaksiPbb::query()
            ->when($date, fn($q) => $q->where('tanggal', $date))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($tahun, fn($q) => $q->where('tahun_pajak', $tahun))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_wajib_pajak', 'like', "%{$search}%")
                        ->orWhere('nop', 'like', "%{$search}%")
                        ->orWhere('nomor_transaksi', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $transaksi = $query->paginate(15)->withQueryString();

        $summary = [
            'total_transaksi' => PbTransaksiPbb::count(),
            'total_sukses' => PbTransaksiPbb::where('status', 'SUCCES')->count(),
            'total_nominal_pokok' => PbTransaksiPbb::where('status', 'SUCCES')->sum('nominal_pokok'),
            'total_biaya_admin' => PbTransaksiPbb::where('status', 'SUCCES')->sum('biaya_admin'),
            'total_bayar' => PbTransaksiPbb::where('status', 'SUCCES')->sum('total_bayar'),
        ];

        $wpList = PbWajibPajakPbb::orderBy('nama_wajib_pajak')->get();

        return Inertia::render('Perdagangan Besar/Pbb/Index', [
            'unit' => $unit,
            'transaksi' => $transaksi,
            'summary' => $summary,
            'wp_list' => $wpList,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'status' => $status,
                'tahun' => $tahun,
            ],
        ]);
    }

    /**
     * Simpan Transaksi PBB
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable'],
            'wajib_pajak_pbb_id' => ['nullable', 'exists:pb_wajib_pajak_pbb,id'],
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nop' => ['required', 'string', 'max:50'],
            'alamat_objek_pajak' => ['nullable', 'string'],
            'tahun_pajak' => ['required', 'string', 'max:10'],
            'nominal_pokok' => ['required', 'numeric', 'min:0'],
            'biaya_admin' => ['nullable', 'numeric', 'min:0'],
            'metode_pembayaran' => ['required', 'in:CASH,TRANSFER'],
            'status' => ['required', 'in:SUCCES,PENDING,CANCEL'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $adminName = $user ? $user->nama : 'Admin';

            // Auto Generate: TRX-PBB-YYYYMMDD-XXXX
            $dateClean = str_replace('-', '', $validated['tanggal']);
            $countToday = PbTransaksiPbb::where('tanggal', $validated['tanggal'])->count() + 1;
            $nomorTransaksi = sprintf('TRX-PBB-%s-%04d', $dateClean, $countToday);

            $nomPokok = (float)$validated['nominal_pokok'];
            $biayaAdmin = (float)($validated['biaya_admin'] ?? 0);
            $totalBayar = $nomPokok + $biayaAdmin;

            PbTransaksiPbb::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'waktu' => $validated['waktu'] ?? Carbon::now()->format('H:i:s'),
                'wajib_pajak_pbb_id' => $validated['wajib_pajak_pbb_id'] ?? null,
                'nama_admin' => $adminName,
                'nama_wajib_pajak' => $validated['nama_wajib_pajak'],
                'nop' => trim($validated['nop']),
                'alamat_objek_pajak' => $validated['alamat_objek_pajak'] ?? null,
                'tahun_pajak' => $validated['tahun_pajak'],
                'nominal_pokok' => $nomPokok,
                'biaya_admin' => $biayaAdmin,
                'total_bayar' => $totalBayar,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Jika ada relasi wajib pajak PBB, update nominal_sppt_terakhir
            if (!empty($validated['wajib_pajak_pbb_id'])) {
                PbWajibPajakPbb::where('id', $validated['wajib_pajak_pbb_id'])
                    ->update(['nominal_sppt_terakhir' => $nomPokok]);
            }

            // Jika status SUCCES, catat ke Kas Masuk
            if ($validated['status'] === 'SUCCES') {
                PbKas::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_layanan' => 'PBB',
                    'jenis' => 'masuk',
                    'nominal' => $totalBayar,
                    'tipe_kas' => $validated['metode_pembayaran'] === 'CASH' ? 'Tunai' : 'Rekening',
                    'nama_admin' => $adminName,
                    'keterangan' => 'Kas Masuk PBB (' . $nomorTransaksi . ' - NOP: ' . $validated['nop'] . ')',
                ]);
            }
        });

        return back()->with('success', 'Transaksi PBB berhasil dicatat!');
    }

    /**
     * Hapus Transaksi PBB
     */
    public function destroy($id): RedirectResponse
    {
        $trx = PbTransaksiPbb::findOrFail($id);
        $trx->delete();
        return back()->with('success', 'Transaksi PBB berhasil dihapus!');
    }

    /**
     * Cetak Struk Transaksi PBB
     */
    public function cetakStruk($id)
    {
        $unit = $this->getUnit();
        $transaksi = PbTransaksiPbb::findOrFail($id);

        return view('perdagangan_besar.struk_pbb', [
            'unit' => $unit,
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Database Wajib Pajak PBB (Master NOP & Wajib Pajak)
     */
    public function wajibPajakIndex(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');

        $query = PbWajibPajakPbb::withCount('transaksi')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_wajib_pajak', 'like', "%{$search}%")
                    ->orWhere('nop', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%")
                    ->orWhere('alamat_objek_pajak', 'like', "%{$search}%");
            })
            ->orderBy('nama_wajib_pajak');

        $wajibPajak = $query->paginate(15)->withQueryString();

        return Inertia::render('Perdagangan Besar/Pbb/WajibPajak', [
            'unit' => $unit,
            'wajib_pajak' => $wajibPajak,
            'filters' => ['search' => $search],
        ]);
    }

    public function wajibPajakStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nop' => ['required', 'string', 'max:50', 'unique:pb_wajib_pajak_pbb,nop'],
            'nik' => ['nullable', 'string', 'max:30'],
            'nomor_hp' => ['nullable', 'string', 'max:25'],
            'alamat_objek_pajak' => ['nullable', 'string'],
            'alamat_wajib_pajak' => ['nullable', 'string'],
            'nominal_sppt_terakhir' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $validated['nop'] = trim($validated['nop']);

        PbWajibPajakPbb::create($validated);
        return back()->with('success', 'Data Wajib Pajak PBB berhasil ditambahkan!');
    }

    public function wajibPajakUpdate(Request $request, $id): RedirectResponse
    {
        $wp = PbWajibPajakPbb::findOrFail($id);
        $validated = $request->validate([
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nop' => ['required', 'string', 'max:50', 'unique:pb_wajib_pajak_pbb,nop,' . $wp->id],
            'nik' => ['nullable', 'string', 'max:30'],
            'nomor_hp' => ['nullable', 'string', 'max:25'],
            'alamat_objek_pajak' => ['nullable', 'string'],
            'alamat_wajib_pajak' => ['nullable', 'string'],
            'nominal_sppt_terakhir' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $validated['nop'] = trim($validated['nop']);

        $wp->update($validated);
        return back()->with('success', 'Data Wajib Pajak PBB berhasil diperbarui!');
    }

    public function wajibPajakDestroy($id): RedirectResponse
    {
        $wp = PbWajibPajakPbb::findOrFail($id);
        $wp->delete();
        return back()->with('success', 'Data Wajib Pajak PBB berhasil dihapus!');
    }
}

