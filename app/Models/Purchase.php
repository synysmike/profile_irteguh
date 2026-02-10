<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'invoice_number', 'purchase_date',
        'subtotal', 'ppn_amount', 'total', 'notes',
        'is_posted', 'posted_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
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
