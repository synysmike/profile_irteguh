<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ProjectFinanceService;
use Illuminate\Http\Request;

class PublicSaleInvoiceController extends Controller
{
    /**
     * Invoice penjualan untuk customer (tanpa login).
     * Dilindungi signature URL agar ID tidak mudah ditebak.
     */
    public function show(Request $request, Sale $sale)
    {
        $sale->load(['customer', 'saleItems', 'project']);

        if ($sale->project_id && $sale->project_payment_term_id) {
            ProjectFinanceService::refreshTermInvoiceItems($sale);
            $sale->load(['customer', 'saleItems', 'project']);
        }

        return view('admin.sales.invoice', [
            'sale' => $sale,
            'isPublic' => true,
        ]);
    }
}
