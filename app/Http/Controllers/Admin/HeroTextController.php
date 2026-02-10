<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroText;
use Illuminate\Http\Request;

class HeroTextController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $heroTexts = HeroText::ordered()->get();
        return view('admin.hero-texts.index', compact('heroTexts'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.hero-texts.partials.form', ['heroText' => null])->render()
            ]);
        }
        return view('admin.hero-texts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        HeroText::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Teks hero berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.hero-texts.index')
            ->with('success', 'Teks hero berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $heroText = HeroText::findOrFail($id);
        return view('admin.hero-texts.show', compact('heroText'));
    }

    public function edit(string $id)
    {
        $heroText = HeroText::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.hero-texts.partials.form', compact('heroText'))->render(),
                'data' => $heroText
            ]);
        }

        return view('admin.hero-texts.edit', compact('heroText'));
    }

    public function update(Request $request, string $id)
    {
        $heroText = HeroText::findOrFail($id);

        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $heroText->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Teks hero berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.hero-texts.index')
            ->with('success', 'Teks hero berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $heroText = HeroText::findOrFail($id);
        $heroText->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Teks hero berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.hero-texts.index')
            ->with('success', 'Teks hero berhasil dihapus.');
    }
}
