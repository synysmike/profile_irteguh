<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $fillable = [
        'name',
        'code',
        'rate',
        'calculation_type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function calculateAmount(float $baseAmount): float
    {
        return round($baseAmount * ((float) $this->rate) / 100, 2);
    }

    public function isDeduction(): bool
    {
        return $this->calculation_type === 'deduction';
    }
}
