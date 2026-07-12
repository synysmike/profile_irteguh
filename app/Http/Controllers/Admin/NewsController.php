<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $news = News::orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create', ['newsItem' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);
        $validated = $this->preparePayload($request, $validated);

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $newsItem = News::findOrFail($id);

        return view('admin.news.edit', compact('newsItem'));
    }

    public function update(Request $request, string $id)
    {
        $newsItem = News::findOrFail($id);
        $validated = $this->validateNews($request, $newsItem);
        $validated = $this->preparePayload($request, $validated, $newsItem);

        $newsItem->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $newsItem = News::findOrFail($id);

        if ($newsItem->cover_image && !filter_var($newsItem->cover_image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($newsItem->cover_image);
        }

        $newsItem->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    private function validateNews(Request $request, ?News $newsItem = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:news,slug,' . ($newsItem?->id ?? 'NULL'),
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'cover_image' => 'nullable|string|max:500',
            'cover_file' => 'nullable|image|max:4096',
            'author_name' => 'nullable|string|max:120',
            'published_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);
    }

    private function preparePayload(Request $request, array $validated, ?News $newsItem = null): array
    {
        $title = $validated['title'];
        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : News::generateUniqueSlug($title, $newsItem?->id);

        // Keep existing cover unless a new URL or file is provided.
        $cover = $newsItem?->cover_image;
        $coverUrlInput = trim((string) ($validated['cover_image'] ?? ''));
        if ($coverUrlInput !== '') {
            $cover = $coverUrlInput;
        }

        if ($request->hasFile('cover_file')) {
            if ($newsItem?->cover_image && !filter_var($newsItem->cover_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($newsItem->cover_image);
            }
            $cover = $request->file('cover_file')->store('news', 'public');
        }

        if ($request->boolean('remove_cover')) {
            if ($cover && !filter_var($cover, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($cover);
            }
            $cover = null;
        }

        $isPublished = $request->boolean('is_published');
        $publishedAt = $validated['published_at'] ?? null;
        if ($isPublished && empty($publishedAt)) {
            $publishedAt = now();
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'cover_image' => $cover,
            'author_name' => $validated['author_name'] ?? null,
            'is_published' => $isPublished,
            'published_at' => $publishedAt,
        ];
    }
}
