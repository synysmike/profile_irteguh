<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'code',
        'title',
        'slug',
        'description',
        'customer_id',
        'tax_id',
        'discount_id',
        'discount_name',
        'discount_type',
        'discount_value',
        'discount_amount',
        'status',
        'progress_percent',
        'base_subtotal',
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
        'is_portfolio_public',
        'is_portfolio_featured',
        'portfolio_category',
        'portfolio_year',
        'portfolio_sort_order',
        'portfolio_client_context',
        'portfolio_excerpt',
        'portfolio_challenge',
        'portfolio_solution',
        'portfolio_outcome',
        'portfolio_tags',
        'portfolio_cover_path',
        'portfolio_visuals',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'base_subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
        'progress_percent' => 'integer',
        'is_portfolio_public' => 'boolean',
        'is_portfolio_featured' => 'boolean',
        'portfolio_year' => 'integer',
        'portfolio_sort_order' => 'integer',
        'portfolio_tags' => 'array',
        'portfolio_visuals' => 'array',
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

    public static function portfolioCategories(): array
    {
        return [
            'Infrastruktur IT',
            'Otomasi & Workflow',
            'Kreatif/Desain',
            'Layanan Hukum/Bisnis',
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

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function jasaNetSubtotal(): float
    {
        return max(0, (float) ($this->base_subtotal ?? 0) - (float) ($this->discount_amount ?? 0));
    }

    public function paymentTerms(): HasMany
    {
        return $this->hasMany(ProjectPaymentTerm::class)->orderBy('term_number');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleTransactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class)->orderBy('description');
    }

    public function stockSubtotal(): float
    {
        return (float) $this->saleTransactions()->sum('subtotal');
    }

    public function assignmentLetters(): HasMany
    {
        return $this->hasMany(AssignmentLetter::class)->orderByDesc('letter_date')->orderByDesc('id');
    }

    public function scopePortfolioPublic($query)
    {
        return $query->where('is_portfolio_public', true)->whereNotNull('slug');
    }

    public function scopePortfolioOrdered($query)
    {
        return $query->orderBy('portfolio_sort_order')->orderByDesc('portfolio_year')->orderByDesc('id');
    }

    public function portfolioYearValue(): ?int
    {
        if ($this->portfolio_year) {
            return (int) $this->portfolio_year;
        }

        return $this->start_date?->year ?? $this->due_date?->year;
    }

    public function portfolioExcerptText(): string
    {
        return (string) ($this->portfolio_excerpt
            ?: Str::limit(strip_tags((string) $this->description), 160)
            ?: Str::limit(strip_tags((string) $this->portfolio_challenge), 160));
    }

    public function portfolioCoverUrl(): ?string
    {
        if (! $this->portfolio_cover_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->portfolio_cover_path, '/'));
    }

    public function portfolioVisualUrls(): array
    {
        $urls = [];
        foreach ($this->portfolio_visuals ?? [] as $path) {
            if (! $path) {
                continue;
            }
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $urls[] = $path;
            } else {
                $urls[] = asset('storage/' . ltrim($path, '/'));
            }
        }

        return $urls;
    }

    public static function generateCode(): string
    {
        $prefix = 'PRJ-' . date('Ymd') . '-';
        $last = static::where('code', 'like', $prefix . '%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->code, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
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

    public function deletePortfolioCover(): void
    {
        if ($this->portfolio_cover_path && Storage::disk('public')->exists($this->portfolio_cover_path)) {
            Storage::disk('public')->delete($this->portfolio_cover_path);
        }
    }
}
