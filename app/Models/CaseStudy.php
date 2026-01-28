<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'client_context',
        'challenge',
        'solution',
        'outcome',
        'visuals',
        'tags',
        'category',
        'year',
        'excerpt',
        'featured',
        'order',
    ];

    protected $casts = [
        'visuals' => 'array',
        'tags' => 'array',
        'featured' => 'boolean',
        'year' => 'integer',
        'order' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
