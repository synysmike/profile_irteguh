<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPaymentTerm extends Model
{
    protected $fillable = [
        'project_id',
        'term_number',
        'label',
        'percentage',
        'subtotal_amount',
        'tax_amount',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'sale_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'percentage' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
