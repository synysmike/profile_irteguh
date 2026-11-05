<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectReviewMedia extends Model
{
    protected $fillable = [
        'project_review_id',
        'file_path',
        'media_type',
        'original_name',
        'mime_type',
        'file_size',
        'sort_order',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(ProjectReview::class, 'project_review_id');
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }

    public function url(): string
    {
        return asset('storage/' . ltrim($this->file_path, '/'));
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (self $media) {
            $media->deleteFile();
        });
    }
}
