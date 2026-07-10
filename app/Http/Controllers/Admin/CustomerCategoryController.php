<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_categories,name',
            'legacy_key' => 'required|in:individual,company',
        ]);

        CustomerCategory::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Kategori tipe customer berhasil ditambahkan.');
    }

    public function update(Request $request, CustomerCategory $customerCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_categories,name,' . $customerCategory->id,
            'legacy_key' => 'required|in:individual,company',
        ]);

        $customerCategory->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Kategori tipe customer berhasil diperbarui.');
    }

    public function destroy(CustomerCategory $customerCategory): RedirectResponse
    {
        if ($customerCategory->customerTypes()->exists()) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih dipakai oleh tipe customer.');
        }

        if (CustomerCategory::count() <= 1) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Minimal harus ada satu kategori tipe customer.');
        }

        $customerCategory->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Kategori tipe customer berhasil dihapus.');
    }
}
