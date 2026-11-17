<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpCustomer;
use App\Models\KetahananPangan\KpEggProduction;
use App\Models\KetahananPangan\KpEggSale;
use App\Models\KetahananPangan\KpPartnerPurchase;
use App\Models\KetahananPangan\KpProduct;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KpSaleController extends Controller
{
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'ketahanan-pangan')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== 'manager_pusat' && $user->unit_id != $unit->id) {
            abort(403, 'Akses ditolak.');
        }
        return $unit;
    }

    private function syncEggStock(): float
    {
        $product = KpProduct::firstOrCreate(
            ['kode' => 'TELUR-BGS'],
            [
                'nama' => 'Telur Ayam Ras Bagus/Cokelat',
                'kategori' => 'Telur Bagus',
                'satuan' => 'kg',
                'harga_default' => 22000,
                'stok_tersedia' => 0
            ]
        );

        $totalProduksiAll = (float) KpEggProduction::sum('produksi_kg');
        $totalTerjualKandangAll = (float) KpEggSale::sum('ambil_kandang_kg');
        $calculatedEggStock = max(0, round($totalProduksiAll - $totalTerjualKandangAll, 2));

        if ((float) $product->stok_tersedia !== (float) $calculatedEggStock) {
            $product->stok_tersedia = $calculatedEggStock;
            $product->save();
        }

        return $calculatedEggStock;
    }

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $customerType = $request->input('customer_type', '');

        $currentStock = $this->syncEggStock();
        $product = KpProduct::where('kode', 'TELUR-BGS')->first();

        $customers = KpCustomer::orderBy('nama')->get();

        $query = KpEggSale::with('customer')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($customerType) {
            $query->where('customer_type', $customerType);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('nomor_transaksi', 'like', "%{$s}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$s}%");
            });
        }

        $sales = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(15)->withQueryString();

        $totalKg = (float) KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('jumlah_kg');
        $totalRp = (float) KpEggSale::whereBetween('tanggal', [$startDate, $endDate])->sum('total_harga');

        // Total pembelian rekanan kumulatif & pemakaian untuk penjualan
        $totalBeliRekanan = (float) KpPartnerPurchase::sum('jumlah_kg');
        $terpakaiRekanan = (float) KpEggSale::sum('ambil_rekanan_kg');
        $sisaStokRekanan = max(0, round($totalBeliRekanan - $terpakaiRekanan, 2));

        return Inertia::render('ketahanan pangan/Hewani/PenjualanTelur', [
            'unit' => $unit,
            'user' => $user,
            'product' => $product,
            'customers' => $customers,
            'sales' => $sales,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'customer_type' => $customerType,
                'search' => $request->input('search', ''),
            ],
            'summary' => [
                'stok_tersedia_kg' => (float) $currentStock,
                'stok_rekanan_kg' => $sisaStokRekanan,
                'total_stok_gabungan_kg' => (float) $currentStock + $sisaStokRekanan,
                'total_terjual_kg' => $totalKg,
                'total_pendapatan_rp' => $totalRp,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'nullable|exists:kp_customers,id',
            'nama_pelanggan' => 'required|string|max:255',
            'customer_type' => 'nullable|string',
            'jumlah_kg' => 'required|numeric|min:0.01',
            'sumber_stok' => 'nullable|in:kandang,rekanan,gabungan',
            'ambil_kandang_kg' => 'nullable|numeric|min:0',
            'ambil_rekanan_kg' => 'nullable|numeric|min:0',
            'harga_per_kg' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,Qris,Hutang',
            'keterangan' => 'nullable|string',
        ]);

        if (empty($validated['customer_type'])) {
            $validated['customer_type'] = 'Eceran';
        }

        $stokKandang = $this->syncEggStock();

        $totalBeliRekanan = (float) KpPartnerPurchase::sum('jumlah_kg');
        $terpakaiRekanan = (float) KpEggSale::sum('ambil_rekanan_kg');
        $sisaStokRekanan = max(0, round($totalBeliRekanan - $terpakaiRekanan, 2));

        $totalKebutuhan = (float) $validated['jumlah_kg'];
        $sumberStok = $validated['sumber_stok'] ?? 'kandang';

        $ambilKandang = isset($validated['ambil_kandang_kg']) ? (float) $validated['ambil_kandang_kg'] : 0;
        $ambilRekanan = isset($validated['ambil_rekanan_kg']) ? (float) $validated['ambil_rekanan_kg'] : 0;

        if ($sumberStok === 'kandang') {
            $ambilKandang = $totalKebutuhan;
            $ambilRekanan = 0;
        } elseif ($sumberStok === 'rekanan') {
            $ambilKandang = 0;
            $ambilRekanan = $totalKebutuhan;
        } elseif ($sumberStok === 'gabungan') {
            if (round($ambilKandang + $ambilRekanan, 2) != round($totalKebutuhan, 2)) {
                $ambilKandang = min($stokKandang, $totalKebutuhan);
                $ambilRekanan = round($totalKebutuhan - $ambilKandang, 2);
            }
        }

        if ($ambilKandang > $stokKandang) {
            return redirect()->back()->withErrors([
                'jumlah_kg' => "Stok telur kandang/BUMDes tidak mencukupi! Dibutuhkan: {$ambilKandang} kg, Tersedia: {$stokKandang} kg."
            ]);
        }

        if ($ambilRekanan > $sisaStokRekanan) {
            return redirect()->back()->withErrors([
                'jumlah_kg' => "Stok telur rekanan tidak mencukupi! Dibutuhkan: {$ambilRekanan} kg, Tersedia: {$sisaStokRekanan} kg."
            ]);
        }

        $totalHarga = round($totalKebutuhan * $validated['harga_per_kg'], 2);
        $kembalian = max(0, $validated['jumlah_pembayaran'] - $totalHarga);
        $statusPembayaran = ($validated['jumlah_pembayaran'] >= $totalHarga && $validated['metode_pembayaran'] !== 'Hutang') ? 'Lunas' : 'Belum Lunas';

        $dateCode = Carbon::parse($validated['tanggal'])->format('ymd');
        $randomSeq = str_pad(KpEggSale::where('tanggal', $validated['tanggal'])->count() + 1, 3, '0', STR_PAD_LEFT);
        $nomorTransaksi = "STR-TLR-{$dateCode}-{$randomSeq}";

        DB::transaction(function () use ($validated, $ambilKandang, $ambilRekanan, $sumberStok, $totalKebutuhan, $totalHarga, $kembalian, $statusPembayaran, $nomorTransaksi) {
            $sale = KpEggSale::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'customer_id' => $validated['customer_id'] ?? null,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'customer_type' => $validated['customer_type'],
                'jumlah_kg' => $totalKebutuhan,
                'ambil_kandang_kg' => $ambilKandang,
                'ambil_rekanan_kg' => $ambilRekanan,
                'sumber_stok' => $sumberStok,
                'harga_per_kg' => $validated['harga_per_kg'],
                'total_harga' => $totalHarga,
                'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                'kembalian' => $kembalian,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $statusPembayaran,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Catat ke Arus Kas (Debit/Pemasukan) hanya jika menerima pembayaran real
            if ($validated['metode_pembayaran'] !== 'Hutang' && $validated['jumlah_pembayaran'] > 0) {
                $pemasukanKas = min($totalHarga, $validated['jumlah_pembayaran']);
                $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
                $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
                $newSaldo = $currentSaldo + $pemasukanKas;

                $ketSumber = '';
                if ($sumberStok === 'gabungan') {
                    $ketSumber = " [Gabungan: Kandang {$ambilKandang} kg + Rekanan {$ambilRekanan} kg]";
                } elseif ($sumberStok === 'rekanan') {
                    $ketSumber = " [Stok Rekanan]";
                }

                KpCashFlow::create([
                    'unit_kategori' => 'hewani',
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Penjualan Telur: ' . $sale->nomor_transaksi . ' (' . $sale->nama_pelanggan . ')' . $ketSumber,
                    'debit' => $pemasukanKas,
                    'kredit' => 0,
                    'saldo_berjalan' => $newSaldo,
                    'sumber_tipe' => 'egg_sale',
                    'sumber_id' => $sale->id,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', "Penjualan berhasil dicatat dengan Nomor Struk: {$nomorTransaksi}");
    }

    public function update(Request $request, $id)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'nullable|exists:kp_customers,id',
            'nama_pelanggan' => 'required|string|max:255',
            'customer_type' => 'nullable|string',
            'jumlah_kg' => 'required|numeric|min:0.01',
            'sumber_stok' => 'nullable|in:kandang,rekanan,gabungan',
            'ambil_kandang_kg' => 'nullable|numeric|min:0',
            'ambil_rekanan_kg' => 'nullable|numeric|min:0',
            'harga_per_kg' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,Qris,Hutang',
            'keterangan' => 'nullable|string',
        ]);

        $sale = KpEggSale::findOrFail($id);

        if (empty($validated['customer_type'])) {
            $validated['customer_type'] = 'Eceran';
        }

        // Hitung ketersediaan selain transaksi ini
        $totalProduced = (float) KpEggProduction::sum('produksi_kg');
        $otherSoldKandang = (float) KpEggSale::where('id', '!=', $id)->sum('ambil_kandang_kg');
        $availableKandang = max(0, round($totalProduced - $otherSoldKandang, 2));

        $totalBeliRekanan = (float) KpPartnerPurchase::sum('jumlah_kg');
        $otherTerpakaiRekanan = (float) KpEggSale::where('id', '!=', $id)->sum('ambil_rekanan_kg');
        $sisaStokRekanan = max(0, round($totalBeliRekanan - $otherTerpakaiRekanan, 2));

        $totalKebutuhan = (float) $validated['jumlah_kg'];
        $sumberStok = $validated['sumber_stok'] ?? 'kandang';

        $ambilKandang = isset($validated['ambil_kandang_kg']) ? (float) $validated['ambil_kandang_kg'] : 0;
        $ambilRekanan = isset($validated['ambil_rekanan_kg']) ? (float) $validated['ambil_rekanan_kg'] : 0;

        if ($sumberStok === 'kandang') {
            $ambilKandang = $totalKebutuhan;
            $ambilRekanan = 0;
        } elseif ($sumberStok === 'rekanan') {
            $ambilKandang = 0;
            $ambilRekanan = $totalKebutuhan;
        } elseif ($sumberStok === 'gabungan') {
            if (round($ambilKandang + $ambilRekanan, 2) != round($totalKebutuhan, 2)) {
                $ambilKandang = min($availableKandang, $totalKebutuhan);
                $ambilRekanan = round($totalKebutuhan - $ambilKandang, 2);
            }
        }

        if ($ambilKandang > $availableKandang) {
            return redirect()->back()->withErrors([
                'jumlah_kg' => "Stok telur kandang/BUMDes tidak mencukupi untuk perubahan ini! Dibutuhkan: {$ambilKandang} kg, Tersedia: {$availableKandang} kg."
            ]);
        }

        if ($ambilRekanan > $sisaStokRekanan) {
            return redirect()->back()->withErrors([
                'jumlah_kg' => "Stok telur rekanan tidak mencukupi untuk perubahan ini! Dibutuhkan: {$ambilRekanan} kg, Tersedia: {$sisaStokRekanan} kg."
            ]);
        }

        $totalHarga = round($totalKebutuhan * $validated['harga_per_kg'], 2);
        $kembalian = max(0, $validated['jumlah_pembayaran'] - $totalHarga);
        $statusPembayaran = ($validated['jumlah_pembayaran'] >= $totalHarga && $validated['metode_pembayaran'] !== 'Hutang') ? 'Lunas' : 'Belum Lunas';

        DB::transaction(function () use ($sale, $validated, $ambilKandang, $ambilRekanan, $sumberStok, $totalKebutuhan, $totalHarga, $kembalian, $statusPembayaran) {
            $sale->update([
                'tanggal' => $validated['tanggal'],
                'customer_id' => $validated['customer_id'] ?? null,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'customer_type' => $validated['customer_type'],
                'jumlah_kg' => $totalKebutuhan,
                'ambil_kandang_kg' => $ambilKandang,
                'ambil_rekanan_kg' => $ambilRekanan,
                'sumber_stok' => $sumberStok,
                'harga_per_kg' => $validated['harga_per_kg'],
                'total_harga' => $totalHarga,
                'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                'kembalian' => $kembalian,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $statusPembayaran,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Update CashFlow
            $cash = KpCashFlow::where('sumber_tipe', 'egg_sale')->where('sumber_id', $sale->id)->first();
            if ($validated['metode_pembayaran'] !== 'Hutang' && $validated['jumlah_pembayaran'] > 0) {
                $pemasukanKas = min($totalHarga, $validated['jumlah_pembayaran']);
                if ($cash) {
                    $cash->update([
                        'tanggal' => $validated['tanggal'],
                        'uraian' => 'Penjualan Telur: ' . $sale->nomor_transaksi . ' (' . $sale->nama_pelanggan . ')',
                        'debit' => $pemasukanKas,
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);
                } else {
                    $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
                    $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
                    KpCashFlow::create([
                        'unit_kategori' => 'hewani',
                        'tanggal' => $validated['tanggal'],
                        'uraian' => 'Penjualan Telur: ' . $sale->nomor_transaksi . ' (' . $sale->nama_pelanggan . ')',
                        'debit' => $pemasukanKas,
                        'kredit' => 0,
                        'saldo_berjalan' => $currentSaldo + $pemasukanKas,
                        'sumber_tipe' => 'egg_sale',
                        'sumber_id' => $sale->id,
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);
                }
            } else {
                if ($cash) {
                    $cash->delete();
                }
            }

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', "Data penjualan {$sale->nomor_transaksi} berhasil diperbarui.");
    }

    public function struk($id)
    {
        $sale = KpEggSale::with('customer')->findOrFail($id);
        $unit = Unit::where('slug', 'ketahanan-pangan')->first();

        return view('ketahanan_pangan.struk', [
            'sale' => $sale,
            'unit' => $unit,
        ]);
    }

    public function destroy($id)
    {
        $this->authorizeUnit();

        DB::transaction(function () use ($id) {
            $sale = KpEggSale::findOrFail($id);

            // Hapus referensi cashflow
            KpCashFlow::where('sumber_tipe', 'egg_sale')->where('sumber_id', $sale->id)->delete();

            $sale->delete();

            $this->syncEggStock();
        });

        return redirect()->back()->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok dikembalikan.');
    }
}
