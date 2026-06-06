<?php

namespace App\Http\Controllers\SimpanPinjam;

use App\Http\Controllers\Controller;
use App\Models\GaleriUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'foto'       => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $file = $request->file('foto');
        $tempPath = $file->getRealPath();
        $info = getimagesize($tempPath);
        
        $filename = time() . '_' . uniqid() . '.webp';
        $path = 'galeri/' . $filename;

        $image = null;
        if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($tempPath);
        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($tempPath);
        elseif ($info['mime'] == 'image/webp') $image = imagecreatefromwebp($tempPath);

        if ($image) {
            // Resize max width 1200px
            $width = imagesx($image);
            $height = imagesy($image);
            $newWidth = $width > 1200 ? 1200 : $width;
            $newHeight = intval($height * ($newWidth / $width));

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency
            if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Kompres dan simpan ke Storage (quality 70%)
            ob_start();
            imagewebp($newImage, null, 70);
            $imageContent = ob_get_clean();
            
            Storage::disk('public')->put($path, $imageContent);

            imagedestroy($image);
            imagedestroy($newImage);
        } else {
            // Fallback simpan original jika gagal kompres
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'galeri/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($tempPath));
        }

        $urutan = GaleriUnit::where('unit', 'simpan-pinjam')->max('urutan') + 1;

        GaleriUnit::create([
            'unit'       => 'simpan-pinjam',
            'foto'       => '/storage/' . $path,
            'keterangan' => $request->keterangan,
            'urutan'     => $urutan,
        ]);

        return redirect()->back()->with('success', 'Foto berhasil diupload dan dikompres (WebP)!');
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
        // Hapus file fisik dari Storage
        $path = str_replace('/storage/', '', $galeri->foto);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $galeri->delete();

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }
}
