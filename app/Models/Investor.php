<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    protected $fillable = [
        'name', 'phone', 'bank_name', 'bank_account', 'bank_account_name',
        'email', 'address', 'notes', 'is_active',
    ];

    public function bankLabel(): string
    {
        $parts = array_filter([
            $this->bank_name,
            $this->bank_account,
            $this->bank_account_name ? 'a/n ' . $this->bank_account_name : null,
        ]);

        return $parts ? implode(' · ', $parts) : '—';
    }

    public function hasBankAccount(): bool
    {
        return filled($this->bank_account);
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = PhoneNumber::normalize($value);
    }

    public function capitalInjections(): HasMany
    {
        return $this->hasMany(InvestorCapitalInjection::class);
    }

    public function dividendAllocations(): HasMany
    {
        return $this->hasMany(InvestorDividendAllocation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InvestorDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
