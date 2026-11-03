<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvestorBenefitPackage extends Model
{
    protected $fillable = [
        'code', 'name', 'profit_share_percent', 'loss_share_percent',
        'min_deposit_amount',
        'description', 'terms_template', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'profit_share_percent' => 'decimal:2',
        'loss_share_percent' => 'decimal:2',
        'min_deposit_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function capitalInjections(): HasMany
    {
        return $this->hasMany(InvestorCapitalInjection::class, 'benefit_package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
