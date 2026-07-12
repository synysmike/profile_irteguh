<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'author_name',
        'views_count',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeLatestPublished($query)
    {
        return $query->published()->orderByDesc('published_at')->orderByDesc('created_at');
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'berita';
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

    public function coverUrl(): ?string
    {
        $path = trim((string) $this->cover_image);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim(str_replace('public/', '', $path), '/'));
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function publicUrl(): string
    {
        return route('news.show', $this->slug);
    }

    public function shareUrls(): array
    {
        $url = urlencode($this->publicUrl());
        $text = urlencode($this->title . ' — ' . $this->publicUrl());

        return [
            'threads' => 'https://www.threads.net/intent/post?text=' . $text,
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode($this->title) . '&url=' . $url,
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
            'whatsapp' => 'https://wa.me/?text=' . $text,
        ];
    }
}
