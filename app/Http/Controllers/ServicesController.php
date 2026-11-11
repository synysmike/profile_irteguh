<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use App\Models\Project;
use App\Models\SaleTransaction;
use App\Models\Service;
use App\Services\LayananCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServicesController extends Controller
{
    public function index()
    {
        $services = Service::active()->ordered()->get();
        $customerTypes = CustomerType::with('category')->orderBy('name')->get();

        $productItems = SaleTransaction::layananPublic()
            ->with(['service', 'purchase'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (SaleTransaction $item) => [
                'id' => 'alloc-' . $item->id,
                'source_type' => 'produk',
                'source_id' => $item->id,
                'service_id' => $item->service_id,
                'service_title' => $item->service?->title ?? 'Umum',
                'service_icon' => $item->service?->icon,
                'name' => $item->description,
                'description' => $item->notes ?: ('Stok siap jual · qty ' . $item->quantity),
                'price' => (float) $item->unit_price,
                'unit' => 'pcs',
                'max_qty' => (int) $item->quantity,
                'rating' => null,
                'sold' => null,
            ]);

        $serviceItems = Project::layananPublic()
            ->with('service')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Project $project) => [
                'id' => 'project-' . $project->id,
                'source_type' => 'jasa',
                'source_id' => $project->id,
                'service_id' => $project->service_id,
                'service_title' => $project->service?->title ?? 'Umum',
                'service_icon' => $project->service?->icon,
                'name' => $project->title,
                'description' => \Illuminate\Support\Str::limit(
                    trim(strip_tags((string) ($project->portfolio_excerpt ?: $project->description))) ?: $project->code,
                    120
                ),
                'price' => (float) $project->total,
                'unit' => 'paket',
                'max_qty' => 1,
                'rating' => $project->averageRating(),
                'sold' => null,
            ]);

        $catalog = $productItems->concat($serviceItems)->values();

        return view('public.services', compact('services', 'catalog', 'customerTypes'));
    }

    public function checkout(Request $request, LayananCheckoutService $checkout)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:32',
            'address' => 'required|string|max:2000',
            'customer_type_id' => 'required|exists:customer_types,id',
            'company_name' => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:transfer,cod',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            $result = $checkout->checkout([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'customer_type_id' => (int) $validated['customer_type_id'],
                'company_name' => $validated['company_name'] ?? null,
                'payment_method' => $validated['payment_method'] ?? 'transfer',
            ], $validated['items']);
        } catch (ValidationException $e) {
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan tersimpan. Membuka WhatsApp…',
            'whatsapp_url' => $result['whatsapp_url'],
            'sale_invoice' => $result['sale']?->invoice_number,
            'project_codes' => collect($result['projects'])->pluck('code')->values(),
        ]);
    }
}
