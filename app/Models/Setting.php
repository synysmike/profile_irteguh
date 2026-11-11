<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** @var array<string, mixed> Request-lifetime memo (avoids stale file-cache when storage is unwritable). */
    private static array $memo = [];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, static::$memo)) {
            return static::$memo[$key];
        }

        $setting = static::where('key', $key)->first();
        $value = $setting ? $setting->value : $default;
        static::$memo[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        static::$memo[$key] = $value;

        try {
            Cache::forget('setting_' . $key);
        } catch (\Throwable) {
            // Ignore unwritable file cache; memo + DB are source of truth.
        }
    }

    /**
     * Get application display name (for invoice, header, etc.). Falls back to config('app.name').
     */
    public static function appName(): string
    {
        return (string) static::get('app_name', config('app.name', 'Laravel'));
    }

    /**
     * Letterhead HTML content shown on printed documents.
     * Logo is handled separately using the uploaded site logo.
     */
    public static function letterheadHtml(): string
    {
        $html = (string) static::get('letterhead_html', '');
        return trim($html);
    }

    /**
     * Get site logo URL (storage path).
     */
    public static function logoUrl(): ?string
    {
        $path = static::get('site_logo');
        if (!$path) {
            return null;
        }
        return Storage::url($path);
    }

    /**
     * Get site logo path for use in img src (absolute URL).
     */
    public static function logoPath(): ?string
    {
        $path = static::get('site_logo');
        if (!$path) {
            return null;
        }
        return asset('storage/' . ltrim(str_replace('public/', '', $path), '/'));
    }

    /**
     * Get landing page logo width (px). Default 180.
     */
    public static function logoLandingWidth(): int
    {
        $v = static::get('logo_landing_width', 180);
        return max(40, min(400, (int) $v));
    }

    /**
     * Get landing page logo height (px). Default 40.
     */
    public static function logoLandingHeight(): int
    {
        $v = static::get('logo_landing_height', 40);
        return max(20, min(120, (int) $v));
    }

    /**
     * Whether landing page logo is locked to 1:1 ratio. Default false.
     */
    public static function logoLandingLockRatio(): bool
    {
        return (bool) (int) static::get('logo_landing_lock_ratio', 0);
    }

    public static function contactAddress(): string
    {
        return (string) static::get('contact_address', 'Surabaya, Indonesia');
    }

    public static function contactEmail(): string
    {
        return (string) static::get('contact_email', 'contact@irteguhsolution.com');
    }

    public static function contactWhatsapp(): string
    {
        return (string) static::get('contact_whatsapp', '6281332444088');
    }

    public static function contactWhatsappLabel(): string
    {
        return (string) static::get('contact_whatsapp_label', 'Chat dengan kami di WhatsApp');
    }

    public static function contactResponseNote(): string
    {
        return (string) static::get('contact_response_note', 'Kami biasanya merespons dalam 24-48 jam');
    }

    public static function contactMapsEmbedUrl(): ?string
    {
        $url = trim((string) static::get('contact_maps_embed_url', ''));
        return $url !== '' ? $url : null;
    }

    /**
     * Extract a safe Google Maps embed URL from a raw URL or pasted iframe HTML.
     */
    public static function normalizeMapsEmbedInput(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $matches)) {
            $input = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        if (!filter_var($input, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = parse_url($input, PHP_URL_HOST);
        $allowedHosts = [
            'www.google.com',
            'google.com',
            'maps.google.com',
            'www.google.co.id',
            'maps.google.co.id',
        ];

        if (!$host || !in_array(strtolower($host), $allowedHosts, true)) {
            return null;
        }

        // Prefer embed URLs; allow maps URLs that already contain /maps/embed
        if (!str_contains($input, '/maps/embed') && !str_contains($input, 'output=embed')) {
            // Still allow if it's clearly a google maps URL - admin may paste share link
            // Convert basic place share links is unreliable; require embed format.
            if (!str_contains($input, 'google.com/maps')) {
                return null;
            }
        }

        return $input;
    }

    public static function contactWhatsappUrl(): string
    {
        $number = preg_replace('/\D+/', '', static::contactWhatsapp());
        return $number ? 'https://wa.me/' . $number : '#';
    }

    public static function investorBankName(): string
    {
        return (string) static::get('investor_bank_name', '');
    }

    public static function investorBankAccount(): string
    {
        return (string) static::get('investor_bank_account', '');
    }

    public static function investorBankHolder(): string
    {
        return (string) static::get('investor_bank_holder', '');
    }

    /**
     * Tema & copy beranda (defaults = nilai hardcode lama).
     *
     * @return array<string, mixed>
     */
    public static function homeTheme(): array
    {
        $overlay = (float) static::get('home_overlay_opacity', '0.35');
        $overlay = max(0.15, min(0.75, $overlay));

        return [
            'kicker' => (string) static::get('home_kicker', 'Selamat Datang Di Situs Resmi'),
            'tagline' => (string) static::get('home_tagline', 'Untuk Pendidikan dan Bisnis'),
            'description' => (string) static::get(
                'home_description',
                'Mengubah tantangan menjadi peluang melalui otomasi, infrastruktur IT, desain kreatif, dan layanan hukum.'
            ),
            'cta_primary_label' => (string) static::get('home_cta_primary_label', 'Lihat Portfolio'),
            'cta_primary_url' => (string) static::get('home_cta_primary_url', '/portfolio'),
            'cta_secondary_label' => (string) static::get('home_cta_secondary_label', 'Hubungi Kami'),
            'cta_secondary_url' => (string) static::get('home_cta_secondary_url', '/contact'),
            'accent' => static::normalizeHexColor(static::get('theme_accent', '#9333ea'), '#9333ea'),
            'accent_hover' => static::normalizeHexColor(static::get('theme_accent_hover', '#7e22ce'), '#7e22ce'),
            'gradient_from' => static::normalizeHexColor(static::get('theme_gradient_from', '#581c87'), '#581c87'),
            'gradient_via' => static::normalizeHexColor(static::get('theme_gradient_via', '#1e3a8a'), '#1e3a8a'),
            'gradient_to' => static::normalizeHexColor(static::get('theme_gradient_to', '#312e81'), '#312e81'),
            'overlay_opacity' => $overlay,
            'show_categories' => (bool) (int) static::get('home_show_categories', '1'),
            'show_featured' => (bool) (int) static::get('home_show_featured', '1'),
            'show_services' => (bool) (int) static::get('home_show_services', '1'),
            'show_team' => (bool) (int) static::get('home_show_team', '1'),
            'footer_tagline' => (string) static::get(
                'home_footer_tagline',
                'Solusi IT & Kreatif Terintegrasi untuk Pendidikan dan Bisnis'
            ),
            'services_heading' => (string) static::get('home_services_heading', 'Apa Yang Kami Tawarkan'),
            'categories_heading' => (string) static::get('home_categories_heading', 'Jelajahi Karya Kami'),
            'featured_heading' => (string) static::get('home_featured_heading', 'Proyek Unggulan'),
            'team_heading' => (string) static::get('home_team_heading', 'Tim Kami'),
        ];
    }

    public static function normalizeHexColor(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $value)) {
            return strtolower($value);
        }
        if (preg_match('/^#([A-Fa-f0-9]{3})$/', $value, $m)) {
            $h = $m[1];

            return strtolower('#'.$h[0].$h[0].$h[1].$h[1].$h[2].$h[2]);
        }

        return strtolower($fallback);
    }

    /**
     * Meta SEO beranda (dikelola di admin /home-seo).
     *
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string,
     *     robots: string,
     *     og_image: ?string,
     *     og_image_url: ?string
     * }
     */
    public static function homeSeo(): array
    {
        $theme = static::homeTheme();
        $appName = static::appName();
        $defaultTitle = $appName.' — '.$theme['tagline'];
        $defaultDescription = $theme['description'];
        $ogPath = static::get('seo_home_og_image');
        $ogPath = $ogPath ? ltrim(str_replace('public/', '', (string) $ogPath), '/') : null;

        return [
            'title' => (string) static::get('seo_home_title', $defaultTitle),
            'description' => (string) static::get('seo_home_description', $defaultDescription),
            'keywords' => (string) static::get(
                'seo_home_keywords',
                'IT, otomasi, infrastruktur, desain, hukum, pendidikan, bisnis'
            ),
            'robots' => (string) static::get('seo_home_robots', 'index,follow'),
            'og_image' => $ogPath,
            'og_image_url' => $ogPath ? asset('storage/'.$ogPath) : null,
        ];
    }
}
