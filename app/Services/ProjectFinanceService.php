<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectFinanceService
{
    public static function applyTax(float $subtotal, ?Tax $tax): array
    {
        $taxAmount = $tax ? $tax->calculateAmount($subtotal) : 0;
        $total = $tax && $tax->isDeduction()
            ? ($subtotal - $taxAmount)
            : ($subtotal + $taxAmount);

        return [
            'subtotal' => $subtotal,
            'ppn_amount' => $taxAmount,
            'tax_name' => $tax?->name,
            'tax_rate' => $tax?->rate,
            'tax_calculation_type' => $tax?->calculation_type,
            'total' => $total,
        ];
    }

    public static function syncPaymentTerms(Project $project, array $termsInput): void
    {
        $project->paymentTerms()->where('status', '!=', 'paid')->delete();

        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $amounts = static::applyTax((float) $project->subtotal, $tax);
        $termNumber = (int) $project->paymentTerms()->where('status', 'paid')->max('term_number');
        $termNumber = $termNumber > 0 ? $termNumber + 1 : 1;

        foreach ($termsInput as $row) {
            if (empty($row['label']) || !isset($row['percentage'])) {
                continue;
            }

            $percentage = (float) $row['percentage'];
            if ($percentage <= 0) {
                continue;
            }

            $subtotalPortion = round($amounts['subtotal'] * $percentage / 100, 2);
            $taxPortion = round($amounts['ppn_amount'] * $percentage / 100, 2);
            $termTotal = round($amounts['total'] * $percentage / 100, 2);

            $project->paymentTerms()->create([
                'term_number' => $termNumber++,
                'label' => $row['label'],
                'percentage' => $percentage,
                'subtotal_amount' => $subtotalPortion,
                'tax_amount' => $taxPortion,
                'amount' => $termTotal,
                'due_date' => $row['due_date'] ?? null,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Update an existing term in place (including paid DP) without changing status.
     */
    public static function updateExistingTerm(Project $project, int $termId, array $payload): void
    {
        $term = $project->paymentTerms()->where('id', $termId)->first();
        if (!$term || empty($payload)) {
            return;
        }

        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $amounts = static::applyTax((float) $project->subtotal, $tax);
        $percentage = (float) ($payload['percentage'] ?? $term->percentage);

        $term->update([
            'label' => trim((string) ($payload['label'] ?? $term->label)) ?: $term->label,
            'percentage' => $percentage,
            'subtotal_amount' => round($amounts['subtotal'] * $percentage / 100, 2),
            'tax_amount' => round($amounts['ppn_amount'] * $percentage / 100, 2),
            'amount' => round($amounts['total'] * $percentage / 100, 2),
            'due_date' => $payload['due_date'] ?? $term->due_date,
        ]);
    }

    public static function rebuildFullPaymentTerm(Project $project): void
    {
        $project->paymentTerms()->where('status', '!=', 'paid')->delete();

        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $amounts = static::applyTax((float) $project->subtotal, $tax);

        $project->paymentTerms()->create([
            'term_number' => 1,
            'label' => 'Pelunasan',
            'percentage' => 100,
            'subtotal_amount' => $amounts['subtotal'],
            'tax_amount' => $amounts['ppn_amount'],
            'amount' => $amounts['total'],
            'due_date' => $project->due_date,
            'status' => 'pending',
        ]);
    }

    /**
     * Recalculate project DPP/tax/total from base_subtotal + attached stock allocations,
     * then refresh unpaid payment terms (keep paid terms as-is).
     */
    public static function recalculateFromAllocations(Project $project): void
    {
        $project->refresh();
        $stockSubtotal = (float) $project->saleTransactions()->sum('subtotal');
        $base = (float) ($project->base_subtotal ?? 0);
        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $amounts = static::applyTax($base + $stockSubtotal, $tax);

        $project->update([
            'subtotal' => $amounts['subtotal'],
            'ppn_amount' => $amounts['ppn_amount'],
            'tax_name' => $amounts['tax_name'],
            'tax_rate' => $amounts['tax_rate'],
            'tax_calculation_type' => $amounts['tax_calculation_type'],
            'total' => $amounts['total'],
        ]);

        $project->refresh();

        // Refresh unpaid term nominals to match new totals (percentages unchanged).
        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $fresh = static::applyTax((float) $project->subtotal, $tax);

        foreach ($project->paymentTerms()->where('status', '!=', 'paid')->get() as $term) {
            $pct = (float) $term->percentage;
            $term->update([
                'subtotal_amount' => round($fresh['subtotal'] * $pct / 100, 2),
                'tax_amount' => round($fresh['ppn_amount'] * $pct / 100, 2),
                'amount' => round($fresh['total'] * $pct / 100, 2),
            ]);
        }

        // Also update paid term display portions? Keep paid amounts frozen (already billed).
    }

    public static function allocateFromPurchase(
        Project $project,
        Purchase $purchase,
        int $quantity,
        float $unitPrice,
        ?string $notes = null
    ): SaleTransaction {
        $remaining = $purchase->remainingQuantity();
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => ['Quantity minimal 1.'],
            ]);
        }
        if ($quantity > $remaining) {
            throw ValidationException::withMessages([
                'quantity' => ["Stok tersisa hanya {$remaining} unit."],
            ]);
        }
        if ($unitPrice < 0) {
            throw ValidationException::withMessages([
                'unit_price' => ['Harga jual tidak valid.'],
            ]);
        }

        return DB::transaction(function () use ($project, $purchase, $quantity, $unitPrice, $notes) {
            $lineSubtotal = $quantity * $unitPrice;
            $transaction = SaleTransaction::create([
                'purchase_id' => $purchase->id,
                'project_id' => $project->id,
                'description' => $purchase->displayDescription(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
                'notes' => $notes,
                'is_active' => false,
            ]);

            static::recalculateFromAllocations($project);

            return $transaction;
        });
    }

    public static function attachSaleTransaction(Project $project, SaleTransaction $transaction): void
    {
        if ($transaction->project_id) {
            throw new \RuntimeException('Alokasi stok ini sudah terpasang ke project lain.');
        }
        if ($transaction->isInvoiced()) {
            throw new \RuntimeException('Alokasi stok ini sudah diinvoice penjualan terpisah.');
        }
        if (! $transaction->purchase_id) {
            throw new \RuntimeException('Alokasi stok harus berasal dari grosir.');
        }

        DB::transaction(function () use ($project, $transaction) {
            $transaction->update([
                'project_id' => $project->id,
                'is_active' => false,
            ]);
            static::recalculateFromAllocations($project);
        });
    }

    public static function detachSaleTransaction(Project $project, SaleTransaction $transaction): void
    {
        if ((int) $transaction->project_id !== (int) $project->id) {
            throw new \RuntimeException('Alokasi stok tidak terpasang pada project ini.');
        }
        if ($transaction->isInvoiced()) {
            throw new \RuntimeException('Alokasi stok sudah terkait invoice dan tidak bisa dilepas.');
        }

        DB::transaction(function () use ($project, $transaction) {
            $transaction->update([
                'project_id' => null,
                'is_active' => true,
            ]);
            static::recalculateFromAllocations($project);
        });
    }

    public static function postTermPayment(ProjectPaymentTerm $term, ?string $paymentDate = null): Sale
    {
        if ($term->isPaid()) {
            throw new \RuntimeException('Termin ini sudah dibayar.');
        }

        $project = $term->project()->with(['customer', 'tax', 'saleTransactions.purchase'])->firstOrFail();
        $paymentDate = $paymentDate ?? now()->toDateString();

        return DB::transaction(function () use ($term, $project, $paymentDate) {
            $invoiceNumber = Sale::generateInvoiceNumber();

            $sale = Sale::create([
                'customer_id' => $project->customer_id,
                'project_id' => $project->id,
                'project_payment_term_id' => $term->id,
                'tax_id' => $project->tax_id,
                'invoice_number' => $invoiceNumber,
                'sale_date' => $paymentDate,
                'subtotal' => $term->subtotal_amount,
                'ppn_amount' => $term->tax_amount,
                'tax_name' => $project->tax_name,
                'tax_rate' => $project->tax_rate,
                'tax_calculation_type' => $project->tax_calculation_type,
                'total' => $term->amount,
                'notes' => 'Pembayaran project ' . $project->code . ' - ' . $term->label,
            ]);

            static::createTermSaleItems($sale, $project, $term);

            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            if ($cashAccount) {
                CashTransaction::create([
                    'transaction_date' => $paymentDate,
                    'transaction_type' => 'debit',
                    'chart_of_account_id' => $cashAccount->id,
                    'amount' => $term->amount,
                    'description' => 'Penerimaan project: ' . $project->code . ' - ' . $term->label,
                    'reference' => $invoiceNumber,
                    'sale_id' => $sale->id,
                    'project_id' => $project->id,
                    'project_payment_term_id' => $term->id,
                ]);
            }

            $term->update([
                'status' => 'paid',
                'paid_at' => now(),
                'sale_id' => $sale->id,
            ]);

            $paidPercent = $project->paymentProgressPercent();
            if ($paidPercent >= 100 && $project->status !== 'completed') {
                $project->update([
                    'status' => 'completed',
                    'progress_percent' => 100,
                ]);
            }

            return $sale;
        });
    }

    /**
     * Build invoice lines for a project term: jasa/dasar + each stock allocation,
     * proportional to the term percentage. Line DPP sums to term.subtotal_amount.
     */
    public static function createTermSaleItems(Sale $sale, Project $project, ProjectPaymentTerm $term): void
    {
        $percentage = (float) $term->percentage;
        $factor = $percentage > 0 ? ($percentage / 100) : 1.0;
        $targetDpp = round((float) $term->subtotal_amount, 2);
        $termLabel = $term->label . ' (' . number_format($percentage, 2, ',', '.') . '%)';
        $lines = [];

        $base = (float) ($project->base_subtotal ?? 0);
        if ($base > 0) {
            $portion = round($base * $factor, 2);
            $lines[] = [
                'sale_transaction_id' => null,
                'description' => 'Jasa project: ' . $project->title,
                'quantity' => 1,
                'unit_price' => $portion,
                'subtotal' => $portion,
                'notes' => $project->code . ' · ' . $termLabel,
            ];
        }

        foreach ($project->saleTransactions as $allocation) {
            $portion = round((float) $allocation->subtotal * $factor, 2);
            $qty = max(1, (int) $allocation->quantity);
            $unitPrice = round($portion / $qty, 2);
            $portion = round($unitPrice * $qty, 2);

            $notesParts = [
                $project->code,
                'Alokasi stok',
                $termLabel,
            ];
            if ($allocation->purchase?->invoice_number) {
                $notesParts[] = 'Grosir ' . $allocation->purchase->invoice_number;
            }
            if ($allocation->notes) {
                $notesParts[] = $allocation->notes;
            }

            $lines[] = [
                'sale_transaction_id' => $allocation->id,
                'description' => $allocation->description,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $portion,
                'notes' => implode(' · ', $notesParts),
            ];
        }

        if ($lines === []) {
            $lines[] = [
                'sale_transaction_id' => null,
                'description' => $project->title . ' (' . $term->label . ')',
                'quantity' => 1,
                'unit_price' => $targetDpp,
                'subtotal' => $targetDpp,
                'notes' => $project->code,
            ];
        }

        // Fix rounding so item DPP equals term DPP.
        $sum = 0.0;
        $last = count($lines) - 1;
        foreach ($lines as $i => $line) {
            if ($i < $last) {
                $sum += $line['subtotal'];
            }
        }
        $lines[$last]['subtotal'] = round($targetDpp - $sum, 2);
        if ((int) $lines[$last]['quantity'] <= 1) {
            $lines[$last]['quantity'] = 1;
            $lines[$last]['unit_price'] = $lines[$last]['subtotal'];
        } else {
            $qty = (int) $lines[$last]['quantity'];
            $lines[$last]['unit_price'] = round($lines[$last]['subtotal'] / $qty, 2);
            // Prefer exact subtotal over qty*price drift
        }

        foreach ($lines as $sort => $line) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'sale_transaction_id' => $line['sale_transaction_id'],
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'subtotal' => $line['subtotal'],
                'notes' => $line['notes'],
                'sort_order' => $sort,
            ]);
        }
    }

    /**
     * Rebuild sale items on an existing project-term invoice (e.g. after stock was attached earlier).
     */
    public static function refreshTermInvoiceItems(Sale $sale): void
    {
        if (! $sale->project_id || ! $sale->project_payment_term_id) {
            return;
        }

        $project = Project::with('saleTransactions.purchase')->find($sale->project_id);
        $term = ProjectPaymentTerm::find($sale->project_payment_term_id);
        if (! $project || ! $term) {
            return;
        }

        $sale->saleItems()->delete();
        static::createTermSaleItems($sale, $project, $term);
    }

    /**
     * Revert a paid term back to pending and remove linked sale + cash entries.
     */
    public static function reverseTermPayment(ProjectPaymentTerm $term): void
    {
        if (!$term->isPaid()) {
            throw new \RuntimeException('Termin ini belum lunas.');
        }

        $project = $term->project()->firstOrFail();

        DB::transaction(function () use ($term, $project) {
            $saleId = $term->sale_id;

            CashTransaction::where('project_payment_term_id', $term->id)->delete();
            if ($saleId) {
                CashTransaction::where('sale_id', $saleId)->delete();
                SaleItem::where('sale_id', $saleId)->delete();
                Sale::where('id', $saleId)->delete();
            }

            $term->update([
                'status' => 'pending',
                'paid_at' => null,
                'sale_id' => null,
            ]);

            if ($project->status === 'completed') {
                $project->update([
                    'status' => 'in_progress',
                    'progress_percent' => min(99, (int) $project->progress_percent),
                ]);
            }
        });
    }
}
