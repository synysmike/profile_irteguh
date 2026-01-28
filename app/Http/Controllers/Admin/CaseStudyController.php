<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaseStudyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caseStudies = CaseStudy::orderBy('order')->orderBy('created_at', 'desc')->get();
        return view('admin.case-studies.index', compact('caseStudies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ['Infrastruktur IT', 'Otomasi & Workflow', 'Kreatif/Desain', 'Layanan Hukum/Bisnis'];
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.case-studies.partials.form', [
                    'categories' => $categories,
                    'caseStudy' => null
                ])->render()
            ]);
        }
        
        return view('admin.case-studies.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_context' => 'nullable|string',
            'challenge' => 'required|string',
            'solution' => 'required|string',
            'outcome' => 'required|string',
            'category' => 'required|string|in:Infrastruktur IT,Otomasi & Workflow,Kreatif/Desain,Layanan Hukum/Bisnis',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'excerpt' => 'nullable|string',
            'featured' => 'boolean',
            'order' => 'integer|min:0',
            'tags' => 'nullable|string',
            'visuals' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        
        // Handle tags (comma-separated string to array)
        if ($request->filled('tags')) {
            $validated['tags'] = array_map('trim', explode(',', $request->tags));
        } else {
            $validated['tags'] = null;
        }

        // Handle visuals (comma-separated string to array)
        if ($request->filled('visuals')) {
            $validated['visuals'] = array_map('trim', explode(',', $request->visuals));
        } else {
            $validated['visuals'] = null;
        }

        $validated['featured'] = $request->has('featured');

        $caseStudy = CaseStudy::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Studi kasus berhasil ditambahkan.',
                'data' => $caseStudy
            ]);
        }

        return redirect()->route('admin.case-studies.index')
            ->with('success', 'Studi kasus berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $caseStudy = CaseStudy::findOrFail($id);
        return view('admin.case-studies.show', compact('caseStudy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $caseStudy = CaseStudy::findOrFail($id);
        $categories = ['Infrastruktur IT', 'Otomasi & Workflow', 'Kreatif/Desain', 'Layanan Hukum/Bisnis'];
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.case-studies.partials.form', [
                    'categories' => $categories,
                    'caseStudy' => $caseStudy
                ])->render(),
                'data' => $caseStudy
            ]);
        }
        
        return view('admin.case-studies.edit', compact('caseStudy', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $caseStudy = CaseStudy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_context' => 'nullable|string',
            'challenge' => 'required|string',
            'solution' => 'required|string',
            'outcome' => 'required|string',
            'category' => 'required|string|in:Infrastruktur IT,Otomasi & Workflow,Kreatif/Desain,Layanan Hukum/Bisnis',
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'excerpt' => 'nullable|string',
            'featured' => 'boolean',
            'order' => 'integer|min:0',
            'tags' => 'nullable|string',
            'visuals' => 'nullable|string',
        ]);

        // Update slug if title changed
        if ($caseStudy->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle tags
        if ($request->filled('tags')) {
            $validated['tags'] = array_map('trim', explode(',', $request->tags));
        } else {
            $validated['tags'] = null;
        }

        // Handle visuals
        if ($request->filled('visuals')) {
            $validated['visuals'] = array_map('trim', explode(',', $request->visuals));
        } else {
            $validated['visuals'] = null;
        }

        $validated['featured'] = $request->has('featured');

        $caseStudy->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Studi kasus berhasil diperbarui.',
                'data' => $caseStudy
            ]);
        }

        return redirect()->route('admin.case-studies.index')
            ->with('success', 'Studi kasus berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $caseStudy = CaseStudy::findOrFail($id);
        $caseStudy->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Studi kasus berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.case-studies.index')
            ->with('success', 'Studi kasus berhasil dihapus.');
    }
}
