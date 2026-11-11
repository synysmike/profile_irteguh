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
        'show_in_layanan',
        'service_id',
        'layanan_template_project_id',
        'is_layanan_pending',
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
        'show_in_layanan' => 'boolean',
        'is_layanan_pending' => 'boolean',
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

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'review' => 'bg-amber-100 text-amber-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700',
        };
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function layananTemplate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'layanan_template_project_id');
    }

    /**
     * Project yang dipajang di etalase Layanan Kami (jasa / template).
     */
    public function scopeLayananPublic($query)
    {
        return $query->where('show_in_layanan', true)
            ->whereNotNull('service_id')
            ->where('status', '!=', 'cancelled')
            ->where('is_layanan_pending', false);
    }

    public function scopeLayananPending($query)
    {
        return $query->where('is_layanan_pending', true);
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

    public function progressMedia(): HasMany
    {
        return $this->hasMany(ProjectProgressMedia::class)->orderBy('sort_order')->orderByDesc('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class)->latest('submitted_at');
    }

    public function publishedReviews(): HasMany
    {
        return $this->hasMany(ProjectReview::class)
            ->where('is_published', true)
            ->latest('submitted_at');
    }

    public function publicReviewUrl(): string
    {
        return url()->route('project.review.show', ['project' => $this->id]);
    }

    public function averageRating(): ?float
    {
        $avg = $this->publishedReviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function portfolioMedia(): HasMany
    {
        return $this->hasMany(ProjectProgressMedia::class)
            ->where('show_in_portfolio', true)
            ->orderBy('sort_order')
            ->orderBy('id');
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
        if ($this->portfolio_cover_path) {
            return asset('storage/' . ltrim($this->portfolio_cover_path, '/'));
        }

        $firstImage = $this->relationLoaded('portfolioMedia')
            ? $this->portfolioMedia->first(fn ($m) => $m->isImage())
            : $this->portfolioMedia()->where('media_type', 'image')->first();

        return $firstImage?->url();
    }

    /**
     * @return array<int, array{url:string,type:string,caption:?string}>
     */
    public function portfolioGalleryItems(): array
    {
        $items = [];

        $media = $this->relationLoaded('portfolioMedia')
            ? $this->portfolioMedia
            : $this->portfolioMedia()->get();

        foreach ($media as $item) {
            $items[] = [
                'url' => $item->url(),
                'type' => $item->media_type,
                'caption' => $item->caption,
            ];
        }

        foreach ($this->portfolio_visuals ?? [] as $path) {
            if (! $path) {
                continue;
            }
            $url = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                ? $path
                : asset('storage/' . ltrim($path, '/'));
            $items[] = [
                'url' => $url,
                'type' => 'image',
                'caption' => null,
            ];
        }

        return $items;
    }

    /** @deprecated use portfolioGalleryItems() */
    public function portfolioVisualUrls(): array
    {
        return array_values(array_map(
            fn ($item) => $item['url'],
            array_filter($this->portfolioGalleryItems(), fn ($i) => $i['type'] === 'image')
        ));
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
