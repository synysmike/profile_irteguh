<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'sale_transaction_id',
        'description',
        'quantity',
        'unit_price',
        'discount_name',
        'discount_amount',
        'subtotal',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleTransaction()
    {
        return $this->belongsTo(SaleTransaction::class);
    }

    public function grossSubtotal(): float
    {
        return round((float) $this->subtotal + (float) ($this->discount_amount ?? 0), 2);
    }

    /**
     * Calculate subtotal from quantity * unit_price
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }
}
