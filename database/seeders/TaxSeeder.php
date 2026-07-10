<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tax::updateOrCreate(
            ['code' => 'PPH23'],
            [
                'name' => 'PPh 23',
                'rate' => 2.00,
                'calculation_type' => 'deduction',
                'description' => 'PPh Pasal 23 2% untuk non-PKP.',
                'is_active' => true,
            ]
        );
    }
}
