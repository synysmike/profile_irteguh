<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = 'setting_' . $key;
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
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
        Cache::forget('setting_' . $key);
    }

    /**
     * Get application display name (for invoice, header, etc.). Falls back to config('app.name').
     */
    public static function appName(): string
    {
        return (string) static::get('app_name', config('app.name', 'Laravel'));
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
        return (string) static::get('contact_whatsapp', '6281234567890');
    }

    public static function contactWhatsappLabel(): string
    {
        return (string) static::get('contact_whatsapp_label', 'Chat dengan kami di WhatsApp');
    }

    public static function contactResponseNote(): string
    {
        return (string) static::get('contact_response_note', 'Kami biasanya merespons dalam 24-48 jam');
    }

    public static function contactWhatsappUrl(): string
    {
        $number = preg_replace('/\D+/', '', static::contactWhatsapp());
        return $number ? 'https://wa.me/' . $number : '#';
    }
}
