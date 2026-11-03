<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDividendAllocation extends Model
{
    protected $fillable = [
        'dividend_period_id', 'investor_id', 'capital_injection_id',
        'share_percent_snapshot', 'amount', 'status',
        'cash_transaction_id', 'paid_at',
        'payment_date', 'payment_reference', 'payment_note',
    ];

    protected $casts = [
        'share_percent_snapshot' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Dibayar',
            'pending' => 'Menunggu Bayar',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(InvestorDividendPeriod::class, 'dividend_period_id');
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function capitalInjection(): BelongsTo
    {
        return $this->belongsTo(InvestorCapitalInjection::class, 'capital_injection_id');
    }

    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class);
    }
}
