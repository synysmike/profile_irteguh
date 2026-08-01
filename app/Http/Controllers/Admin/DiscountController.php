<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $discounts = Discount::withCount(['projects', 'saleTransactions'])
            ->orderBy('name')
            ->get();

        return view('admin.keuangan.diskon.index', compact('discounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDiscount($request);
        Discount::create($validated);

        return redirect()->route('admin.keuangan.diskon.index')
            ->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function update(Request $request, Discount $diskon): RedirectResponse
    {
        $validated = $this->validateDiscount($request, $diskon);
        $diskon->update($validated);

        return redirect()->route('admin.keuangan.diskon.index')
            ->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Discount $diskon): RedirectResponse
    {
        if ($diskon->projects()->exists() || $diskon->saleTransactions()->exists()) {
            return redirect()->route('admin.keuangan.diskon.index')
                ->with('error', 'Diskon tidak bisa dihapus karena sudah dipakai pada project atau alokasi stok.');
        }

        $diskon->delete();

        return redirect()->route('admin.keuangan.diskon.index')
            ->with('success', 'Diskon berhasil dihapus.');
    }

    private function validateDiscount(Request $request, ?Discount $discount = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:30|unique:discounts,code' . ($discount ? ',' . $discount->id : ''),
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validated['type'] === 'percentage' && (float) $validated['value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => ['Nilai persentase diskon maksimal 100%.'],
            ]);
        }

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
