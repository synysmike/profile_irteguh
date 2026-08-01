<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentLetterJasaItem extends Model
{
    protected $fillable = [
        'assignment_letter_id',
        'jasa_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function assignmentLetter(): BelongsTo
    {
        return $this->belongsTo(AssignmentLetter::class);
    }

    public function jasa(): BelongsTo
    {
        return $this->belongsTo(Jasa::class);
    }

    public function recalculateSubtotal(): void
    {
        $this->subtotal = round(max(0, (int) $this->quantity) * max(0, (float) $this->unit_price), 2);
    }
}
