<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\InvestorCapitalInjection;
use App\Models\InvestorDividendAllocation;
use App\Models\InvestorDividendPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestorDividendService
{
    public function calculate(InvestorDividendPeriod $period): InvestorDividendPeriod
    {
        if (in_array($period->status, ['paid', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => ['Periode ini tidak bisa dihitung ulang.'],
            ]);
        }

        return DB::transaction(function () use ($period) {
            $period->allocations()->where('status', 'pending')->whereNull('cash_transaction_id')->delete();

            $injections = InvestorCapitalInjection::query()
                ->approved()
                ->whereDate('injection_date', '<=', $period->end_date)
                ->with('investor')
                ->get();

            $base = (float) $period->profit_base_amount;
            $useLoss = $base < 0;

            foreach ($injections as $injection) {
                $percent = $useLoss
                    ? (float) $injection->loss_share_percent
                    : (float) $injection->profit_share_percent;
                $amount = round(abs($base) * ($percent / 100), 2);
                if ($useLoss) {
                    $amount = 0; // catatan rugi: tidak ada payout kas
                }

                InvestorDividendAllocation::create([
                    'dividend_period_id' => $period->id,
                    'investor_id' => $injection->investor_id,
                    'capital_injection_id' => $injection->id,
                    'share_percent_snapshot' => $percent,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            $period->update(['status' => 'calculated']);

            return $period->fresh(['allocations.investor', 'allocations.capitalInjection']);
        });
    }

    public function payAllocation(InvestorDividendAllocation $allocation, array $meta = []): InvestorDividendAllocation
    {
        if ($allocation->status === 'paid') {
            return $allocation;
        }

        if ((float) $allocation->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Nominal alokasi harus lebih dari 0 untuk dibayar.'],
            ]);
        }

        return DB::transaction(function () use ($allocation, $meta) {
            $allocation->loadMissing(['investor', 'period']);

            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            $expenseAccount = ChartOfAccount::active()->where('code', '5-5100')->first();
            $sumberDanaId = app(SumberDanaResolver::class)->dividenInvestorId();

            if (! $cashAccount) {
                throw ValidationException::withMessages([
                    'cash' => ['Akun Kas belum tersedia.'],
                ]);
            }

            $paymentDate = $meta['payment_date'] ?? now()->toDateString();
            $paymentReference = trim((string) ($meta['payment_reference'] ?? '')) ?: ('DIV-' . $allocation->id);
            $paymentNote = trim((string) ($meta['payment_note'] ?? '')) ?: null;

            $investor = $allocation->investor;
            $bankInfo = $investor?->bankLabel();
            $desc = 'Payout dividen: ' . ($allocation->period?->period_label ?? '')
                . ' — ' . ($investor?->name ?? '');
            if ($investor?->hasBankAccount()) {
                $desc .= ' → ' . $bankInfo;
            }

            $cash = CashTransaction::create([
                'transaction_date' => $paymentDate,
                'transaction_type' => 'credit',
                'chart_of_account_id' => $cashAccount->id,
                'sumber_dana_id' => $sumberDanaId,
                'amount' => $allocation->amount,
                'description' => $desc,
                'reference' => $paymentReference,
                'investor_dividend_allocation_id' => $allocation->id,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            if ($expenseAccount) {
                $journal = JournalEntry::create([
                    'entry_date' => $paymentDate,
                    'reference' => $paymentReference,
                    'description' => 'Payout dividen investor #' . $allocation->id,
                    'is_posted' => true,
                    'posted_at' => now(),
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'chart_of_account_id' => $expenseAccount->id,
                    'debit' => $allocation->amount,
                    'credit' => 0,
                    'memo' => 'Beban dividen investor',
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'chart_of_account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $allocation->amount,
                    'memo' => 'Kredit Kas — payout dividen',
                ]);
            }

            $allocation->update([
                'status' => 'paid',
                'cash_transaction_id' => $cash->id,
                'paid_at' => now(),
                'payment_date' => $paymentDate,
                'payment_reference' => $paymentReference,
                'payment_note' => $paymentNote,
            ]);

            $period = $allocation->period;
            if ($period && $period->allocations()->where('status', 'pending')->where('amount', '>', 0)->doesntExist()) {
                $period->update(['status' => 'paid']);
            } elseif ($period && $period->status === 'draft') {
                $period->update(['status' => 'calculated']);
            }

            return $allocation->fresh(['investor', 'period', 'cashTransaction']);
        });
    }

    public function payAllPending(InvestorDividendPeriod $period, array $meta = []): int
    {
        $count = 0;
        foreach ($period->allocations()->where('status', 'pending')->where('amount', '>', 0)->get() as $alloc) {
            $this->payAllocation($alloc, $meta);
            $count++;
        }

        return $count;
    }
}
