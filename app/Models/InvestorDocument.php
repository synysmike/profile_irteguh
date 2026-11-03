<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDocument extends Model
{
    protected $fillable = [
        'investor_id', 'capital_injection_id', 'title', 'file_path', 'uploaded_by',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function capitalInjection(): BelongsTo
    {
        return $this->belongsTo(InvestorCapitalInjection::class, 'capital_injection_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
