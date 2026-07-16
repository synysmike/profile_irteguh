<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentLetterAssignee extends Model
{
    protected $fillable = [
        'assignment_letter_id',
        'sort_order',
        'name',
        'gender',
        'ktp',
        'phone',
    ];

    public function assignmentLetter(): BelongsTo
    {
        return $this->belongsTo(AssignmentLetter::class);
    }

    public function genderLabel(): string
    {
        return AssignmentLetter::genderLabels()[$this->gender] ?? $this->gender;
    }
}
