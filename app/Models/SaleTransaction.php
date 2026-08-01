<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransaction extends Model
{
    protected $fillable = [
        'purchase_id',
        'project_id',
        'discount_id',
        'discount_name',
        'discount_type',
        'discount_value',
        'discount_amount',
        'code',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function grossSubtotal(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isInvoiced(): bool
    {
        return $this->saleItems()->exists();
    }

    public function scopeAvailableForInvoice($query)
    {
        return $query->active()
            ->fromGrosir()
            ->whereNull('project_id')
            ->whereDoesntHave('saleItems');
    }

    public function scopeAvailableForProject($query)
    {
        return $query->fromGrosir()
            ->whereNull('project_id')
            ->whereDoesntHave('saleItems');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFromGrosir($query)
    {
        return $query->whereNotNull('purchase_id');
    }

    /**
     * Calculate subtotal from quantity * unit_price
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
    }
}
