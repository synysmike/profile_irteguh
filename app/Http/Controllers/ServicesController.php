<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use App\Models\Project;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Service;
use App\Services\LayananCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ServicesController extends Controller
{
    public function index()
    {
        $services = Service::active()->ordered()->get();
        $customerTypes = CustomerType::with('category')->orderBy('name')->get();

        $allocations = SaleTransaction::layananPublic()
            ->with(['service', 'purchase'])
            ->orderByDesc('updated_at')
            ->get();

        $allocStats = $this->allocationCatalogStats($allocations->pluck('id'));

        $productItems = $allocations->map(fn (SaleTransaction $item) => [
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
            'rating' => $allocStats[$item->id]['rating'] ?? null,
            'sold' => $allocStats[$item->id]['sold'] ?? null,
        ]);

        $projects = Project::layananPublic()
            ->with('service')
            ->withAvg('publishedReviews', 'rating')
            ->withCount([
                'layananOrders as sold_count' => fn ($q) => $q
                    ->where('is_layanan_pending', false)
                    ->where('status', '!=', 'cancelled'),
            ])
            ->orderByDesc('updated_at')
            ->get();

        $serviceItems = $projects->map(fn (Project $project) => [
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
            'rating' => $project->published_reviews_avg_rating !== null
                ? round((float) $project->published_reviews_avg_rating, 1)
                : null,
            'sold' => (int) ($project->sold_count ?? 0) ?: null,
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

    /**
     * Rating/sold untuk kartu produk etalase.
     * Menggabungkan item yang terhubung langsung ke alokasi + draft/konfirmasi via notes.
     *
     * @param  Collection<int, int|string>  $allocIds
     * @return array<int, array{rating:?float,sold:?int}>
     */
    private function allocationCatalogStats(Collection $allocIds): array
    {
        $ids = $allocIds->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $items = SaleItem::query()
            ->where(function ($q) use ($ids) {
                $q->whereIn('sale_transaction_id', $ids->all());
                foreach ($ids as $id) {
                    $q->orWhere('notes', 'like', '%layanan_alloc:'.$id.'%')
                        ->orWhere('notes', 'like', '%split_from_alloc:'.$id.'%');
                }
            })
            ->with(['sale.publishedReviews'])
            ->get();

        $stats = [];
        foreach ($ids as $id) {
            $stats[$id] = ['rating' => null, 'sold' => null, '_ratings' => [], '_sold' => 0];
        }

        foreach ($items as $item) {
            $sale = $item->sale;
            if (! $sale || $sale->is_layanan_pending) {
                continue;
            }

            $matchedIds = [];
            if ($item->sale_transaction_id && $ids->contains((int) $item->sale_transaction_id)) {
                $matchedIds[] = (int) $item->sale_transaction_id;
            }
            if (preg_match_all('/(?:layanan_alloc|split_from_alloc):(\d+)/', (string) $item->notes, $m)) {
                foreach ($m[1] as $rawId) {
                    $aid = (int) $rawId;
                    if ($ids->contains($aid)) {
                        $matchedIds[] = $aid;
                    }
                }
            }
            $matchedIds = array_values(array_unique($matchedIds));
            if ($matchedIds === []) {
                continue;
            }

            foreach ($matchedIds as $aid) {
                $stats[$aid]['_sold'] += (int) $item->quantity;
                foreach ($sale->publishedReviews as $review) {
                    $stats[$aid]['_ratings'][] = (float) $review->rating;
                }
            }
        }

        $out = [];
        foreach ($stats as $id => $row) {
            $rating = null;
            if (count($row['_ratings']) > 0) {
                $rating = round(array_sum($row['_ratings']) / count($row['_ratings']), 1);
            }
            $out[$id] = [
                'rating' => $rating,
                'sold' => $row['_sold'] > 0 ? $row['_sold'] : null,
            ];
        }

        return $out;
    }
}
