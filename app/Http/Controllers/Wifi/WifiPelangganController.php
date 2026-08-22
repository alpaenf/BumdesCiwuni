<?php

namespace App\Http\Controllers\Wifi;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePelangganWifiRequest;
use App\Http\Requests\UpdatePelangganWifiRequest;
use App\Models\PelangganWifi;
use App\Models\Unit;
use App\Traits\ComputesWifiStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WifiPelangganController extends Controller
{
    use ComputesWifiStatus;
    /**
     * Authorize: only admin or user belonging to the wifi unit.
     */
    private function authorizeUnit(): Unit
    {
        $unit = Unit::where('slug', 'wifi')->firstOrFail();
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->unit_id != $unit->id) {
            abort(403, 'Unauthorized access to WiFi unit.');
        }
        return $unit;
    }

    /**
     * Show the interactive map of customer locations.
     */
    public function peta(): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        // All pelanggan that have GPS coordinates
        $allPelanggan = PelangganWifi::whereNotNull('gps_lat')
            ->whereNotNull('gps_long')
            ->get([
                'id', 'no', 'nama', 'paket', 'alamat', 'rt', 'rw',
                'no_wa', 'gelombang', 'status_1_15', 'status_16_30',
                'gps_lat', 'gps_long', 'foto_rumah',
                'total_tarikan', 'tanggal_daftar',
            ]);

        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $latestPembayaran = \App\Models\PembayaranWifi::whereIn('pelanggan_wifi_id', $allPelanggan->pluck('id'))
            ->where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->get()
            ->keyBy('pelanggan_wifi_id');

        $allPelanggan->transform(function ($item) use ($latestPembayaran) {
            $pay = $latestPembayaran->get($item->id);
            $item->current_status = $this->computeCurrentStatus($item, $pay);
            return $item;
        });

        // All pelanggan (including those without GPS) for sidebar list
        $tanpaGps = PelangganWifi::whereNull('gps_lat')
            ->orWhereNull('gps_long')
            ->count();

        // Unique paket & RT/RW for filter options
        $paketOptions = PelangganWifi::select('paket')
            ->whereNotNull('paket')->distinct()->orderBy('paket')->pluck('paket');
        $rtOptions    = PelangganWifi::select('rt')
            ->whereNotNull('rt')->distinct()->orderBy('rt')->pluck('rt');
        $rwOptions    = PelangganWifi::select('rw')
            ->whereNotNull('rw')->distinct()->orderBy('rw')->pluck('rw');

        return Inertia::render('Wifi/Peta', [
            'unit'         => $unit,
            'user'         => $user,
            'pelanggan'    => $allPelanggan,
            'tanpaGps'     => $tanpaGps,
            'paketOptions' => $paketOptions,
            'rtOptions'    => $rtOptions,
            'rwOptions'    => $rwOptions,
            'stats' => [
                'total'     => PelangganWifi::count(),
                'ada_gps'   => $allPelanggan->count(),
                'gel1'      => PelangganWifi::where('gelombang', '1_15')->count(),
                'gel2'      => PelangganWifi::where('gelombang', '16_30')->count(),
            ],
        ]);
    }

    /**
     * Display paginated list of pelanggan with search, filter, sort.
     */
    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $query = PelangganWifi::with('provider');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_id_pel', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('paket', 'like', "%{$search}%")
                  ->orWhere('rt', 'like', "%{$search}%")
                  ->orWhere('rw', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($paket = $request->input('paket')) {
            $query->where('paket', $paket);
        }

        // Filter by gelombang (jadwal warga)
        if ($gelombang = $request->input('gelombang')) {
            $query->where('gelombang', $gelombang);
        }

        // Filter by status (derived from latest pembayaran — handled client-side via attached data)
        // We also support filtering via status param against current_status derived below.
        if ($rt = $request->input('rt')) {
            $query->where('rt', $rt);
        }
        if ($rw = $request->input('rw')) {
            $query->where('rw', $rw);
        }
        if ($tglDari = $request->input('tanggal_dari')) {
            $query->whereDate('tanggal_daftar', '>=', $tglDari);
        }
        if ($tglSampai = $request->input('tanggal_sampai')) {
            $query->whereDate('tanggal_daftar', '<=', $tglSampai);
        }

        // Sort
        $sortField = $request->input('sort', 'no');
        $sortDir   = $request->input('dir', 'asc');
        $allowedSorts = [
            'no', 'nama', 'tanggal_daftar', 'paket', 'nik', 'rt', 'rw', 'no_id_pel', 'no_wa',
            'total_dasar_tarikan_non_ppn', 'ppn_dan_pph', 'ppn_pph', 'total_tarikan',
            'bagi_hasil_bumdes', 'hasil_bumdes', 'nota_bayar_provider', 'total_provider',
            'gelombang',
        ];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = $perPage === 0 ? PelangganWifi::count() : 25;
        }

        $pelanggan = $query->paginate($perPage)->withQueryString();

        // Attach current status from latest pembayaran (bulan ini / tahun ini sesuai gelombang masing2)
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $pelangganIds = $pelanggan->pluck('id');

        // Get latest payment for each pelanggan for current month
        $latestPembayaran = \App\Models\PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganIds)
            ->where('periode_bulan', $bulanIni)
            ->where('periode_tahun', $tahunIni)
            ->get()
            ->keyBy('pelanggan_wifi_id');

        // Also fetch last payment ever (any month) for fallback status display
        $lastEverPembayaran = \App\Models\PembayaranWifi::whereIn('pelanggan_wifi_id', $pelangganIds)
            ->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')->orderByDesc('id')
            ->get()
            ->unique('pelanggan_wifi_id')
            ->keyBy('pelanggan_wifi_id');

        $pelanggan->getCollection()->transform(function ($item) use ($latestPembayaran, $lastEverPembayaran) {
            $thisMonth = $latestPembayaran->get($item->id);
            $lastEver  = $lastEverPembayaran->get($item->id);
            // If paid this month, trust that status; otherwise use auto-isolir logic
            $item->current_status  = $thisMonth
                ? $thisMonth->status
                : $this->computeCurrentStatus($item, null);
            $item->last_pembayaran = $thisMonth ?? $lastEver;
            return $item;
        });

        // Unique filter options
        $paketOptions   = PelangganWifi::select('paket')->whereNotNull('paket')->distinct()->orderBy('paket')->pluck('paket');
        $rtOptions      = PelangganWifi::select('rt')->whereNotNull('rt')->distinct()->orderBy('rt')->pluck('rt');
        $rwOptions      = PelangganWifi::select('rw')->whereNotNull('rw')->distinct()->orderBy('rw')->pluck('rw');

        $providersList  = \App\Models\ProviderWifi::orderBy('nama_provider')->get();

        return Inertia::render('Wifi/Pelanggan', [
            'unit'          => $unit,
            'user'          => $user,
            'pelanggan'     => $pelanggan,
            'paketOptions'  => $paketOptions,
            'rtOptions'     => $rtOptions,
            'rwOptions'     => $rwOptions,
            'providersList' => $providersList,
            'filters'       => $request->only(['search', 'paket', 'gelombang', 'status', 'rt', 'rw', 'tanggal_dari', 'tanggal_sampai', 'sort', 'dir', 'per_page']),
        ]);
    }

    /**
     * Store new pelanggan.
     */
    public function store(StorePelangganWifiRequest $request): RedirectResponse
    {
        $this->authorizeUnit();

        $data = $request->validated();

        // Handle foto upload
        if ($request->hasFile('foto_rumah')) {
            $file     = $request->file('foto_rumah');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pelanggan_wifi'), $filename);
            $data['foto_rumah'] = $filename;
        }

        // Auto calculate BUMDes 9% & Provider 91% if total_tarikan is provided
        if (!empty($data['total_tarikan']) && (empty($data['hasil_bumdes']) || $data['hasil_bumdes'] == 0)) {
            $pct = !empty($data['bagi_hasil_bumdes']) && $data['bagi_hasil_bumdes'] <= 100 ? ($data['bagi_hasil_bumdes'] / 100) : 0.09;
            $data['hasil_bumdes']  = round($data['total_tarikan'] * $pct);
            $data['total_provider'] = $data['total_tarikan'] - $data['hasil_bumdes'];
        }

        PelangganWifi::create($data);

        return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /**
     * Update existing pelanggan.
     */
    public function update(UpdatePelangganWifiRequest $request, PelangganWifi $pelanggan): RedirectResponse
    {
        $this->authorizeUnit();

        $data = $request->validated();

        // Handle foto upload
        if ($request->hasFile('foto_rumah')) {
            // Delete old photo
            if ($pelanggan->foto_rumah) {
                $oldPath = public_path('uploads/pelanggan_wifi/' . $pelanggan->foto_rumah);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file     = $request->file('foto_rumah');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pelanggan_wifi'), $filename);
            $data['foto_rumah'] = $filename;
        } else {
            unset($data['foto_rumah']); // Don't overwrite existing if not uploading new
        }

        // Auto calculate BUMDes 9% & Provider 91% if total_tarikan is updated
        if (!empty($data['total_tarikan']) && (empty($data['hasil_bumdes']) || $data['hasil_bumdes'] == 0)) {
            $pct = !empty($data['bagi_hasil_bumdes']) && $data['bagi_hasil_bumdes'] <= 100 ? ($data['bagi_hasil_bumdes'] / 100) : 0.09;
            $data['hasil_bumdes']  = round($data['total_tarikan'] * $pct);
            $data['total_provider'] = $data['total_tarikan'] - $data['hasil_bumdes'];
        }

        $pelanggan->update($data);

        return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Delete pelanggan.
     */
    public function destroy(PelangganWifi $pelanggan): RedirectResponse
    {
        $this->authorizeUnit();

        // Delete photo file
        if ($pelanggan->foto_rumah) {
            $oldPath = public_path('uploads/pelanggan_wifi/' . $pelanggan->foto_rumah);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $pelanggan->delete();

        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Export all pelanggan as formatted XLSX Excel.
     */
    public function export(Request $request)
    {
        $this->authorizeUnit();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PelangganWifiExport($request),
            'database_pelanggan_wifi_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Import pelanggan from CSV/Excel file.
     * Returns JSON with preview data or errors.
     */
    public function import(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->authorizeUnit();

        $request->validate([
            'file'    => 'required|file|mimes:csv,txt|max:10240',
            'confirm' => 'nullable|boolean',
        ]);

        $file   = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Read header
        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return response()->json(['error' => 'File CSV kosong atau tidak valid.'], 422);
        }

        // Strip BOM if present
        $rawHeader[0] = ltrim($rawHeader[0], "\xEF\xBB\xBF");

        $expectedHeaders = [
            'No', 'Nama', 'Tanggal Daftar', 'Paket', 'NIK', 'Alamat', 'RT', 'RW',
            'No ID Pel', 'No WA', 'Total Dasar Tarikan Non PPN', 'PPN dan PPH', 'PPN/PPH',
            'Total Tarikan', 'Bagi Hasil BUMDes', 'Hasil BUMDes', 'Nota Bayar Provider',
            'Total Provider', 'Status 1-15', 'Status 16-30', 'GPS Long', 'GPS Lat', 'Foto Rumah',
        ];

        $headerOk = true;
        foreach ($expectedHeaders as $i => $eh) {
            if (!isset($rawHeader[$i]) || trim($rawHeader[$i]) !== $eh) {
                $headerOk = false;
                break;
            }
        }

        if (!$headerOk) {
            fclose($handle);
            return response()->json([
                'error' => 'Header CSV tidak sesuai. Pastikan urutan kolom sama dengan template export.'
            ], 422);
        }

        $dbColumns = [
            'no', 'nama', 'tanggal_daftar', 'paket', 'nik', 'alamat', 'rt', 'rw',
            'no_id_pel', 'no_wa', 'total_dasar_tarikan_non_ppn', 'ppn_dan_pph', 'ppn_pph',
            'total_tarikan', 'bagi_hasil_bumdes', 'hasil_bumdes', 'nota_bayar_provider',
            'total_provider', 'status_1_15', 'status_16_30', 'gps_long', 'gps_lat', 'foto_rumah',
        ];

        $rows   = [];
        $errors = [];
        $lineNo = 1;

        while (($csvRow = fgetcsv($handle)) !== false) {
            $lineNo++;
            if (count($csvRow) < count($dbColumns)) {
                // Pad missing columns
                $csvRow = array_pad($csvRow, count($dbColumns), null);
            }

            $rowData = array_combine($dbColumns, array_slice($csvRow, 0, count($dbColumns)));

            // Validate per row
            $rowErrors = [];

            if (empty($rowData['nama'])) {
                $rowErrors[] = 'Kolom Nama wajib diisi';
            }

            if (!empty($rowData['tanggal_daftar'])) {
                try {
                    $rowData['tanggal_daftar'] = date('Y-m-d', strtotime($rowData['tanggal_daftar']));
                } catch (\Exception $e) {
                    $rowErrors[] = 'Format tanggal tidak valid';
                }
            }

            if (!empty($rowData['status_1_15']) && !in_array($rowData['status_1_15'], ['LUNAS', 'TUNGGAKAN', 'ISOLIR'])) {
                $rowErrors[] = 'Status 1-15 tidak valid (LUNAS/TUNGGAKAN/ISOLIR)';
            }
            if (!empty($rowData['status_16_30']) && !in_array($rowData['status_16_30'], ['LUNAS', 'TUNGGAKAN', 'ISOLIR'])) {
                $rowErrors[] = 'Status 16-30 tidak valid (LUNAS/TUNGGAKAN/ISOLIR)';
            }

            // Check duplicate no_id_pel
            if (!empty($rowData['no_id_pel'])) {
                $exists = PelangganWifi::where('no_id_pel', $rowData['no_id_pel'])->exists();
                if ($exists) {
                    $rowErrors[] = "No ID Pel '{$rowData['no_id_pel']}' sudah ada di database";
                }
            }

            // Convert empty strings to null for numeric fields
            $numericFields = [
                'total_dasar_tarikan_non_ppn', 'ppn_dan_pph', 'ppn_pph', 'total_tarikan',
                'bagi_hasil_bumdes', 'hasil_bumdes', 'nota_bayar_provider', 'total_provider',
                'gps_long', 'gps_lat',
            ];
            foreach ($numericFields as $nf) {
                if ($rowData[$nf] === '' || $rowData[$nf] === null) {
                    $rowData[$nf] = null;
                } else {
                    $rowData[$nf] = is_numeric($rowData[$nf]) ? (float) $rowData[$nf] : null;
                }
            }

            $rows[] = ['line' => $lineNo, 'data' => $rowData, 'errors' => $rowErrors];
            if (!empty($rowErrors)) {
                $errors[] = ['line' => $lineNo, 'nama' => $rowData['nama'] ?? '(kosong)', 'errors' => $rowErrors];
            }
        }

        fclose($handle);

        // If confirm=true, save rows without errors
        if ($request->boolean('confirm') && empty($errors)) {
            foreach ($rows as $row) {
                if (empty($row['errors'])) {
                    $d = $row['data'];
                    unset($d['foto_rumah']); // Skip foto on import (files handled separately)
                    PelangganWifi::create($d);
                }
            }
            return response()->json(['success' => true, 'imported' => count($rows)]);
        }

        return response()->json([
            'preview' => array_slice($rows, 0, 10),
            'total'   => count($rows),
            'errors'  => $errors,
            'valid'   => count($rows) - count($errors),
        ]);
    }

    /**
     * Printable Super Detailed PDF / HTML Report of Pelanggan Data
     */
    public function cetakPdf(Request $request)
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $query = PelangganWifi::with('provider');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_id_pel', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('paket', 'like', "%{$search}%")
                  ->orWhere('rt', 'like', "%{$search}%")
                  ->orWhere('rw', 'like', "%{$search}%");
            });
        }

        if ($paket = $request->input('paket')) {
            $query->where('paket', $paket);
        }
        if ($status115 = $request->input('status_1_15')) {
            $query->where('status_1_15', $status115);
        }
        if ($status1630 = $request->input('status_16_30')) {
            $query->where('status_16_30', $status1630);
        }
        if ($rt = $request->input('rt')) {
            $query->where('rt', $rt);
        }
        if ($rw = $request->input('rw')) {
            $query->where('rw', $rw);
        }

        $pelangganList = $query->orderBy('no')->orderBy('nama')->get();

        $stats = [
            'total_pelanggan'    => $pelangganList->count(),
            'total_tarikan'      => $pelangganList->sum('total_tarikan'),
            'total_hasil_bumdes' => $pelangganList->sum('hasil_bumdes'),
            'total_provider'     => $pelangganList->sum('total_provider'),
            'lunas_115'          => $pelangganList->where('status_1_15', 'LUNAS')->count(),
            'tunggakan_115'      => $pelangganList->where('status_1_15', 'TUNGGAKAN')->count(),
            'isolir_115'        => $pelangganList->where('status_1_15', 'ISOLIR')->count(),
        ];

        return view('wifi.cetak_pelanggan_pdf', compact('unit', 'user', 'pelangganList', 'stats'));
    }
}
