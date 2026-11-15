<?php

namespace App\Http\Controllers\KetahananPangan;

use App\Http\Controllers\Controller;
use App\Models\KetahananPangan\KpCustomer;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class KpCustomerController extends Controller
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

        $query = KpCustomer::query()
            ->withCount('eggSales')
            ->withSum('eggSales', 'total_harga')
            ->withSum('eggSales', 'jumlah_kg')
            ->with(['eggSales' => function ($q) {
                $q->orderByDesc('tanggal')->orderByDesc('id')->limit(1);
            }]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        $customers = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('ketahanan pangan/Hewani/Pelanggan', [
            'unit' => $unit,
            'user' => $user,
            'customers' => $customers,
            'filters' => [
                'search' => $request->input('search', ''),
                'customer_type' => $request->input('customer_type', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'nama' => 'required|string|max:255',
            'customer_type' => 'required|in:Eceran,Agen',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        if (empty($validated['tanggal'])) {
            $validated['tanggal'] = now()->toDateString();
        }

        KpCustomer::create($validated);

        return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeUnit();
        $customer = KpCustomer::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'nama' => 'required|string|max:255',
            'customer_type' => 'required|in:Eceran,Agen',
            'no_hp' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->authorizeUnit();
        $customer = KpCustomer::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Data pelanggan berhasil dihapus.');
    }
}

