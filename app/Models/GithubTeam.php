<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GithubTeam extends Model
{
    protected $fillable = [
        'name',
        'github_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function contributors(): HasMany
    {
        return $this->hasMany(Contributor::class)->orderBy('order');
    }

    public function activeContributors(): HasMany
    {
        return $this->contributors()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Ambil username/org dari URL GitHub.
     * Contoh: https://github.com/ir-teguh -> ir-teguh
     */
    public function githubLogin(): ?string
    {
        $url = trim((string) $this->github_url);
        if ($url === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://github.com/' . ltrim($url, '/');
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $segment = trim(explode('/', trim($path, '/'))[0] ?? '');
        if ($segment === '' || ! preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9]|-(?=[A-Za-z0-9])){0,38}$/', $segment)) {
            return null;
        }

        return $segment;
    }

    public function normalizedGithubUrl(): string
    {
        $login = $this->githubLogin();
        if ($login) {
            return 'https://github.com/' . $login;
        }

        return $this->github_url;
    }
}
