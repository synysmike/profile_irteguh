<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

class SeoMeta
{
    /**
     * @param  array{
     *     title?: string|null,
     *     description?: string|null,
     *     url?: string|null,
     *     image?: string|null,
     *     type?: string|null,
     *     keywords?: string|null,
     *     robots?: string|null,
     *     published_time?: string|null,
     *     author?: string|null,
     *     site_name?: string|null,
     *     locale?: string|null
     * }  $overrides
     * @return array{
     *     title: string,
     *     description: string,
     *     url: string,
     *     image: ?string,
     *     image_type: ?string,
     *     image_is_https: bool,
     *     type: string,
     *     keywords: ?string,
     *     robots: string,
     *     published_time: ?string,
     *     author: ?string,
     *     site_name: string,
     *     locale: string
     * }
     */
    public static function make(array $overrides = []): array
    {
        $siteName = Setting::appName();
        $theme = Setting::homeTheme();

        $title = trim((string) ($overrides['title'] ?? $siteName));
        $description = self::limitDescription(
            $overrides['description']
                ?? $theme['description']
                ?? $theme['footer_tagline']
                ?? $siteName
        );

        $image = self::absoluteImage($overrides['image'] ?? null) ?? self::fallbackImage();

        return [
            'title' => $title !== '' ? $title : $siteName,
            'description' => $description,
            'url' => self::absoluteUrl($overrides['url'] ?? url()->current()),
            'image' => $image,
            'image_type' => self::mimeFromUrl($image),
            'image_is_https' => $image ? str_starts_with($image, 'https://') : false,
            'type' => (string) ($overrides['type'] ?? 'website'),
            'keywords' => self::normalizeKeywords($overrides['keywords'] ?? null),
            'robots' => trim((string) ($overrides['robots'] ?? 'index,follow')) ?: 'index,follow',
            'published_time' => $overrides['published_time'] ?? null,
            'author' => isset($overrides['author']) && trim((string) $overrides['author']) !== ''
                ? trim((string) $overrides['author'])
                : null,
            'site_name' => (string) ($overrides['site_name'] ?? $siteName),
            'locale' => (string) ($overrides['locale'] ?? 'id_ID'),
        ];
    }

    public static function limitDescription(?string $text, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)) ?? '');

        return $text === '' ? Setting::appName() : Str::limit($text, $limit, '');
    }

    public static function normalizeKeywords(?string $keywords): ?string
    {
        $keywords = trim((string) $keywords);
        if ($keywords === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map(
            fn ($part) => trim($part),
            preg_split('/[,;]+/', $keywords) ?: []
        )));

        return $parts === [] ? null : implode(', ', array_slice($parts, 0, 24));
    }

    public static function absoluteUrl(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return url('/');
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public static function absoluteImage(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return self::preferHttps($path);
        }

        if (str_starts_with($path, '//')) {
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

            return self::preferHttps($scheme.':'.$path);
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return self::preferHttps(url(ltrim($path, '/')));
        }

        if (str_starts_with($path, '/')) {
            return self::preferHttps(url(ltrim($path, '/')));
        }

        return self::preferHttps(url('storage/'.ltrim(str_replace('public/', '', $path), '/')));
    }

    public static function fallbackImage(): ?string
    {
        $logo = Setting::logoPath();

        return $logo ? self::preferHttps($logo) : null;
    }

    public static function mimeFromUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = strtolower((string) (parse_url($url, PHP_URL_PATH) ?: ''));

        return match (true) {
            str_ends_with($path, '.png') => 'image/png',
            str_ends_with($path, '.webp') => 'image/webp',
            str_ends_with($path, '.gif') => 'image/gif',
            str_ends_with($path, '.jpg'), str_ends_with($path, '.jpeg') => 'image/jpeg',
            default => 'image/jpeg',
        };
    }

    public static function preferHttps(string $url): string
    {
        $appIsHttps = str_starts_with((string) config('app.url'), 'https://');
        if ($appIsHttps && str_starts_with($url, 'http://')) {
            return 'https://'.substr($url, strlen('http://'));
        }

        return $url;
    }

    /**
     * Build a short description from a list of content labels.
     *
     * @param  iterable<int, string|null>  $labels
     */
    public static function fromLabels(string $prefix, iterable $labels, int $limit = 160): string
    {
        $parts = [];
        foreach ($labels as $label) {
            $label = trim(strip_tags((string) $label));
            if ($label !== '') {
                $parts[] = $label;
            }
            if (count($parts) >= 8) {
                break;
            }
        }

        $joined = $parts === [] ? $prefix : ($prefix.': '.implode(', ', $parts));

        return self::limitDescription($joined, $limit);
    }
}
