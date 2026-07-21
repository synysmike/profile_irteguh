<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosCheckoutService
{
    /**
     * Cart item shape:
     * - type: purchase|sale_transaction
     * - purchase_id (required for purchase)
     * - sale_transaction_id (required for sale_transaction)
     * - quantity, unit_price, notes?
     *
     * @param  array<int, array<string, mixed>>  $cart
     */
    public function checkout(array $header, array $cart): Sale
    {
        if (count($cart) === 0) {
            throw ValidationException::withMessages([
                'cart' => ['Keranjang masih kosong.'],
            ]);
        }

        return DB::transaction(function () use ($header, $cart) {
            $lines = [];
            $subtotal = 0;

            foreach ($cart as $index => $row) {
                $type = $row['type'] ?? 'purchase';
                $qty = (int) ($row['quantity'] ?? 0);
                $price = (float) ($row['unit_price'] ?? 0);
                $notes = isset($row['notes']) ? (string) $row['notes'] : null;

                if ($qty < 1 || $price < 0) {
                    throw ValidationException::withMessages([
                        "cart.{$index}" => ['Qty dan harga jual tidak valid.'],
                    ]);
                }

                if ($type === 'sale_transaction') {
                    $transaction = SaleTransaction::with('purchase')
                        ->availableForInvoice()
                        ->find($row['sale_transaction_id'] ?? 0);

                    if (! $transaction) {
                        throw ValidationException::withMessages([
                            "cart.{$index}" => ['Alokasi stok tidak tersedia atau sudah diinvoice.'],
                        ]);
                    }

                    $lineSubtotal = (float) $transaction->subtotal;
                    $lines[] = [
                        'transaction' => $transaction,
                        'description' => $transaction->description,
                        'quantity' => (int) $transaction->quantity,
                        'unit_price' => (float) $transaction->unit_price,
                        'subtotal' => $lineSubtotal,
                        'notes' => $notes ?: $transaction->notes,
                    ];
                    $subtotal += $lineSubtotal;
                    continue;
                }

                $purchaseId = (int) ($row['purchase_id'] ?? 0);
                $purchase = Purchase::find($purchaseId);
                if (! $purchase) {
                    throw ValidationException::withMessages([
                        "cart.{$index}" => ['Barang grosir tidak ditemukan.'],
                    ]);
                }

                $remaining = $purchase->remainingQuantity();
                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        "cart.{$index}" => ["Stok \"{$purchase->displayDescription()}\" tidak cukup. Tersisa {$remaining}."],
                    ]);
                }

                $lineSubtotal = $qty * $price;
                $transaction = SaleTransaction::create([
                    'purchase_id' => $purchase->id,
                    'description' => $purchase->displayDescription(),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineSubtotal,
                    'notes' => $notes,
                    'is_active' => false,
                ]);

                $lines[] = [
                    'transaction' => $transaction,
                    'description' => $transaction->description,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineSubtotal,
                    'notes' => $notes,
                ];
                $subtotal += $lineSubtotal;
            }

            $tax = ! empty($header['tax_id']) ? Tax::find($header['tax_id']) : null;
            $taxAmount = $tax ? $tax->calculateAmount((float) $subtotal) : 0;
            $total = $tax && $tax->isDeduction()
                ? ($subtotal - $taxAmount)
                : ($subtotal + $taxAmount);

            $sale = Sale::create([
                'customer_id' => $header['customer_id'],
                'tax_id' => $tax?->id,
                'invoice_number' => $header['invoice_number'],
                'sale_date' => $header['sale_date'],
                'subtotal' => $subtotal,
                'ppn_amount' => $taxAmount,
                'tax_name' => $tax?->name,
                'tax_rate' => $tax?->rate,
                'tax_calculation_type' => $tax?->calculation_type,
                'total' => max($total, 0),
                'notes' => $header['notes'] ?? null,
            ]);

            foreach ($lines as $index => $line) {
                /** @var SaleTransaction $transaction */
                $transaction = $line['transaction'];
                $transaction->update(['is_active' => false]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'sale_transaction_id' => $transaction->id,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                    'notes' => $line['notes'],
                    'sort_order' => $index,
                ]);
            }

            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            if ($cashAccount) {
                CashTransaction::create([
                    'transaction_date' => $sale->sale_date,
                    'transaction_type' => 'debit',
                    'chart_of_account_id' => $cashAccount->id,
                    'amount' => $sale->total,
                    'description' => 'Penerimaan kas dari penjualan: ' . $sale->invoice_number,
                    'reference' => $sale->invoice_number,
                    'sale_id' => $sale->id,
                ]);
            }

            return $sale->load(['customer', 'saleItems']);
        });
    }

    /**
     * Stock available for POS (purchase remaining + unused allocations).
     *
     * @return array{stock: array<int, mixed>, ready: array<int, mixed>}
     */
    public function catalog(): array
    {
        $stock = Purchase::with('supplier')
            ->latestFirst()
            ->get()
            ->filter(fn (Purchase $p) => $p->remainingQuantity() > 0)
            ->map(fn (Purchase $p) => [
                'type' => 'purchase',
                'purchase_id' => $p->id,
                'invoice_number' => $p->invoice_number,
                'description' => $p->displayDescription(),
                'remaining_quantity' => $p->remainingQuantity(),
                'cost_unit_price' => (float) $p->unit_price,
                'suggested_unit_price' => (float) $p->unit_price,
                'supplier' => $p->supplier?->name,
                'purchase_date' => $p->purchase_date?->format('d/m/Y'),
            ])
            ->values()
            ->all();

        $ready = SaleTransaction::with('purchase.supplier')
            ->availableForInvoice()
            ->orderBy('description')
            ->get()
            ->map(fn (SaleTransaction $t) => [
                'type' => 'sale_transaction',
                'sale_transaction_id' => $t->id,
                'purchase_id' => $t->purchase_id,
                'invoice_number' => $t->purchase?->invoice_number,
                'description' => $t->description,
                'quantity' => (int) $t->quantity,
                'unit_price' => (float) $t->unit_price,
                'subtotal' => (float) $t->subtotal,
                'supplier' => $t->purchase?->supplier?->name,
            ])
            ->values()
            ->all();

        return compact('stock', 'ready');
    }
}
