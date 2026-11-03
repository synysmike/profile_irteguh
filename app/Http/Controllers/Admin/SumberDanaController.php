<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SumberDana;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SumberDanaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $sumberDanas = SumberDana::query()
            ->withCount('cashTransactions')
            ->ordered()
            ->get();

        return view('admin.keuangan.sumber-dana.index', compact('sumberDanas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSumberDana($request);
        $validated['system_key'] = null;
        SumberDana::create($validated);

        return redirect()->route('admin.keuangan.sumber-dana.index')
            ->with('success', 'Sumber dana berhasil ditambahkan.');
    }

    public function update(Request $request, SumberDana $sumberDana): RedirectResponse
    {
        $validated = $this->validateSumberDana($request, $sumberDana);
        // system_key tidak boleh diubah dari UI
        unset($validated['system_key']);
        $sumberDana->update($validated);

        return redirect()->route('admin.keuangan.sumber-dana.index')
            ->with('success', 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumberDana): RedirectResponse
    {
        if ($sumberDana->isSystem()) {
            return redirect()->route('admin.keuangan.sumber-dana.index')
                ->with('error', 'Sumber dana sistem tidak bisa dihapus.');
        }

        if ($sumberDana->cashTransactions()->exists()) {
            return redirect()->route('admin.keuangan.sumber-dana.index')
                ->with('error', 'Sumber dana tidak bisa dihapus karena sudah dipakai pada transaksi kas.');
        }

        $sumberDana->delete();

        return redirect()->route('admin.keuangan.sumber-dana.index')
            ->with('success', 'Sumber dana berhasil dihapus.');
    }

    private function validateSumberDana(Request $request, ?SumberDana $sumberDana = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('sumber_danas', 'code')->ignore($sumberDana?->id),
            ],
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000',
            'flow_type' => 'required|in:masuk,keluar,both',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 100);

        return $validated;
    }
}
