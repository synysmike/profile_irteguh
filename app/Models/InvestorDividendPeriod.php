<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorDividendPeriod extends Model
{
    protected $fillable = [
        'period_label', 'start_date', 'end_date',
        'profit_base_amount', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'profit_base_amount' => 'decimal:2',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(InvestorDividendAllocation::class, 'dividend_period_id');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('end_date', 'desc')->orderBy('id', 'desc');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'calculated' => 'Teralokasi',
            'paid' => 'Lunas',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }
}
