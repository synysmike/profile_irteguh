<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Purchase;
use App\Models\SaleTransaction;
use App\Models\Service;
use App\Services\ProjectFinanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $transactions = SaleTransaction::with(['purchase.supplier', 'discount', 'service'])
            ->orderBy('description')
            ->get();
        $services = Service::ordered()->get();

        return view('admin.keuangan.transaksi.sale-transactions', compact('transactions', 'services'));
    }

    /**
     * Toggle / set tampil di etalase Layanan Kami (produk dari alokasi).
     */
    public function updateLayanan(Request $request, SaleTransaction $saleTransaction)
    {
        if ($saleTransaction->isInvoiced() || $saleTransaction->project_id) {
            return back()->with('error', 'Alokasi yang sudah diinvoice atau terikat project tidak bisa dipajang di Layanan Kami.');
        }

        if ($request->boolean('show_in_layanan')) {
            if (! $saleTransaction->purchase_id) {
                return back()->with('error', 'Alokasi harus terhubung ke grosir (purchase_id) sebelum dipajang di Layanan Kami.');
            }

            if (! $saleTransaction->is_active) {
                return back()->with('error', 'Alokasi nonaktif tidak bisa dipajang di Layanan Kami.');
            }

            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
            ], [
                'service_id.required' => 'Pilih kategori layanan sebelum menampilkan di etalase.',
            ]);

            $saleTransaction->update([
                'show_in_layanan' => true,
                'service_id' => (int) $validated['service_id'],
            ]);

            return back()->with('success', 'Alokasi ditampilkan di Layanan Kami.');
        }

        $saleTransaction->update([
            'show_in_layanan' => false,
            'service_id' => null,
        ]);

        return back()->with('success', 'Alokasi disembunyikan dari Layanan Kami.');
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.sale-transactions.partials.form', [
                    'transaction' => null,
                    'purchases' => $this->availablePurchases(),
                    'discounts' => $this->activeDiscounts(),
                ])->render(),
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTransaction($request);
        $discount = !empty($validated['discount_id']) ? Discount::find($validated['discount_id']) : null;
        $line = ProjectFinanceService::lineTotals(
            (int) $validated['quantity'],
            (float) $validated['unit_price'],
            $discount
        );

        $payload = array_merge($validated, ProjectFinanceService::discountSnapshot($discount, $line['discount_amount']), [
            'subtotal' => $line['subtotal'],
            'is_active' => $request->input('is_active', '0') == '1',
        ]);

        SaleTransaction::create($payload);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $transaction = SaleTransaction::with('purchase')->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.sale-transactions.partials.form', [
                    'transaction' => $transaction,
                    'purchases' => $this->availablePurchases($transaction->id),
                    'discounts' => $this->activeDiscounts($transaction->discount_id),
                ])->render(),
                'data' => $transaction,
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function update(Request $request, string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);
        $validated = $this->validateTransaction($request, $transaction);

        $discount = !empty($validated['discount_id']) ? Discount::find($validated['discount_id']) : null;
        $line = ProjectFinanceService::lineTotals(
            (int) $validated['quantity'],
            (float) $validated['unit_price'],
            $discount
        );

        $payload = array_merge($validated, ProjectFinanceService::discountSnapshot($discount, $line['discount_amount']), [
            'subtotal' => $line['subtotal'],
            'is_active' => $request->input('is_active', '0') == '1',
        ]);

        $transaction->update($payload);

        if ($transaction->project_id) {
            ProjectFinanceService::recalculateFromAllocations($transaction->project);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);
        $project = $transaction->project_id ? $transaction->project : null;
        $transaction->delete();

        if ($project) {
            ProjectFinanceService::recalculateFromAllocations($project);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function purchaseDetails(string $purchaseId)
    {
        $purchase = Purchase::with('supplier')->findOrFail($purchaseId);
        $excludeId = request()->integer('exclude_sale_transaction_id') ?: null;
        $remaining = $purchase->remainingQuantity($excludeId);

        return response()->json([
            'id' => $purchase->id,
            'invoice_number' => $purchase->invoice_number,
            'description' => $purchase->displayDescription(),
            'quantity' => (int) $purchase->quantity,
            'remaining_quantity' => $remaining,
            'cost_unit_price' => (float) $purchase->unit_price,
            'supplier' => $purchase->supplier?->name,
            'purchase_date' => $purchase->purchase_date?->format('d/m/Y'),
        ]);
    }

    private function validateTransaction(Request $request, ?SaleTransaction $transaction = null): array
    {
        $id = $transaction?->id;
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'code' => 'nullable|string|max:50|unique:sale_transactions,code' . ($id ? ',' . $id : ''),
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_id' => 'nullable|exists:discounts,id',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|string|in:0,1',
        ]);

        $this->validatePurchaseQuantity(
            (int) $validated['purchase_id'],
            (int) $validated['quantity'],
            $id ? (int) $id : null
        );

        return $validated;
    }

    private function activeDiscounts(?int $includeId = null)
    {
        return Discount::query()
            ->where(function ($q) use ($includeId) {
                $q->where('is_active', true);
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function availablePurchases(?int $includeForSaleTransactionId = null): \Illuminate\Support\Collection
    {
        $currentPurchaseId = null;
        if ($includeForSaleTransactionId) {
            $currentPurchaseId = SaleTransaction::whereKey($includeForSaleTransactionId)->value('purchase_id');
        }

        return Purchase::with('supplier')
            ->latestFirst()
            ->get()
            ->filter(function (Purchase $purchase) use ($includeForSaleTransactionId, $currentPurchaseId) {
                if ($currentPurchaseId && (int) $purchase->id === (int) $currentPurchaseId) {
                    return true;
                }

                return $purchase->remainingQuantity($includeForSaleTransactionId) > 0;
            })
            ->values();
    }

    private function validatePurchaseQuantity(int $purchaseId, int $quantity, ?int $excludeSaleTransactionId = null): void
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $remaining = $purchase->remainingQuantity($excludeSaleTransactionId);
        if ($quantity > $remaining) {
            throw ValidationException::withMessages([
                'quantity' => ["Stok tersisa hanya {$remaining} unit."],
            ]);
        }
    }
}
