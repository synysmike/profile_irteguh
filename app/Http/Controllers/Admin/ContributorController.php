<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contributor;
use Illuminate\Http\Request;

class ContributorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $contributors = Contributor::orderBy('order')->orderBy('created_at', 'desc')->get();
        return view('admin.contributors.index', compact('contributors'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.contributors.partials.form', ['contributor' => null])->render()
            ]);
        }
        return view('admin.contributors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image_url' => 'required|url|max:500',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $contributor = Contributor::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil ditambahkan.',
                'data' => $contributor
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $contributor = Contributor::findOrFail($id);
        return view('admin.contributors.show', compact('contributor'));
    }

    public function edit(string $id)
    {
        $contributor = Contributor::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.contributors.partials.form', compact('contributor'))->render(),
                'data' => $contributor
            ]);
        }
        
        return view('admin.contributors.edit', compact('contributor'));
    }

    public function update(Request $request, string $id)
    {
        $contributor = Contributor::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image_url' => 'required|url|max:500',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $contributor->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil diperbarui.',
                'data' => $contributor
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $contributor = Contributor::findOrFail($id);
        $contributor->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil dihapus.');
    }
}
