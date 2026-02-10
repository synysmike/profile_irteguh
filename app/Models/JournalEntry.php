<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_date', 'reference', 'description',
        'is_posted', 'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('entry_date', 'desc')->orderBy('id', 'desc');
    }
}
