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
     * Absolute image URL for social previews (Open Graph / Twitter / WhatsApp).
     * Preference: cover → first image in content → site logo.
     */
    public function shareImageUrl(): ?string
    {
        $cover = $this->coverUrl();
        if ($cover) {
            return $this->preferHttpsUrl($cover);
        }

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $this->content, $matches)) {
            $fromContent = $this->resolveMediaUrl(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));
            if ($fromContent) {
                return $this->preferHttpsUrl($fromContent);
            }
        }

        $logo = $this->resolveMediaUrl(Setting::logoPath());

        return $logo ? $this->preferHttpsUrl($logo) : null;
    }

    /**
     * WhatsApp caches previews aggressively; a stable version query helps re-scrape after updates.
     */
    public function shareImageUrlForPreview(): ?string
    {
        $url = $this->shareImageUrl();
        if (!$url) {
            return null;
        }

        $version = optional($this->updated_at)->timestamp ?: time();
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . $version;
    }

    public function shareImageMimeType(): ?string
    {
        $url = $this->shareImageUrl();
        if (!$url) {
            return null;
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH) ?: '');
        return match (true) {
            str_ends_with($path, '.png') => 'image/png',
            str_ends_with($path, '.webp') => 'image/webp',
            str_ends_with($path, '.gif') => 'image/gif',
            str_ends_with($path, '.jpg'), str_ends_with($path, '.jpeg') => 'image/jpeg',
            default => 'image/jpeg',
        };
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

    protected function preferHttpsUrl(string $url): string
    {
        $appIsHttps = str_starts_with((string) config('app.url'), 'https://');
        if ($appIsHttps && str_starts_with($url, 'http://')) {
            return 'https://' . substr($url, strlen('http://'));
        }

        return $url;
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
        $pageUrl = $this->publicUrl();
        $encodedUrl = urlencode($pageUrl);
        $threadsText = urlencode($this->title . ' — ' . $pageUrl);
        // WhatsApp previews more reliably when the bare URL comes first.
        $whatsappText = urlencode($pageUrl . "\n\n" . $this->title);

        return [
            'threads' => 'https://www.threads.net/intent/post?text=' . $threadsText,
            'twitter' => 'https://twitter.com/intent/tweet?text=' . urlencode($this->title) . '&url=' . $encodedUrl,
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $encodedUrl,
            'whatsapp' => 'https://api.whatsapp.com/send?text=' . $whatsappText,
        ];
    }
}
