<?php

namespace Database\Seeders;

use App\Models\CustomerCategory;
use Illuminate\Database\Seeder;

class CustomerCategorySeeder extends Seeder
{
    public function run(): void
    {
        CustomerCategory::updateOrCreate(
            ['name' => 'Individu'],
            ['legacy_key' => 'individual']
        );

        CustomerCategory::updateOrCreate(
            ['name' => 'Perusahaan'],
            ['legacy_key' => 'company']
        );
    }
}
