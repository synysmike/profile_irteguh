<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentLetter extends Model
{
    protected $fillable = [
        'project_id',
        'number',
        'letter_date',
        'subject',
        'task_description',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'letter_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public static function genderLabels(): array
    {
        return [
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(AssignmentLetterAssignee::class)->orderBy('sort_order')->orderBy('id');
    }

    public function assigneeNamesSummary(int $limit = 2): string
    {
        $names = $this->assignees->pluck('name')->filter()->values();
        if ($names->isEmpty()) {
            return '—';
        }

        if ($names->count() <= $limit) {
            return $names->implode(', ');
        }

        return $names->take($limit)->implode(', ') . ' +' . ($names->count() - $limit) . ' lainnya';
    }

    public static function generateNumber(): string
    {
        $prefix = 'ST-' . date('Ymd') . '-';
        $last = static::where('number', 'like', $prefix . '%')->orderByDesc('id')->first();
        $seq = $last ? (int) substr($last->number, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
