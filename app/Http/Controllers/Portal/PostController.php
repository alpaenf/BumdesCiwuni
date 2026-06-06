<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::latest()->paginate(15);

        return Inertia::render('Portal/Cms/Berita/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Portal/Cms/Berita/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'thumbnail' => 'nullable',
            'ringkasan' => 'nullable|string|max:500',
            'konten' => 'required|string',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['judul']) . '-' . Str::random(5);

        if ($validated['status'] === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('posts/thumbnails', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        }

        Post::create($validated);

        return redirect()->route('portal.cms.berita.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Portal/Cms/Berita/Edit', [
            'post' => $post,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'thumbnail' => 'nullable',
            'ringkasan' => 'nullable|string|max:500',
            'konten' => 'required|string',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        if ($validated['status'] === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail && Str::startsWith($post->thumbnail, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->thumbnail));
            }
            $path = $request->file('thumbnail')->store('posts/thumbnails', 'public');
            $validated['thumbnail'] = '/storage/' . $path;
        } else {
            if ($request->has('thumbnail') && $request->thumbnail === null) {
                if ($post->thumbnail && Str::startsWith($post->thumbnail, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $post->thumbnail));
                }
                $validated['thumbnail'] = null;
            } else {
                unset($validated['thumbnail']);
            }
        }

        $post->update($validated);

        return redirect()->route('portal.cms.berita.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('portal.cms.berita.index')->with('success', 'Berita berhasil dihapus.');
    }
}
