<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name', 'nip', 'position', 'department', 'basic_salary',
        'bank_name', 'bank_account', 'npwp', 'email', 'phone',
        'is_active', 'order',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
