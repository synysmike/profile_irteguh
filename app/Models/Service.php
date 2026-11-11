<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['title', 'icon', 'features', 'order', 'is_active'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function saleTransactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('id');
    }
}
