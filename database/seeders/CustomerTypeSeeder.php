<?php

namespace Database\Seeders;

use App\Models\CustomerCategory;
use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    public function run(): void
    {
        $individual = CustomerCategory::where('legacy_key', 'individual')->first();
        $company = CustomerCategory::where('legacy_key', 'company')->first();

        if ($individual) {
            CustomerType::updateOrCreate(
                ['name' => 'Individu'],
                ['legacy_key' => 'individual', 'customer_category_id' => $individual->id]
            );
        }

        if ($company) {
            CustomerType::updateOrCreate(
                ['name' => 'Perusahaan'],
                ['legacy_key' => 'company', 'customer_category_id' => $company->id]
            );
        }
    }
}
