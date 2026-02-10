<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'description', 'quantity', 'unit_price', 'subtotal', 'notes', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Calculate subtotal from quantity * unit_price
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }
}
