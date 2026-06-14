<?php

namespace App\Exports;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class TabunganExport implements FromView, ShouldAutoSize
{
    private $transaksi;
    private $filters;
    private $summary;
    private $jenis;

    public function __construct(Request $request)
    {
        $query = TransaksiTabungan::with('tabungan.nasabah');

        $this->jenis = $request->input('jenis', 'reguler');
        if ($this->jenis !== 'gabungan') {
            $query->whereHas('tabungan', function ($q) {
                $q->where('jenis_tabungan', $this->jenis);
            });
        }

        if ($request->filled('start_date')) $query->whereDate('tanggal', '>=', $request->start_date);
        if ($request->filled('end_date'))   $query->whereDate('tanggal', '<=', $request->end_date);
        if ($request->filled('tanggal'))    $query->whereDate('tanggal', '=', $request->tanggal);
        if ($request->filled('bulan')) {
            $query->whereYear('tanggal', substr($request->bulan, 0, 4))
                  ->whereMonth('tanggal', substr($request->bulan, 5, 2));
        }

        $this->transaksi = $query->orderBy('tanggal')->get();
        $this->filters = $request->only(['start_date', 'end_date', 'tanggal', 'bulan']);
        $this->summary = [
            'total_setoran'   => $this->transaksi->where('jenis_transaksi', 'setor')->sum('nominal'),
            'total_penarikan' => $this->transaksi->whereIn('jenis_transaksi', ['tarik_tunai', 'tarik_sembako'])->sum('nominal'),
            'total_admin'     => $this->transaksi->sum('administrasi'),
        ];
    }

    public function view(): View
    {
        return view('exports.simpan-pinjam.laporan.tabungan', [
            'transaksi' => $this->transaksi,
            'filters'   => $this->filters,
            'summary'   => $this->summary,
            'jenis'     => $this->jenis,
            'isExcel'   => true,
        ]);
    }
}
