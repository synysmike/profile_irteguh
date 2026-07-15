<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\Project;
use App\Models\ProjectPaymentTerm;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;

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

    public static function postTermPayment(ProjectPaymentTerm $term, ?string $paymentDate = null): Sale
    {
        if ($term->isPaid()) {
            throw new \RuntimeException('Termin ini sudah dibayar.');
        }

        $project = $term->project()->with(['customer', 'tax'])->firstOrFail();
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

            SaleItem::create([
                'sale_id' => $sale->id,
                'description' => $project->title . ' (' . $term->label . ')',
                'quantity' => 1,
                'unit_price' => $term->subtotal_amount,
                'subtotal' => $term->subtotal_amount,
                'notes' => $project->code,
                'sort_order' => 0,
            ]);

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
