<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerType;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $quick = request()->boolean('quick');

        if (request()->ajax()) {
            return response()->json([
                'html' => view($quick ? 'admin.customers.partials.form-quick' : 'admin.customers.partials.form', [
                    'customer' => null,
                    'customerTypes' => $customerTypes,
                ])->render(),
            ]);
        }
        return view('admin.customers.create', compact('customerTypes'));
    }

    public function store(Request $request)
    {
        $quick = $request->boolean('quick');

        $rules = [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ($quick ? 'required' : 'nullable') . '|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'customer_type_id' => 'required|exists:customer_types,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        $validated = $request->validate($rules, [
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
        ]);

        $validated['phone'] = $this->normalizePhoneOrFail($validated['phone'] ?? null);

        $selectedType = CustomerType::with('category')->findOrFail($validated['customer_type_id']);
        $validated['customer_type'] = $selectedType->resolveLegacyKey();
        $validated['is_active'] = $request->boolean('is_active', true);
        if ($quick) {
            $validated['is_active'] = true;
        }
        $customer = Customer::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer berhasil ditambahkan.',
                'data' => $customer,
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

        $validated['phone'] = $this->normalizePhoneOrFail($validated['phone'] ?? null);

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

    /**
     * Normalisasi nomor HP (paste WhatsApp dll). Kosong = null; isi tidak valid = error.
     */
    private function normalizePhoneOrFail(?string $phone): ?string
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return null;
        }

        $normalized = PhoneNumber::normalize($raw);
        if (! $normalized) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor HP tidak valid. Tempel dari WhatsApp atau isi seperti 08xxxxxxxxxx / +62…'],
            ]);
        }

        return $normalized;
    }
}
