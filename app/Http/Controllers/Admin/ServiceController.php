<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $services = Service::ordered()->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.services.partials.form', ['service' => null])->render()
            ]);
        }
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'features' => 'nullable|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['features'] = $request->filled('features')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : [];

        Service::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.show', compact('service'));
    }

    public function edit(string $id)
    {
        $service = Service::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.services.partials.form', compact('service'))->render(),
                'data' => $service
            ]);
        }

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'features' => 'nullable|string',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['features'] = $request->filled('features')
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : [];

        $service->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Layanan berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
