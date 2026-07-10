<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCategory extends Model
{
    protected $fillable = [
        'name',
        'legacy_key',
        'description',
    ];

    public function customerTypes(): HasMany
    {
        return $this->hasMany(CustomerType::class);
    }

    public static function legacyKeyLabels(): array
    {
        return [
            'individual' => 'Perilaku Individu',
            'company' => 'Perilaku Perusahaan',
        ];
    }
}
