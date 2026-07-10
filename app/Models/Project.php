<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'customer_id',
        'tax_id',
        'status',
        'progress_percent',
        'subtotal',
        'ppn_amount',
        'tax_name',
        'tax_rate',
        'tax_calculation_type',
        'total',
        'payment_method',
        'start_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
        'progress_percent' => 'integer',
    ];

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Menunggu',
            'in_progress' => 'Berjalan',
            'review' => 'Review',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function paymentTerms(): HasMany
    {
        return $this->hasMany(ProjectPaymentTerm::class)->orderBy('term_number');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public static function generateCode(): string
    {
        $prefix = 'PRJ-' . date('Ymd') . '-';
        $last = static::where('code', 'like', $prefix . '%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->code, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function paidAmount(): float
    {
        return (float) $this->paymentTerms()->where('status', 'paid')->sum('amount');
    }

    public function paymentProgressPercent(): int
    {
        if ($this->total <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->paidAmount() / (float) $this->total) * 100));
    }
}
