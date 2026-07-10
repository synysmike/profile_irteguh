<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $taxes = Tax::withCount(['sales', 'purchases'])->orderBy('name')->get();

        return view('admin.keuangan.pajak.index', compact('taxes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:30|unique:taxes,code',
            'rate' => 'required|numeric|min:0|max:100',
            'calculation_type' => 'required|in:addition,deduction',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        Tax::create($validated);

        return redirect()->route('admin.keuangan.pajak.index')
            ->with('success', 'Pajak berhasil ditambahkan.');
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:30|unique:taxes,code,' . $tax->id,
            'rate' => 'required|numeric|min:0|max:100',
            'calculation_type' => 'required|in:addition,deduction',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $tax->update($validated);

        return redirect()->route('admin.keuangan.pajak.index')
            ->with('success', 'Pajak berhasil diperbarui.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        if ($tax->sales()->exists() || $tax->purchases()->exists()) {
            return redirect()->route('admin.keuangan.pajak.index')
                ->with('error', 'Pajak tidak bisa dihapus karena sudah dipakai pada invoice.');
        }

        $tax->delete();

        return redirect()->route('admin.keuangan.pajak.index')
            ->with('success', 'Pajak berhasil dihapus.');
    }
}
