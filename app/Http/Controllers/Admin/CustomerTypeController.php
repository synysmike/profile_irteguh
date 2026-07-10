<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use App\Models\CustomerType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_types,name',
            'customer_category_id' => 'required|exists:customer_categories,id',
        ]);

        $category = CustomerCategory::findOrFail($validated['customer_category_id']);
        $validated['legacy_key'] = $category->legacy_key;

        CustomerType::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Tipe customer berhasil ditambahkan.');
    }

    public function update(Request $request, CustomerType $customerType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_types,name,' . $customerType->id,
            'customer_category_id' => 'required|exists:customer_categories,id',
        ]);

        $category = CustomerCategory::findOrFail($validated['customer_category_id']);
        $validated['legacy_key'] = $category->legacy_key;

        $customerType->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Tipe customer berhasil diperbarui.');
    }

    public function destroy(CustomerType $customerType): RedirectResponse
    {
        if ($customerType->customers()->exists()) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Tipe customer tidak bisa dihapus karena masih dipakai oleh data customer.');
        }

        if (CustomerType::count() <= 1) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Minimal harus ada satu tipe customer.');
        }

        $customerType->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Tipe customer berhasil dihapus.');
    }
}
