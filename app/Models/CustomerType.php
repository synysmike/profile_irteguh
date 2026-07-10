<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerType extends Model
{
    protected $fillable = [
        'name',
        'legacy_key',
        'customer_category_id',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }

    public function resolveLegacyKey(): string
    {
        return $this->category?->legacy_key ?? $this->legacy_key ?? 'individual';
    }
}
