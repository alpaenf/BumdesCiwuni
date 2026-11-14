<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCashFlow;
use App\Models\KetahananPangan\KpCustomer;
use App\Models\KetahananPangan\KpEggSale;
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

    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $customerType = $request->input('customer_type', '');

        $product = KpProduct::firstOrCreate(
            ['kode' => 'TELUR-BGS'],
            ['nama' => 'Telur Ayam Ras Bagus/Cokelat', 'kategori' => 'Telur Bagus', 'satuan' => 'kg', 'harga_default' => 22000, 'stok_tersedia' => 0]
        );

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
                'stok_tersedia_kg' => (float) $product->stok_tersedia,
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
            'customer_id' => 'nullable|exists:ketahanan_pangan.kp_customers,id',
            'nama_pelanggan' => 'required|string|max:255',
            'customer_type' => 'required|in:Eceran,Agen',
            'jumlah_kg' => 'required|numeric|min:0.01',
            'harga_per_kg' => 'required|numeric|min:0',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Tunai,Transfer,Qris,Hutang',
            'keterangan' => 'nullable|string',
        ]);

        $product = KpProduct::where('kode', 'TELUR-BGS')->firstOrFail();

        if ($product->stok_tersedia < $validated['jumlah_kg']) {
            return redirect()->back()->withErrors([
                'jumlah_kg' => "Stok telur tidak mencukupi! Tersedia: {$product->stok_tersedia} kg."
            ]);
        }

        $totalHarga = round($validated['jumlah_kg'] * $validated['harga_per_kg'], 2);
        $kembalian = max(0, $validated['jumlah_pembayaran'] - $totalHarga);
        $statusPembayaran = ($validated['jumlah_pembayaran'] >= $totalHarga && $validated['metode_pembayaran'] !== 'Hutang') ? 'Lunas' : 'Belum Lunas';

        // Generate nomor transaksi
        $dateCode = Carbon::parse($validated['tanggal'])->format('ymd');
        $randomSeq = str_pad(KpEggSale::where('tanggal', $validated['tanggal'])->count() + 1, 3, '0', STR_PAD_LEFT);
        $nomorTransaksi = "STR-TLR-{$dateCode}-{$randomSeq}";

        DB::connection('ketahanan_pangan')->transaction(function () use ($validated, $product, $totalHarga, $kembalian, $statusPembayaran, $nomorTransaksi) {
            $sale = KpEggSale::create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal' => $validated['tanggal'],
                'customer_id' => $validated['customer_id'] ?? null,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'customer_type' => $validated['customer_type'],
                'jumlah_kg' => $validated['jumlah_kg'],
                'harga_per_kg' => $validated['harga_per_kg'],
                'total_harga' => $totalHarga,
                'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                'kembalian' => $kembalian,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $statusPembayaran,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            // Potong stok telur
            $product->decrement('stok_tersedia', $validated['jumlah_kg']);

            // Catat ke Arus Kas (Debit/Pemasukan) hanya jika menerima pembayaran real
            if ($validated['metode_pembayaran'] !== 'Hutang' && $validated['jumlah_pembayaran'] > 0) {
                $pemasukanKas = min($totalHarga, $validated['jumlah_pembayaran']);
                $lastCash = KpCashFlow::where('unit_kategori', 'hewani')->orderByDesc('id')->first();
                $currentSaldo = $lastCash ? (float) $lastCash->saldo_berjalan : 0;
                $newSaldo = $currentSaldo + $pemasukanKas;

                KpCashFlow::create([
                    'unit_kategori' => 'hewani',
                    'tanggal' => $validated['tanggal'],
                    'uraian' => 'Penjualan Telur: ' . $sale->nomor_transaksi . ' (' . $sale->nama_pelanggan . ')',
                    'debit' => $pemasukanKas,
                    'kredit' => 0,
                    'saldo_berjalan' => $newSaldo,
                    'sumber_tipe' => 'egg_sale',
                    'sumber_id' => $sale->id,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->back()->with('success', "Penjualan berhasil dicatat dengan Nomor Struk: {$nomorTransaksi}");
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

        DB::connection('ketahanan_pangan')->transaction(function () use ($id) {
            $sale = KpEggSale::findOrFail($id);

            // Rollback stok telur
            $product = KpProduct::where('kode', 'TELUR-BGS')->first();
            if ($product) {
                $product->increment('stok_tersedia', $sale->jumlah_kg);
            }

            // Hapus referensi cashflow
            KpCashFlow::where('sumber_tipe', 'egg_sale')->where('sumber_id', $sale->id)->delete();

            $sale->delete();
        });

        return redirect()->back()->with('success', 'Transaksi penjualan berhasil dibatalkan dan stok dikembalikan.');
    }
}

