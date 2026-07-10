<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\SaleTransaction;
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
        $transactions = SaleTransaction::with(['purchase.supplier'])
            ->orderBy('description')
            ->get();

        return view('admin.keuangan.transaksi.sale-transactions', compact('transactions'));
    }

    public function create()
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.sale-transactions.partials.form', [
                    'transaction' => null,
                    'purchases' => $this->availablePurchases(),
                ])->render(),
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'code' => 'nullable|string|max:50|unique:sale_transactions,code',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|string|in:0,1',
        ]);

        $this->validatePurchaseQuantity($validated['purchase_id'], (int) $validated['quantity']);

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];
        $validated['is_active'] = $request->input('is_active', '0') == '1';

        SaleTransaction::create($validated);

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
                ])->render(),
                'data' => $transaction,
            ]);
        }

        return redirect()->route('admin.keuangan.sale-transactions.index');
    }

    public function update(Request $request, string $id)
    {
        $transaction = SaleTransaction::findOrFail($id);

        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'code' => 'nullable|string|max:50|unique:sale_transactions,code,' . $id,
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|string|in:0,1',
        ]);

        $this->validatePurchaseQuantity(
            (int) $validated['purchase_id'],
            (int) $validated['quantity'],
            (int) $transaction->id
        );

        $validated['subtotal'] = $validated['quantity'] * $validated['unit_price'];
        $validated['is_active'] = $request->input('is_active', '0') == '1';

        $transaction->update($validated);

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
        $transaction->delete();

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
                'quantity' => ["Stok grosir tersisa {$remaining} unit untuk {$purchase->displayDescription()}."],
            ]);
        }
    }
}
