<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(): Response
    {
        $units = Unit::orderBy('urutan')->paginate(15);

        return Inertia::render('Portal/Cms/Unit/Index', [
            'units' => $units,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Portal/Cms/Unit/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'logo' => 'nullable',
            'thumbnail' => 'nullable',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:internal,external',
            'status' => 'required|in:aktif,coming_soon,nonaktif',
            'api_url' => 'nullable|string|max:500',
            'api_key' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['nama_unit']);

        // Ensure slug is unique
        $slug = $validated['slug'];
        $counter = 1;
        while (Unit::where('slug', $slug)->exists()) {
            $slug = $validated['slug'] . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('units/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('units/thumbnails', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        Unit::create($validated);

        return redirect()->route('portal.cms.unit.index')->with('success', 'Unit usaha berhasil ditambahkan.');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Portal/Cms/Unit/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:255',
            'logo' => 'nullable',
            'thumbnail' => 'nullable',
            'deskripsi' => 'nullable|string',
            'tipe' => 'required|in:internal,external',
            'status' => 'required|in:aktif,coming_soon,nonaktif',
            'api_url' => 'nullable|string|max:500',
            'api_key' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('logo')) {
            if ($unit->logo && Str::startsWith($unit->logo, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $unit->logo));
            }
            $path = $request->file('logo')->store('units/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        } else {
            // Keep existing if not changed, but if it was cleared (e.g., set to null via string), we might want to handle it.
            // But since Inertia preserves the string, it's fine.
            if ($request->has('logo') && $request->logo === null) {
                if ($unit->logo && Str::startsWith($unit->logo, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $unit->logo));
                }
                $validated['logo'] = null;
            } else {
                unset($validated['logo']); // don't override existing if it wasn't explicitly sent as null
            }
        }

        if ($request->hasFile('thumbnail')) {
            if ($unit->thumbnail && Str::startsWith($unit->thumbnail, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $unit->thumbnail));
            }
            $path = $request->file('thumbnail')->store('units/thumbnails', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        } else {
            if ($request->has('thumbnail') && $request->thumbnail === null) {
                if ($unit->thumbnail && Str::startsWith($unit->thumbnail, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $unit->thumbnail));
                }
                $validated['thumbnail'] = null;
            } else {
                unset($validated['thumbnail']);
            }
        }

        $unit->update($validated);

        return redirect()->route('portal.cms.unit.index')->with('success', 'Unit usaha berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('portal.cms.unit.index')->with('success', 'Unit usaha berhasil dihapus.');
    }
}
