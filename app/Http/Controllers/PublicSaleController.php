<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class PublicSaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::publicCatalog()
            ->with(['saleItems', 'customer', 'publishedReviews']);

        if ($request->filled('year')) {
            $query->whereYear('sale_date', (int) $request->year);
        }

        $sales = $query->get();

        $years = Sale::publicCatalog()
            ->whereNotNull('sale_date')
            ->reorder()
            ->selectRaw('YEAR(sale_date) as y')
            ->distinct()
            ->orderByDesc('y')
            ->pluck('y');

        return view('public.sales', compact('sales', 'years'));
    }

    public function show(Sale $sale)
    {
        abort_unless($sale->show_in_public && ! $sale->project_id, 404);

        $sale->load(['saleItems', 'customer', 'publishedReviews.media']);

        $related = Sale::publicCatalog()
            ->with(['saleItems', 'publishedReviews'])
            ->withAvg('publishedReviews', 'rating')
            ->withCount('publishedReviews')
            ->where('id', '!=', $sale->id)
            ->limit(3)
            ->get();

        return view('public.sales-show', compact('sale', 'related'));
    }
}
