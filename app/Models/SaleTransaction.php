<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTransaction extends Model
{
    protected $fillable = [
        'purchase_id', 'project_id', 'code', 'description', 'quantity', 'unit_price', 'subtotal', 'notes', 'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
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
