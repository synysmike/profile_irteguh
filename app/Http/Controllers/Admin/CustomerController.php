<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerType;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $customers = Customer::with('customerType.category')->orderBy('name')->get();
        $customerCategories = CustomerCategory::withCount('customerTypes')->orderBy('name')->get();
        $customerTypes = CustomerType::with('category')->withCount('customers')->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'customerTypes', 'customerCategories'));
    }

    public function create()
    {
        $customerTypes = CustomerType::with('category')->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.customers.partials.form', [
                    'customer' => null,
                    'customerTypes' => $customerTypes,
                ])->render(),
            ]);
        }
        return view('admin.customers.create', compact('customerTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'customer_type_id' => 'required|exists:customer_types,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $selectedType = CustomerType::with('category')->findOrFail($validated['customer_type_id']);
        $validated['customer_type'] = $selectedType->resolveLegacyKey();
        $validated['is_active'] = $request->has('is_active');
        $customer = Customer::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customerTypes = CustomerType::with('category')->orderBy('name')->get();
        
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.customers.partials.form', compact('customer', 'customerTypes'))->render(),
                'data' => $customer
            ]);
        }
        
        return view('admin.customers.edit', compact('customer', 'customerTypes'));
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'customer_type_id' => 'required|exists:customer_types,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $selectedType = CustomerType::with('category')->findOrFail($validated['customer_type_id']);
        $validated['customer_type'] = $selectedType->resolveLegacyKey();
        $validated['is_active'] = $request->has('is_active');
        $customer->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil diperbarui.',
                'data' => $customer
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
