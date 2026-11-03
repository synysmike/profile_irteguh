<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberDana extends Model
{
    protected $table = 'sumber_danas';

    protected $fillable = [
        'code',
        'name',
        'description',
        'flow_type',
        'system_key',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'sumber_dana_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Filter by cash transaction type: debit=masuk, credit=keluar.
     */
    public function scopeForFlow($query, ?string $transactionType)
    {
        if ($transactionType === 'debit') {
            return $query->whereIn('flow_type', ['masuk', 'both']);
        }
        if ($transactionType === 'credit') {
            return $query->whereIn('flow_type', ['keluar', 'both']);
        }

        return $query;
    }

    public function isSystem(): bool
    {
        return filled($this->system_key);
    }

    public function getFlowLabelAttribute(): string
    {
        return match ($this->flow_type) {
            'masuk' => 'Masuk',
            'keluar' => 'Keluar',
            'both' => 'Masuk & Keluar',
            default => $this->flow_type,
        };
    }

    public static function findBySystemKey(string $key): ?self
    {
        return static::query()->where('system_key', $key)->first();
    }

    public static function flowTypes(): array
    {
        return [
            'masuk' => 'Masuk (penerimaan)',
            'keluar' => 'Keluar (pengeluaran)',
            'both' => 'Masuk & Keluar',
        ];
    }
}
