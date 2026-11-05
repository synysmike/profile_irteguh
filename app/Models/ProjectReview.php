<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectReview extends Model
{
    protected $fillable = [
        'project_id',
        'customer_id',
        'rating',
        'comment',
        'reviewer_name',
        'is_published',
        'submitted_at',
        'ip_address',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_published' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectReviewMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function displayName(): string
    {
        return $this->reviewer_name
            ?: ($this->customer?->name ?? 'Klien');
    }

    public function stars(): string
    {
        $rating = max(0, min(5, (int) $this->rating));

        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }
}
