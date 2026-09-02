<?php

namespace App\Exports;

use App\Models\PelangganWifi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Illuminate\Contracts\View\View;

class PelangganWifiExport implements FromView, ShouldAutoSize, WithDrawings
{
    use WithLogo;

    private $pelangganList;
    private $filters;
    private $summary;

    public function __construct(Request $request)
    {
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

        if ($providerId = $request->input('provider_id')) {
            $query->where('provider_wifi_id', $providerId);
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

        $this->pelangganList = $query->orderBy('no')->orderBy('nama')->get();

        $this->filters = $request->only(['search', 'paket', 'status_1_15', 'status_16_30', 'rt', 'rw']);
        $this->summary = [
            'total_pelanggan'    => $this->pelangganList->count(),
            'total_tarikan'      => $this->pelangganList->sum('total_tarikan'),
            'total_hasil_bumdes' => $this->pelangganList->sum('hasil_bumdes'),
            'total_provider'     => $this->pelangganList->sum('total_provider'),
            'lunas_115'          => $this->pelangganList->where('status_1_15', 'LUNAS')->count(),
            'tunggakan_115'      => $this->pelangganList->where('status_1_15', 'TUNGGAKAN')->count(),
            'isolir_115'        => $this->pelangganList->where('status_1_15', 'ISOLIR')->count(),
        ];
    }

    public function view(): View
    {
        return view('exports.wifi.pelanggan', [
            'pelangganList' => $this->pelangganList,
            'filters'       => $this->filters,
            'summary'       => $this->summary,
            'isExcel'       => true,
        ]);
    }
}
