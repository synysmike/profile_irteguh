<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'link_url',
        'link_text',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope untuk mendapatkan slide yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk mengurutkan berdasarkan order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function resolvedImageUrl(): string
    {
        $url = trim((string) $this->image_url);

        if ($url === '') {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return asset(ltrim($url, '/'));
        }

        return asset($url);
    }
}
