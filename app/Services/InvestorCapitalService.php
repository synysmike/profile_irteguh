<?php

namespace App\Services;

use App\Models\CashTransaction;
use App\Models\ChartOfAccount;
use App\Models\InvestorCapitalInjection;
use App\Models\InvestorDocument;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvestorCapitalService
{
    /**
     * Investor menyetujui T&C — belum mencatat kas.
     */
    public function approve(InvestorCapitalInjection $injection): InvestorCapitalInjection
    {
        if ($injection->status === 'awaiting_payment' || $injection->status === 'approved') {
            return $injection;
        }

        if ($injection->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'status' => ['Hanya setoran menunggu persetujuan yang bisa disetujui.'],
            ]);
        }

        $injection->update([
            'status' => 'awaiting_payment',
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_note' => null,
        ]);

        return $injection->fresh(['investor']);
    }

    public function reject(InvestorCapitalInjection $injection, ?string $note = null): InvestorCapitalInjection
    {
        if ($injection->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'status' => ['Hanya setoran menunggu persetujuan yang bisa ditolak.'],
            ]);
        }

        $injection->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_note' => $note,
        ]);

        return $injection->fresh();
    }

    /**
     * Investor mengunggah bukti transfer → dana masuk + jurnal Modal Investor.
     */
    public function confirmPayment(
        InvestorCapitalInjection $injection,
        UploadedFile $proof,
        array $meta = []
    ): InvestorCapitalInjection {
        if ($injection->status === 'approved' && $injection->cash_transaction_id) {
            return $injection;
        }

        if ($injection->status !== 'awaiting_payment') {
            throw ValidationException::withMessages([
                'status' => ['Pembayaran hanya bisa dikirim setelah investor menyetujui syarat & ketentuan.'],
            ]);
        }

        return DB::transaction(function () use ($injection, $proof, $meta) {
            $cashAccount = ChartOfAccount::active()->where('type', 'kas')->ordered()->first();
            $equityAccount = $injection->equity_account_id
                ? ChartOfAccount::find($injection->equity_account_id)
                : ChartOfAccount::active()->where('code', '3-3100')->first();

            if (! $cashAccount) {
                throw ValidationException::withMessages([
                    'cash' => ['Akun Kas belum tersedia di COA.'],
                ]);
            }

            $sumberDanaId = $injection->sumber_dana_id
                ?: app(SumberDanaResolver::class)->modalInvestorId();

            $paymentDate = $meta['payment_date'] ?? now()->toDateString();
            $reference = trim((string) ($meta['payment_reference'] ?? '')) ?: $injection->invoice_number;
            $senderName = trim((string) ($meta['payment_sender_name'] ?? '')) ?: null;

            $proofPath = $proof->store('investor-payments', 'public');

            $cash = CashTransaction::create([
                'transaction_date' => $paymentDate,
                'transaction_type' => 'debit',
                'chart_of_account_id' => $cashAccount->id,
                'sumber_dana_id' => $sumberDanaId,
                'amount' => $injection->amount,
                'description' => 'Dana masuk setoran modal investor: ' . $injection->invoice_number
                    . ($senderName ? ' — ' . $senderName : ''),
                'reference' => $reference,
                'document_path' => $proofPath,
                'investor_capital_injection_id' => $injection->id,
                'is_posted' => true,
                'posted_at' => now(),
            ]);

            $journal = null;
            if ($equityAccount) {
                $journal = JournalEntry::create([
                    'entry_date' => $paymentDate,
                    'reference' => $injection->invoice_number,
                    'description' => 'Setoran modal investor: ' . $injection->invoice_number,
                    'is_posted' => true,
                    'posted_at' => now(),
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'chart_of_account_id' => $cashAccount->id,
                    'debit' => $injection->amount,
                    'credit' => 0,
                    'memo' => 'Debit Kas — dana masuk investor',
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'chart_of_account_id' => $equityAccount->id,
                    'debit' => 0,
                    'credit' => $injection->amount,
                    'memo' => 'Kredit Modal Investor',
                ]);
            }

            InvestorDocument::create([
                'investor_id' => $injection->investor_id,
                'capital_injection_id' => $injection->id,
                'title' => 'Bukti transfer ' . $injection->invoice_number,
                'file_path' => $proofPath,
                'uploaded_by' => null,
            ]);

            $injection->update([
                'status' => 'approved',
                'payment_proof_path' => $proofPath,
                'payment_reference' => $reference,
                'payment_sender_name' => $senderName,
                'payment_date' => $paymentDate,
                'paid_at' => now(),
                'cash_transaction_id' => $cash->id,
                'journal_entry_id' => $journal?->id,
                'equity_account_id' => $equityAccount?->id,
                'sumber_dana_id' => $sumberDanaId,
            ]);

            return $injection->fresh(['investor', 'cashTransaction', 'journalEntry', 'documents']);
        });
    }
}
