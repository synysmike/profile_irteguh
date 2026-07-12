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
        return $this->resolveMediaUrl($this->cover_image);
    }

    /**
     * Absolute image URL for social previews (Open Graph / Twitter).
     * Preference: cover → first image in content → site logo.
     */
    public function shareImageUrl(): ?string
    {
        $cover = $this->coverUrl();
        if ($cover) {
            return $cover;
        }

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $this->content, $matches)) {
            $fromContent = $this->resolveMediaUrl(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));
            if ($fromContent) {
                return $fromContent;
            }
        }

        return $this->resolveMediaUrl(Setting::logoPath());
    }

    public function resolveMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $path;
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return url(ltrim($path, '/'));
        }

        if (str_starts_with($path, '/')) {
            return url(ltrim($path, '/'));
        }

        return url('storage/' . ltrim(str_replace('public/', '', $path), '/'));
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
