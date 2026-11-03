<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Discount;
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
    public static function applyDiscount(float $baseAmount, ?Discount $discount): array
    {
        $gross = max(0, round($baseAmount, 2));
        $discountAmount = $discount ? $discount->calculateAmount($gross) : 0;

        return [
            'gross' => $gross,
            'discount_amount' => $discountAmount,
            'net' => max(0, round($gross - $discountAmount, 2)),
            'discount_id' => $discount?->id,
            'discount_name' => $discount?->name,
            'discount_type' => $discount?->type,
            'discount_value' => $discount?->value,
        ];
    }

    public static function lineTotals(int $quantity, float $unitPrice, ?Discount $discount = null): array
    {
        $gross = round(max(0, $quantity) * max(0, $unitPrice), 2);
        $discounted = static::applyDiscount($gross, $discount);

        return array_merge($discounted, [
            'subtotal' => $discounted['net'],
        ]);
    }

    public static function discountSnapshot(?Discount $discount, float $discountAmount = 0): array
    {
        return [
            'discount_id' => $discount?->id,
            'discount_name' => $discount?->name,
            'discount_type' => $discount?->type,
            'discount_value' => $discount?->value,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

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
     * Recalculate project DPP/tax/total from base_subtotal (minus jasa discount)
     * + attached stock allocations (already net of line discounts),
     * then refresh unpaid payment terms (keep paid terms as-is).
     */
    public static function recalculateFromAllocations(Project $project): void
    {
        $project->refresh();
        $stockSubtotal = (float) $project->saleTransactions()->sum('subtotal');
        $base = (float) ($project->base_subtotal ?? 0);
        $discount = $project->discount_id ? Discount::find($project->discount_id) : null;
        $jasa = static::applyDiscount($base, $discount);
        $tax = $project->tax_id ? Tax::find($project->tax_id) : null;
        $amounts = static::applyTax($jasa['net'] + $stockSubtotal, $tax);

        $project->update(array_merge(
            static::discountSnapshot($discount, $jasa['discount_amount']),
            [
                'subtotal' => $amounts['subtotal'],
                'ppn_amount' => $amounts['ppn_amount'],
                'tax_name' => $amounts['tax_name'],
                'tax_rate' => $amounts['tax_rate'],
                'tax_calculation_type' => $amounts['tax_calculation_type'],
                'total' => $amounts['total'],
            ]
        ));

        $project->refresh();

        // Refresh unpaid term nominals to match new totals (percentages unchanged).
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

    /**
     * Set project base_subtotal from Σ (qty × unit_price) of all assignment-letter jasa items,
     * then recalculate finance. Blocks changes when any payment term is already paid.
     */
    public static function syncJasaFromAssignmentLetters(Project $project): void
    {
        $project->loadMissing(['assignmentLetters.jasaItems', 'paymentTerms']);

        $newBase = round((float) $project->assignmentLetters
            ->flatMap(fn ($letter) => $letter->jasaItems)
            ->sum('subtotal'), 2);

        $currentBase = round((float) ($project->base_subtotal ?? 0), 2);
        $hasLetterJasa = $project->assignmentLetters
            ->flatMap(fn ($letter) => $letter->jasaItems)
            ->isNotEmpty();

        // If no letter jasa lines exist, leave manual base_subtotal untouched.
        if (! $hasLetterJasa) {
            static::recalculateFromAllocations($project);

            return;
        }

        if ($project->paymentTerms->where('status', 'paid')->isNotEmpty()
            && abs($newBase - $currentBase) > 0.009) {
            throw ValidationException::withMessages([
                'jasa_items' => ['Nilai jasa tidak bisa diubah karena sudah ada termin yang lunas.'],
            ]);
        }

        $project->update(['base_subtotal' => $newBase]);
        static::recalculateFromAllocations($project->fresh());
    }

    public static function allocateFromPurchase(
        Project $project,
        Purchase $purchase,
        int $quantity,
        float $unitPrice,
        ?string $notes = null,
        ?Discount $discount = null
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

        return DB::transaction(function () use ($project, $purchase, $quantity, $unitPrice, $notes, $discount) {
            $line = static::lineTotals($quantity, $unitPrice, $discount);
            $transaction = SaleTransaction::create(array_merge([
                'purchase_id' => $purchase->id,
                'project_id' => $project->id,
                'description' => $purchase->displayDescription(),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $line['subtotal'],
                'notes' => $notes,
                'is_active' => false,
            ], static::discountSnapshot($discount, $line['discount_amount'])));

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

        $project = $term->project()->with([
            'customer',
            'tax',
            'saleTransactions.purchase',
            'assignmentLetters.jasaItems',
        ])->firstOrFail();
        $paymentDate = $paymentDate ?? now()->toDateString();

        return DB::transaction(function () use ($term, $project, $paymentDate) {
            $invoiceNumber = Sale::generateInvoiceNumber();

            $sale = Sale::create([
                'customer_id' => $project->customer_id,
                'project_id' => $project->id,
                'project_payment_term_id' => $term->id,
                'tax_id' => $project->tax_id,
                'discount_id' => $project->discount_id,
                'discount_name' => $project->discount_name,
                'discount_type' => $project->discount_type,
                'discount_value' => $project->discount_value,
                'discount_amount' => 0,
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
                    'sumber_dana_id' => app(SumberDanaResolver::class)->projectTerminId(),
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
     * Build proportional jasa lines for a term invoice.
     * Prefer itemized assignment-letter jasa; fall back to single lump-sum jasa line.
     *
     * @return list<array{description: string, quantity: int, unit_price: float, discount_amount: float, discount_name: ?string, subtotal: float, notes: string, sale_transaction_id: null}>
     */
    public static function buildTermJasaLines(Project $project, float $factor, string $termLabel): array
    {
        $project->loadMissing(['assignmentLetters.jasaItems']);

        $jasaItems = $project->assignmentLetters->flatMap->jasaItems->values();
        $jasaGross = (float) ($project->base_subtotal ?? 0);
        $jasaDiscount = (float) ($project->discount_amount ?? 0);
        $jasaNet = max(0, $jasaGross - $jasaDiscount);
        $discountName = $jasaDiscount > 0 ? ($project->discount_name ?: 'Diskon jasa') : null;

        $lines = [];

        if ($jasaItems->isNotEmpty()) {
            $itemsGrossSum = round((float) $jasaItems->sum('subtotal'), 2);
            // Prefer live letter totals; if empty/desynced, use project base.
            $basisGross = $itemsGrossSum > 0 ? $itemsGrossSum : $jasaGross;

            foreach ($jasaItems as $item) {
                $itemGross = (float) $item->subtotal;
                if ($itemGross <= 0 && (int) $item->quantity <= 0) {
                    continue;
                }

                $share = $basisGross > 0 ? ($itemGross / $basisGross) : 0;
                $grossPortion = round($itemGross * $factor, 2);
                $discountPortion = round($jasaDiscount * $factor * $share, 2);
                $netPortion = max(0, round($grossPortion - $discountPortion, 2));
                $qty = max(1, (int) $item->quantity);
                $unitPrice = round(($netPortion + $discountPortion) / $qty, 2);

                $notes = $project->code . ' · ' . $termLabel;
                if ($discountPortion > 0) {
                    $notes .= ' · Diskon Rp ' . number_format($discountPortion, 0, ',', '.');
                }

                $lines[] = [
                    'sale_transaction_id' => null,
                    'description' => $item->description ?: 'Jasa',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_name' => $discountPortion > 0 ? $discountName : null,
                    'discount_amount' => $discountPortion,
                    'subtotal' => $netPortion,
                    'notes' => $notes,
                ];
            }

            return $lines;
        }

        if ($jasaNet > 0 || $jasaGross > 0) {
            $netPortion = round($jasaNet * $factor, 2);
            $discountPortion = round($jasaDiscount * $factor, 2);
            $notes = $project->code . ' · ' . $termLabel;
            if ($discountPortion > 0) {
                $notes .= ' · Diskon Rp ' . number_format($discountPortion, 0, ',', '.');
            }

            $lines[] = [
                'sale_transaction_id' => null,
                'description' => 'Jasa project: ' . $project->title,
                'quantity' => 1,
                'unit_price' => $netPortion + $discountPortion,
                'discount_name' => $discountPortion > 0 ? $discountName : null,
                'discount_amount' => $discountPortion,
                'subtotal' => $netPortion,
                'notes' => $notes,
            ];
        }

        return $lines;
    }

    /**
     * Build invoice lines for a project term: jasa/dasar + each stock allocation,
     * proportional to the term percentage. Line DPP sums to term.subtotal_amount.
     * Diskon jasa & stok disimpan per baris dan diagregasi ke header invoice.
     */
    public static function createTermSaleItems(Sale $sale, Project $project, ProjectPaymentTerm $term): void
    {
        $percentage = (float) $term->percentage;
        $factor = $percentage > 0 ? ($percentage / 100) : 1.0;
        $targetDpp = round((float) $term->subtotal_amount, 2);
        $termLabel = $term->label . ' (' . number_format($percentage, 2, ',', '.') . '%)';
        $lines = [];
        $totalDiscount = 0.0;
        $discountLabels = [];

        foreach (static::buildTermJasaLines($project, $factor, $termLabel) as $line) {
            $totalDiscount += (float) $line['discount_amount'];
            if ((float) $line['discount_amount'] > 0 && $line['discount_name']) {
                $discountLabels[$line['discount_name']] = true;
            }
            $lines[] = $line;
        }

        foreach ($project->saleTransactions as $allocation) {
            $netPortion = round((float) $allocation->subtotal * $factor, 2);
            $discountPortion = round((float) ($allocation->discount_amount ?? 0) * $factor, 2);
            $qty = max(1, (int) $allocation->quantity);
            // Keep unit_price as pre-discount unit so invoice shows harga & diskon jelas.
            $grossPortion = round($netPortion + $discountPortion, 2);
            $unitPrice = round($grossPortion / $qty, 2);
            $grossPortion = round($unitPrice * $qty, 2);
            $netPortion = round($grossPortion - $discountPortion, 2);

            $totalDiscount += $discountPortion;
            if ($discountPortion > 0 && $allocation->discount_name) {
                $discountLabels[$allocation->discount_name] = true;
            }

            $notesParts = [
                $project->code,
                'Alokasi stok',
                $termLabel,
            ];
            if ($allocation->purchase?->invoice_number) {
                $notesParts[] = 'Grosir ' . $allocation->purchase->invoice_number;
            }
            if ($discountPortion > 0) {
                $notesParts[] = 'Diskon Rp ' . number_format($discountPortion, 0, ',', '.');
            }
            if ($allocation->notes) {
                $notesParts[] = $allocation->notes;
            }

            $lines[] = [
                'sale_transaction_id' => $allocation->id,
                'description' => $allocation->description,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount_name' => $discountPortion > 0 ? ($allocation->discount_name ?: 'Diskon stok') : null,
                'discount_amount' => $discountPortion,
                'subtotal' => $netPortion,
                'notes' => implode(' · ', $notesParts),
            ];
        }

        if ($lines === []) {
            $lines[] = [
                'sale_transaction_id' => null,
                'description' => $project->title . ' (' . $term->label . ')',
                'quantity' => 1,
                'unit_price' => $targetDpp,
                'discount_name' => null,
                'discount_amount' => 0,
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
        $lines[$last]['unit_price'] = round(
            ((float) $lines[$last]['subtotal'] + (float) $lines[$last]['discount_amount'])
            / max(1, (int) $lines[$last]['quantity']),
            2
        );

        foreach ($lines as $sort => $line) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'sale_transaction_id' => $line['sale_transaction_id'],
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_name' => $line['discount_name'],
                'discount_amount' => $line['discount_amount'],
                'subtotal' => $line['subtotal'],
                'notes' => $line['notes'],
                'sort_order' => $sort,
            ]);
        }

        $label = $project->discount_name
            ?: (count($discountLabels) === 1 ? array_key_first($discountLabels) : (count($discountLabels) > 1 ? 'Diskon gabungan' : null));

        $sale->update([
            'discount_id' => $project->discount_id,
            'discount_name' => $totalDiscount > 0 ? ($label ?: 'Diskon') : null,
            'discount_type' => $project->discount_type,
            'discount_value' => $project->discount_value,
            'discount_amount' => round($totalDiscount, 2),
        ]);
    }

    /**
     * Rebuild sale items on an existing project-term invoice (e.g. after stock was attached earlier).
     */
    public static function refreshTermInvoiceItems(Sale $sale): void
    {
        if (! $sale->project_id || ! $sale->project_payment_term_id) {
            return;
        }

        $project = Project::with(['saleTransactions.purchase', 'assignmentLetters.jasaItems'])->find($sale->project_id);
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
