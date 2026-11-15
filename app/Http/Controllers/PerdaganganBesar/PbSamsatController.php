<?php

namespace App\Http\Controllers\PerdaganganBesar;

use App\Http\Controllers\Controller;
use App\Models\PerdaganganBesar\PbKas;
use App\Models\PerdaganganBesar\PbNasabahLakuPandai;
use App\Models\PerdaganganBesar\PbTransaksiSamsat;
use App\Models\PerdaganganBesar\PbWajibPajakSamsat;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PbSamsatController extends Controller
{
    private function getUnit()
    {
        return Unit::where('slug', 'perdagangan-besar')->firstOrFail();
    }

    /**
     * Daftar Transaksi Samsat Budiman (INPUT TRANSAKSI)
     */
    public function index(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');
        $date = $request->input('date');
        $status = $request->input('status');
        $jenisKendaraan = $request->input('jenis_kendaraan');

        $query = PbTransaksiSamsat::with('wajibPajak')
            ->when($date, fn($q) => $q->where('tanggal', $date))
            ->when($status, fn($q) => $q->where('status_transaksi', $status))
            ->when($jenisKendaraan, fn($q) => $q->where('jenis_kendaraan', $jenisKendaraan))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_wajib_pajak', 'like', "%{$search}%")
                        ->orWhere('nomor_polisi', 'like', "%{$search}%")
                        ->orWhere('nomor_transaksi', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $transaksi = $query->paginate(15)->withQueryString();

        // Ringkasan
        $summary = [
            'total_transaksi' => PbTransaksiSamsat::count(),
            'total_sukses' => PbTransaksiSamsat::where('status_transaksi', 'SUCCES')->count(),
            'total_nominal_pajak' => PbTransaksiSamsat::where('status_transaksi', 'SUCCES')->sum('nominal_pajak'),
            'total_jasa_bumdes' => PbTransaksiSamsat::where('status_transaksi', 'SUCCES')->sum('biaya_jasa_bumdes'),
            'total_pembayaran' => PbTransaksiSamsat::where('status_transaksi', 'SUCCES')->sum('total_pembayaran'),
        ];

        // Ambil daftar nasabah terpadu beserta kendaraan_list
        $nasabahTerpadu = PbNasabahLakuPandai::orderBy('nama')->get();

        $wpList = [];
        foreach ($nasabahTerpadu as $n) {
            if (!empty($n->kendaraan_list) && is_array($n->kendaraan_list)) {
                foreach ($n->kendaraan_list as $kdr) {
                    if (!empty($kdr['nomor_plat'])) {
                        $wpList[] = [
                            'id' => 'nb_' . $n->id . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $kdr['nomor_plat']),
                            'nama_wajib_pajak' => $n->nama,
                            'nomor_plat' => strtoupper($kdr['nomor_plat']),
                            'jenis_kendaraan' => $kdr['jenis_kendaraan'] ?? 'Sepeda Motor',
                            'merek_tipe' => $kdr['merek_tipe'] ?? '',
                            'tanggal_jatuh_tempo' => $kdr['tanggal_jatuh_tempo'] ?? null,
                        ];
                    }
                }
            }
        }

        // Gabungkan juga dengan data legacy pb_wajib_pajak_samsat jika ada
        $legacyWp = PbWajibPajakSamsat::orderBy('nama_wajib_pajak')->get();
        foreach ($legacyWp as $lw) {
            $wpList[] = [
                'id' => $lw->id,
                'nama_wajib_pajak' => $lw->nama_wajib_pajak,
                'nomor_plat' => $lw->nomor_plat,
                'jenis_kendaraan' => $lw->jenis_kendaraan,
                'merek_tipe' => $lw->merek_tipe,
                'tanggal_jatuh_tempo' => $lw->tanggal_jatuh_tempo,
            ];
        }

        return Inertia::render('Perdagangan Besar/Samsat/Index', [
            'unit' => $unit,
            'transaksi' => $transaksi,
            'summary' => $summary,
            'wp_list' => $wpList,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'status' => $status,
                'jenis_kendaraan' => $jenisKendaraan,
            ],
        ]);
    }

    /**
     * Simpan Transaksi Samsat
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'tanggal_jatuh_tempo' => ['nullable', 'date'],
            'wajib_pajak_id' => ['nullable', 'exists:pb_wajib_pajak_samsat,id'],
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nomor_polisi' => ['required', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string'],
            'jenis_pembayaran' => ['required', 'in:Cash,Transfer'],
            'nominal_pajak' => ['required', 'numeric', 'min:0'],
            'denda_pajak' => ['nullable', 'numeric', 'min:0'],
            'biaya_admin' => ['nullable', 'numeric', 'min:0'],
            'biaya_jasa_bumdes' => ['nullable', 'numeric', 'min:0'],
            'status_transaksi' => ['required', 'in:SUCCES,PENDING,CANCEL'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $adminName = $user ? $user->nama : 'Admin';

            // Auto Generate Nomor Transaksi: TRX-PKB-YYYYMMDD-XXXX
            $dateClean = str_replace('-', '', $validated['tanggal']);
            $countToday = PbTransaksiSamsat::where('tanggal', $validated['tanggal'])->count() + 1;
            $nomorTransaksi = sprintf('TRX-PKB-%s-%04d', $dateClean, $countToday);

            $nomPajak = (float)$validated['nominal_pajak'];
            $dendaPajak = (float)($validated['denda_pajak'] ?? 0);
            $biayaAdmin = (float)($validated['biaya_admin'] ?? 0);
            $jasaBumdes = (float)($validated['biaya_jasa_bumdes'] ?? 0);
            $totalBayar = $nomPajak + $dendaPajak + $biayaAdmin + $jasaBumdes;

            PbTransaksiSamsat::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'] ?? null,
                'wajib_pajak_id' => $validated['wajib_pajak_id'] ?? null,
                'nama_wajib_pajak' => $validated['nama_wajib_pajak'],
                'nomor_polisi' => strtoupper(trim($validated['nomor_polisi'])),
                'jenis_kendaraan' => $validated['jenis_kendaraan'],
                'jenis_pembayaran' => $validated['jenis_pembayaran'],
                'nominal_pajak' => $nomPajak,
                'denda_pajak' => $dendaPajak,
                'biaya_admin' => $biayaAdmin,
                'biaya_jasa_bumdes' => $jasaBumdes,
                'total_pembayaran' => $totalBayar,
                'status_transaksi' => $validated['status_transaksi'],
                'nama_admin' => $adminName,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Jika status SUCCES, catat ke Kas Samsat Budiman
            if ($validated['status_transaksi'] === 'SUCCES') {
                PbKas::create([
                    'tanggal' => $validated['tanggal'],
                    'kategori_layanan' => 'Samsat Budiman',
                    'jenis' => 'masuk',
                    'nominal' => $totalBayar,
                    'tipe_kas' => $validated['jenis_pembayaran'] === 'Cash' ? 'Tunai' : 'Rekening',
                    'nama_admin' => $adminName,
                    'keterangan' => 'Kas Masuk Samsat Budiman (' . $nomorTransaksi . ' - ' . $validated['nomor_polisi'] . ')',
                ]);
            }
        });

        return back()->with('success', 'Transaksi Samsat Budiman berhasil dicatat!');
    }

    /**
     * Hapus Transaksi Samsat
     */
    public function destroy($id): RedirectResponse
    {
        $trx = PbTransaksiSamsat::findOrFail($id);
        $trx->delete();
        return back()->with('success', 'Transaksi Samsat berhasil dihapus!');
    }

    /**
     * Cetak Struk Transaksi Samsat Budiman
     */
    public function cetakStruk($id)
    {
        $unit = $this->getUnit();
        $transaksi = PbTransaksiSamsat::findOrFail($id);

        return view('perdagangan_besar.struk_samsat', [
            'unit' => $unit,
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Database Wajib Pajak & Kendaraan (DATABASE WP)
     */
    public function wajibPajakIndex(Request $request): Response
    {
        $unit = $this->getUnit();
        $search = $request->input('search');

        $query = PbWajibPajakSamsat::withCount('transaksi')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_wajib_pajak', 'like', "%{$search}%")
                    ->orWhere('nomor_plat', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nomor_hp', 'like', "%{$search}%")
                    ->orWhere('merek_tipe', 'like', "%{$search}%");
            })
            ->orderBy('nama_wajib_pajak');

        $wajibPajak = $query->paginate(15)->withQueryString();

        return Inertia::render('Perdagangan Besar/Samsat/WajibPajak', [
            'unit' => $unit,
            'wajib_pajak' => $wajibPajak,
            'filters' => ['search' => $search],
        ]);
    }

    public function wajibPajakStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:25'],
            'alamat_lengkap' => ['nullable', 'string'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string'],
            'nomor_plat' => ['required', 'string', 'max:20'],
            'merek_tipe' => ['nullable', 'string', 'max:100'],
            'tahun_kendaraan' => ['nullable', 'string', 'max:10'],
            'tanggal_jatuh_tempo' => ['nullable', 'date'],
        ]);

        $validated['nomor_plat'] = strtoupper(trim($validated['nomor_plat']));

        PbWajibPajakSamsat::create($validated);
        return back()->with('success', 'Data Wajib Pajak berhasil ditambahkan!');
    }

    public function wajibPajakUpdate(Request $request, $id): RedirectResponse
    {
        $wp = PbWajibPajakSamsat::findOrFail($id);
        $validated = $request->validate([
            'nama_wajib_pajak' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:25'],
            'alamat_lengkap' => ['nullable', 'string'],
            'nomor_hp' => ['nullable', 'string', 'max:20'],
            'jenis_kendaraan' => ['required', 'string'],
            'nomor_plat' => ['required', 'string', 'max:20'],
            'merek_tipe' => ['nullable', 'string', 'max:100'],
            'tahun_kendaraan' => ['nullable', 'string', 'max:10'],
            'tanggal_jatuh_tempo' => ['nullable', 'date'],
        ]);

        $validated['nomor_plat'] = strtoupper(trim($validated['nomor_plat']));

        $wp->update($validated);
        return back()->with('success', 'Data Wajib Pajak berhasil diperbarui!');
    }

    public function wajibPajakDestroy($id): RedirectResponse
    {
        $wp = PbWajibPajakSamsat::findOrFail($id);
        $wp->delete();
        return back()->with('success', 'Data Wajib Pajak berhasil dihapus!');
    }
}

