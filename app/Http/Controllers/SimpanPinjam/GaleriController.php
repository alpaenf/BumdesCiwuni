<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\GaleriUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GaleriController extends Controller
{
    /**
     * Halaman kelola galeri (admin unit).
     */
    public function index(): Response
    {
        $galeri = GaleriUnit::where('unit', 'simpan-pinjam')
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('SimpanPinjam/Admin/Galeri', [
            'galeri' => $galeri,
        ]);
    }

    /**
     * Upload foto baru ke galeri.
     */
    public function store(Request $request)
    {
        $request->validate([
            'foto'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file     = $request->file('foto');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path     = public_path('uploads/galeri');

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $file->move($path, $filename);

        $urutan = GaleriUnit::where('unit', 'simpan-pinjam')->max('urutan') + 1;

        GaleriUnit::create([
            'unit'       => 'simpan-pinjam',
            'foto'       => '/uploads/galeri/' . $filename,
            'keterangan' => $request->keterangan,
            'urutan'     => $urutan,
        ]);

        return redirect()->back()->with('success', 'Foto berhasil ditambahkan ke galeri.');
    }

    /**
     * Update keterangan atau urutan foto.
     */
    public function update(Request $request, GaleriUnit $galeri)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:255',
            'urutan'     => 'nullable|integer|min:0',
        ]);

        $galeri->update($request->only(['keterangan', 'urutan']));

        return redirect()->back()->with('success', 'Galeri berhasil diperbarui.');
    }

    /**
     * Hapus foto dari galeri.
     */
    public function destroy(GaleriUnit $galeri)
    {
        // Hapus file fisik jika ada
        $filePath = public_path($galeri->foto);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $galeri->delete();

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }
}
