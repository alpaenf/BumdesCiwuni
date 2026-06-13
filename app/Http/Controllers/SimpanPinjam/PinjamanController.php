<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Http\Requests\StorePinjamanRequest;
use App\Models\Nasabah;
use App\Models\Pinjaman;
use App\Services\PinjamanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PinjamanController extends Controller
{
    public function __construct(private PinjamanService $pinjamanService) {}

    public function index(Request $request): Response
    {
        $query = Pinjaman::with('nasabah');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->whereHas('nasabah', fn($q) => $q
                ->where('nama', 'like', $search)
                ->orWhere('nomor_rekening', 'like', $search)
            );
        }

        if ($request->filled('status')) {
            if ($request->status === 'macet') {
                $query->where('status', 'aktif')
                      ->where('tanggal_akad', '<=', now()->subMonths(2));
            } elseif ($request->status === 'aktif') {
                $query->where('status', 'aktif')
                      ->where('tanggal_akad', '>', now()->subMonths(2));
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter bulan (format: YYYY-MM)
        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $query->whereYear('tanggal_akad', $year)
                  ->whereMonth('tanggal_akad', $month);
        }

        $pinjaman = $query->orderByDesc('tanggal_akad')->paginate(15)->withQueryString();

        // Summary total keseluruhan (tanpa filter bulan)
        $summaryAll = Pinjaman::selectRaw('
            COUNT(*) as total_pinjaman,
            SUM(pinjaman_pokok) as total_pokok,
            SUM(total_tagihan) as total_tagihan,
            SUM(sisa_pinjaman) as total_sisa,
            SUM(sisa_pinjaman * pinjaman_pokok / IF(total_tagihan > 0, total_tagihan, 1)) as total_sisa_pokok
        ')->first();

        // Summary total sesuai filter aktif (termasuk bulan jika ada)
        $summaryFiltered = (clone $query->getQuery());
        $summaryFiltered = Pinjaman::selectRaw('
            COUNT(*) as total_pinjaman,
            SUM(pinjaman_pokok) as total_pokok,
            SUM(total_tagihan) as total_tagihan,
            SUM(sisa_pinjaman) as total_sisa,
            SUM(sisa_pinjaman * pinjaman_pokok / IF(total_tagihan > 0, total_tagihan, 1)) as total_sisa_pokok
        ');
        if ($request->filled('bulan')) {
            [$year, $month] = explode('-', $request->bulan);
            $summaryFiltered->whereYear('tanggal_akad', $year)
                            ->whereMonth('tanggal_akad', $month);
        }
        if ($request->filled('status')) {
            if ($request->status === 'macet') {
                $summaryFiltered->where('status', 'aktif')
                                ->where('tanggal_akad', '<=', now()->subMonths(2));
            } elseif ($request->status === 'aktif') {
                $summaryFiltered->where('status', 'aktif')
                                ->where('tanggal_akad', '>', now()->subMonths(2));
            } else {
                $summaryFiltered->where('status', $request->status);
            }
        }
        $summaryFiltered = $summaryFiltered->first();

        // Daftar bulan yang memiliki data pinjaman (untuk dropdown)
        $availableBulan = Pinjaman::selectRaw("DATE_FORMAT(tanggal_akad, '%Y-%m') as bulan")
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->pluck('bulan');

        return Inertia::render('SimpanPinjam/Pinjaman/Index', [
            'pinjaman'         => $pinjaman,
            'filters'          => $request->only(['search', 'status', 'bulan']),
            'summaryAll'       => $summaryAll,
            'summaryFiltered'  => $summaryFiltered,
            'availableBulan'   => $availableBulan,
        ]);
    }

    public function create(): Response
    {
        $nasabah = Nasabah::where('status', 'aktif')
            ->whereJsonContains('kategori', 'pinjaman')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nomor_rekening']);

        return Inertia::render('SimpanPinjam/Pinjaman/Create', [
            'nasabahOptions' => $nasabah,
        ]);
    }

    public function store(StorePinjamanRequest $request)
    {
        $nasabah  = Nasabah::findOrFail($request->nasabah_id);
        $pinjaman = $this->pinjamanService->buat($nasabah, $request->validated());

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('success', 'Pinjaman berhasil ditambahkan.');
    }

    public function show(Pinjaman $pinjaman): Response
    {
        $pinjaman->load(['nasabah', 'angsuran' => fn($q) => $q->orderBy('angsuran_ke')]);

        return Inertia::render('SimpanPinjam/Pinjaman/Show', [
            'pinjaman' => $pinjaman,
        ]);
    }

    public function edit(Pinjaman $pinjaman): Response
    {
        $nasabah = Nasabah::where('status', 'aktif')
            ->whereJsonContains('kategori', 'pinjaman')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nomor_rekening']);

        return Inertia::render('SimpanPinjam/Pinjaman/Edit', [
            'pinjaman'       => $pinjaman,
            'nasabahOptions' => $nasabah,
        ]);
    }

    public function update(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'nasabah_id'      => 'required|exists:nasabah,id',
            'tanggal_akad'    => 'required|date',
            'jenis_pinjaman'  => 'required|in:uang,barang,sembako',
            'keterangan'      => 'nullable|string|max:255',
            'pinjaman_pokok'  => 'required|numeric|min:1',
            'bunga'           => 'required|numeric|min:0|max:100',
            'nominal_setoran' => 'required|numeric|min:1',
            'biaya_tambahan'  => 'nullable|numeric|min:0',
            'foto_perjanjian' => 'nullable|image|max:2048',
            'foto_barang'     => 'nullable|image|max:5120',
        ]);

        \DB::transaction(function () use ($pinjaman, $validated) {
            $pokok         = (float) $validated['pinjaman_pokok'];
            $bunga         = (float) $validated['bunga'];
            $nominalSetor  = (float) $validated['nominal_setoran'];
            $biayaTambahan = (float) ($validated['biaya_tambahan'] ?? 0);

            $totalTagihan    = $pokok + ($pokok * $bunga / 100) + $biayaTambahan;
            $jumlahAngsuran  = (int) ceil($totalTagihan / max(1, $nominalSetor));

            $foto_perjanjian = $pinjaman->foto_perjanjian;
            if (isset($validated['foto_perjanjian'])) {
                $file = $validated['foto_perjanjian'];
                $filename = time() . '_perjanjian_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/pinjaman'), $filename);
                if ($foto_perjanjian && file_exists(public_path('uploads/pinjaman/' . $foto_perjanjian))) {
                    @unlink(public_path('uploads/pinjaman/' . $foto_perjanjian));
                }
                $foto_perjanjian = $filename;
            }

            $foto_barang = $pinjaman->foto_barang;
            if (isset($validated['foto_barang'])) {
                $file = $validated['foto_barang'];
                $filename = time() . '_barang_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/pinjaman'), $filename);
                if ($foto_barang && file_exists(public_path('uploads/pinjaman/' . $foto_barang))) {
                    @unlink(public_path('uploads/pinjaman/' . $foto_barang));
                }
                $foto_barang = $filename;
            }

            $pinjaman->update([
                'nasabah_id'      => $validated['nasabah_id'],
                'tanggal_akad'    => $validated['tanggal_akad'],
                'pinjaman_pokok'  => $pokok,
                'bunga'           => $bunga,
                'total_tagihan'   => $totalTagihan,
                'nominal_setoran' => $nominalSetor,
                'biaya_tambahan'  => $biayaTambahan,
                'jumlah_angsuran' => $jumlahAngsuran,
                'jenis_pinjaman'  => $validated['jenis_pinjaman'],
                'keterangan'      => $validated['keterangan'] ?? null,
                'foto_perjanjian' => $foto_perjanjian,
                'foto_barang'     => $foto_barang,
            ]);

            // Recalculate sisa_pinjaman for the pinjaman and all its angsuran
            $allAngsuran = $pinjaman->angsuran()->orderBy('angsuran_ke')->get();
            $saldo = $totalTagihan;
            foreach ($allAngsuran as $a) {
                $saldo = max(0, $saldo - $a->jumlah_bayar);
                $a->updateQuietly(['sisa_pinjaman' => $saldo]);
            }

            $pinjaman->update([
                'sisa_pinjaman' => $saldo,
                'status'        => $saldo <= 0 ? 'lunas' : 'aktif',
            ]);
        });

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('success', 'Pinjaman berhasil diperbarui.');
    }

    public function kalkulasi(Request $request)
    {
        $request->validate([
            'pinjaman_pokok'  => 'required|numeric|min:1',
            'bunga'           => 'required|numeric|min:0|max:100',
            'nominal_setoran' => 'required|numeric|min:1',
            'biaya_tambahan'  => 'nullable|numeric|min:0',
        ]);

        return response()->json(
            $this->pinjamanService->kalkulasi(
                $request->pinjaman_pokok,
                $request->bunga,
                $request->nominal_setoran,
                $request->biaya_tambahan ?? 0
            )
        );
    }
}
