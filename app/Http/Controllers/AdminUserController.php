<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /** Roles yang bisa dikelola dari CMS */
    private const MANAGEABLE_ROLES = ['admin', 'admin_unit', 'manager_pusat', 'manager'];

    public function showPinForm()
    {
        return Inertia::render('Portal/Cms/User/Pin');
    }

    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => 'required|string']);

        if ($request->pin === '123456') {
            $request->session()->put('pin_confirmed_at', time());
            return redirect()->intended(route('portal.cms.users.index'));
        }

        return back()->withErrors(['pin' => 'PIN keamanan tidak valid.']);
    }

    public function resetPin(Request $request)
    {
        $request->session()->forget('pin_confirmed_at');
        return redirect()->route('portal.cms.pin')->with('success', 'Sesi PIN telah dikunci. Masukkan PIN untuk melanjutkan.');
    }

    public function index()
    {
        $users = User::with('unit')
            ->orderByRaw("FIELD(role, 'admin', 'manager_pusat', 'admin_unit', 'manager')")
            ->orderBy('nama')
            ->paginate(15);

        return Inertia::render('Portal/Cms/User/Index', [
            'users' => $users,
            'roles' => $this->roleOptions(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Portal/Cms/User/Create', [
            'units' => Unit::orderBy('nama_unit')->get(),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => 'required|in:' . implode(',', self::MANAGEABLE_ROLES),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'unit_id'  => 'nullable|exists:units,id',
        ]);

        User::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
            'unit_id'  => $request->unit_id,
        ]);

        return redirect()->route('portal.cms.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {

        return Inertia::render('Portal/Cms/User/Edit', [
            'user'  => $user->load('unit'),
            'units' => Unit::orderBy('nama_unit')->get(),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {

        $request->validate([
            'nama'    => 'required|string|max:255',
            'email'   => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'    => 'required|in:' . implode(',', self::MANAGEABLE_ROLES),
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $data = [
            'nama'    => $request->nama,
            'email'   => $request->email,
            'role'    => $request->role,
            'unit_id' => $request->unit_id,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('portal.cms.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('portal.cms.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function roleOptions(): array
    {
        return [
            ['value' => 'admin',         'label' => 'Super Admin (Akses penuh Portal & Semua Unit)'],
            ['value' => 'manager_pusat', 'label' => 'Manager Pusat (Akses laporan Portal & Semua Unit)'],
            ['value' => 'admin_unit',    'label' => 'Admin Unit (Akses kelola unit spesifik)'],
            ['value' => 'manager',       'label' => 'Manager Unit (Akses terbatas unit spesifik)'],
        ];
    }
}
