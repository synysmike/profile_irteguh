<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'description',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function typeLabels(): array
    {
        return [
            'percentage' => 'Persentase (%)',
            'fixed' => 'Nominal tetap (Rp)',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function saleTransactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class);
    }

    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    public function calculateAmount(float $baseAmount): float
    {
        $base = max(0, $baseAmount);
        if ($base <= 0 || (float) $this->value <= 0) {
            return 0;
        }

        if ($this->isPercentage()) {
            return min($base, round($base * ((float) $this->value) / 100, 2));
        }

        return min($base, round((float) $this->value, 2));
    }

    public function label(): string
    {
        if ($this->isPercentage()) {
            return $this->name . ' (' . number_format((float) $this->value, 2, ',', '.') . '%)';
        }

        return $this->name . ' (Rp ' . number_format((float) $this->value, 0, ',', '.') . ')';
    }
}
