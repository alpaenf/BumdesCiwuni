<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengaturanBiayaController extends Controller
{
    public function index(): Response
    {
        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        $keys = [
            $prefix . 'biaya_admin_sembako',
            $prefix . 'biaya_admin_tarik_reguler',
        ];

        $biaya = Setting::getMany($keys, [
            $prefix . 'biaya_admin_sembako'       => 20000,
            $prefix . 'biaya_admin_tarik_reguler' => 0,
        ]);

        return Inertia::render('SimpanPinjam/Pengaturan/Biaya', [
            'biaya' => [
                'biaya_admin_sembako'       => $biaya[$prefix . 'biaya_admin_sembako'],
                'biaya_admin_tarik_reguler' => $biaya[$prefix . 'biaya_admin_tarik_reguler'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'biaya_admin_sembako'       => ['required', 'numeric', 'min:0'],
            'biaya_admin_tarik_reguler' => ['required', 'numeric', 'min:0'],
        ]);

        $unitId = auth()->user()->unit_id;
        $prefix = $unitId ? "unit_{$unitId}_" : "global_";

        foreach ($data as $key => $value) {
            Setting::set($prefix . $key, $value);
        }

        return back()->with('success', 'Pengaturan biaya berhasil disimpan.');
    }
}
