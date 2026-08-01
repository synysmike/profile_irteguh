<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id', 'project_id', 'project_payment_term_id', 'tax_id',
        'discount_id', 'discount_name', 'discount_type', 'discount_value', 'discount_amount',
        'invoice_number', 'sale_date',
        'subtotal', 'ppn_amount', 'tax_name', 'tax_rate', 'tax_calculation_type', 'total', 'notes',
        'is_posted', 'posted_at',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectPaymentTerm()
    {
        return $this->belongsTo(ProjectPaymentTerm::class);
    }

    public function cashTransaction()
    {
        return $this->hasOne(CashTransaction::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function grossSubtotal(): float
    {
        return round((float) $this->subtotal + (float) ($this->discount_amount ?? 0), 2);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class)->orderBy('sort_order');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('sale_date', 'desc')->orderBy('id', 'desc');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq = $last ? (int) substr($last->invoice_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Public invoice URL (customer must verify phone before viewing).
     */
    public function publicInvoiceUrl(): string
    {
        return route('invoice.sales.show', ['sale' => $this->id]);
    }
}
