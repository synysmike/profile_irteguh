<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contributor extends Model
{
    protected $fillable = [
        'name',
        'role',
        'image_url',
        'order',
        'is_active',
        'github_team_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'github_team_id' => 'integer',
    ];

    public function githubTeam(): BelongsTo
    {
        return $this->belongsTo(GithubTeam::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
