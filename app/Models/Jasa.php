<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jasa extends Model
{
    protected $fillable = [
        'name',
        'code',
        'unit_price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function assignmentLetterItems(): HasMany
    {
        return $this->hasMany(AssignmentLetterJasaItem::class);
    }

    public function label(): string
    {
        return $this->name . ' (Rp ' . number_format((float) $this->unit_price, 0, ',', '.') . ')';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
