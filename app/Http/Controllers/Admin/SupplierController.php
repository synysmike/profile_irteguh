<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.suppliers.partials.form', ['supplier' => null])->render()
            ]);
        }
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $supplier = Supplier::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempat grosir berhasil ditambahkan.',
                'data' => $supplier
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Tempat grosir berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.suppliers.partials.form', compact('supplier'))->render(),
                'data' => $supplier
            ]);
        }
        
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $supplier->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempat grosir berhasil diperbarui.',
                'data' => $supplier
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Tempat grosir berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tempat grosir berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Tempat grosir berhasil dihapus.');
    }
}
