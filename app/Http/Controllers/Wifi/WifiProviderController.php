<?php

namespace App\Http\Controllers\Wifi;

use App\Http\Controllers\Controller;
use App\Models\ProviderWifi;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WifiProviderController extends Controller
{
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
     * Display list of providers & revenue sharing schemes.
     */
    public function index(Request $request): Response
    {
        $unit = $this->authorizeUnit();
        $user = Auth::user();

        $query = ProviderWifi::withCount('pelanggan');

        if ($search = $request->input('search')) {
            $query->where('nama_provider', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
        }

        $providers = $query->orderBy('nama_provider')->get();

        return Inertia::render('Wifi/Provider', [
            'unit'      => $unit,
            'user'      => $user,
            'providers' => $providers,
            'filters'   => $request->only(['search']),
        ]);
    }

    /**
     * Store new provider.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUnit();

        $data = $request->validate([
            'nama_provider'    => 'required|string|max:255',
            'tipe_bagi_hasil'  => 'required|in:PERSENTASE,FLAT_ADMIN',
            'nilai_bagi_hasil' => 'required|numeric|min:0',
            'penanggung_jawab' => 'nullable|string|max:255',
            'no_telepon'       => 'nullable|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        ProviderWifi::create($data);

        return redirect()->back()->with('success', 'Provider berhasil ditambahkan.');
    }

    /**
     * Update existing provider.
     */
    public function update(Request $request, ProviderWifi $provider): RedirectResponse
    {
        $this->authorizeUnit();

        $data = $request->validate([
            'nama_provider'    => 'required|string|max:255',
            'tipe_bagi_hasil'  => 'required|in:PERSENTASE,FLAT_ADMIN',
            'nilai_bagi_hasil' => 'required|numeric|min:0',
            'penanggung_jawab' => 'nullable|string|max:255',
            'no_telepon'       => 'nullable|string|max:50',
            'keterangan'       => 'nullable|string|max:500',
        ]);

        $provider->update($data);

        return redirect()->back()->with('success', 'Data provider berhasil diperbarui.');
    }

    /**
     * Delete provider.
     */
    public function destroy(ProviderWifi $provider): RedirectResponse
    {
        $this->authorizeUnit();

        $provider->delete();

        return redirect()->back()->with('success', 'Provider berhasil dihapus.');
    }
}
