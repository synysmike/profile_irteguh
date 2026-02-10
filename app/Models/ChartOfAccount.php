<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = ['code', 'name', 'type', 'parent_id', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function types()
    {
        return [
            'kas' => 'Kas',
            'piutang' => 'Piutang',
            'hutang' => 'Hutang',
            'modal' => 'Modal',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban',
        ];
    }
}
