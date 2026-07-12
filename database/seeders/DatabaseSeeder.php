<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CustomerTypeSeeder::class,
            CustomerCategorySeeder::class,
            TaxSeeder::class,
            SlideSeeder::class,
            HeroTextSeeder::class,
            ServiceSeeder::class,
            ContributorSeeder::class,
            ChartOfAccountSeeder::class,
            CaseStudySeeder::class,
            NewsSeeder::class,
        ]);
    }
}
