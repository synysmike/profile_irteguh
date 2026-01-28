<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $slides = Slide::orderBy('order')->orderBy('created_at', 'desc')->get();
        return view('admin.slides.index', compact('slides'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.slides.partials.form', ['slide' => null])->render()
            ]);
        }
        return view('admin.slides.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'required|url|max:500',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $slide = Slide::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Slide berhasil ditambahkan.',
                'data' => $slide
            ]);
        }

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $slide = Slide::findOrFail($id);
        return view('admin.slides.show', compact('slide'));
    }

    public function edit(string $id)
    {
        $slide = Slide::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.slides.partials.form', compact('slide'))->render(),
                'data' => $slide
            ]);
        }
        
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, string $id)
    {
        $slide = Slide::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'required|url|max:500',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $slide->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Slide berhasil diperbarui.',
                'data' => $slide
            ]);
        }

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $slide = Slide::findOrFail($id);
        $slide->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Slide berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.slides.index')
            ->with('success', 'Slide berhasil dihapus.');
    }
}
