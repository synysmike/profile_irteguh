<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'tax_id', 'invoice_number', 'purchase_date',
        'description', 'quantity', 'unit_price',
        'subtotal', 'ppn_amount', 'tax_name', 'tax_rate', 'tax_calculation_type', 'total', 'notes',
        'is_posted', 'posted_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cashTransaction()
    {
        return $this->hasOne(CashTransaction::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function saleTransactions()
    {
        return $this->hasMany(SaleTransaction::class);
    }

    public function allocatedQuantity(?int $excludeSaleTransactionId = null): int
    {
        $query = $this->saleTransactions();
        if ($excludeSaleTransactionId) {
            $query->where('id', '!=', $excludeSaleTransactionId);
        }

        return (int) $query->sum('quantity');
    }

    public function remainingQuantity(?int $excludeSaleTransactionId = null): int
    {
        return max(0, (int) $this->quantity - $this->allocatedQuantity($excludeSaleTransactionId));
    }

    public function displayDescription(): string
    {
        return $this->description ?: ('Grosir ' . $this->invoice_number);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('purchase_date', 'desc')->orderBy('id', 'desc');
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $last = static::where('invoice_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq = $last ? (int) substr($last->invoice_number, strlen($prefix)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
