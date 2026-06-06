<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengaturanTabunganController extends Controller
{
    public function index(): Response
    {
        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        $keys = [
            $prefix . 'endapan_wajib_tabungan',
        ];

        $setting = Setting::getMany($keys, [
            $prefix . 'endapan_wajib_tabungan' => 20000,
        ]);

        return Inertia::render('SimpanPinjam/Pengaturan/Tabungan', [
            'pengaturan' => [
                'endapan_wajib' => $setting[$prefix . 'endapan_wajib_tabungan'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'endapan_wajib' => ['required', 'numeric', 'min:0'],
        ]);

        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        Setting::set($prefix . 'endapan_wajib_tabungan', $data['endapan_wajib']);

        return back()->with('success', 'Pengaturan tabungan berhasil disimpan.');
    }
}

