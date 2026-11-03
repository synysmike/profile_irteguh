<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;

class InvestorCapitalInjection extends Model
{
    protected $fillable = [
        'invoice_number', 'investor_id', 'benefit_package_id',
        'injection_date', 'amount', 'sumber_dana_id', 'equity_account_id',
        'benefit_name', 'profit_share_percent', 'loss_share_percent',
        'terms_content', 'status', 'approved_at', 'rejected_at', 'rejection_note',
        'cash_transaction_id', 'journal_entry_id', 'notes',
        'payment_proof_path', 'payment_reference', 'payment_sender_name',
        'payment_date', 'paid_at',
    ];

    protected $casts = [
        'injection_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'profit_share_percent' => 'decimal:2',
        'loss_share_percent' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function benefitPackage(): BelongsTo
    {
        return $this->belongsTo(InvestorBenefitPackage::class, 'benefit_package_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_id');
    }

    public function equityAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'equity_account_id');
    }

    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InvestorDocument::class, 'capital_injection_id');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('injection_date', 'desc')->orderBy('id', 'desc');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval'], true);
    }

    public function publicInvoiceUrl(): string
    {
        return URL::temporarySignedRoute(
            'invoice.investor.show',
            now()->addDays(30),
            ['injection' => $this->id]
        );
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-INV-' . date('Ymd') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq = $last ? (int) substr($last->invoice_number, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Menunggu Persetujuan',
            'awaiting_payment' => 'Menunggu Pembayaran',
            'approved' => 'Lunas / Modal Masuk',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function awaitsPayment(): bool
    {
        return $this->status === 'awaiting_payment';
    }
}
