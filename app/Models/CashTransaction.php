<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'transaction_date', 'transaction_type', 'chart_of_account_id',
        'amount', 'description', 'reference', 'document_path',
        'sale_id', 'purchase_id',
        'is_posted', 'posted_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
    }

    public function scopeDebit($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeCredit($query)
    {
        return $query->where('transaction_type', 'credit');
    }

    public function getTypeLabelAttribute()
    {
        return $this->transaction_type === 'debit' ? 'Penerimaan Kas' : 'Pengeluaran Kas';
    }
}
