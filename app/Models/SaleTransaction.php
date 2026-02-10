<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransaction extends Model
{
    protected $fillable = [
        'code', 'description', 'quantity', 'unit_price', 'subtotal', 'notes', 'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate subtotal from quantity * unit_price
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }
}
