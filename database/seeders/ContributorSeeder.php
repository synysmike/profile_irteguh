<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contributor;

class ContributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing contributors if re-seeding
        Contributor::truncate();
        
        $contributors = [
            [
                'name' => 'Ir Teguh',
                'role' => 'Founder & CEO',
                'image_url' => 'https://i.pravatar.cc/300?img=1',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Sarah Wijaya',
                'role' => 'Lead Designer',
                'image_url' => 'https://i.pravatar.cc/300?img=5',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'role' => 'IT Infrastructure Specialist',
                'image_url' => 'https://i.pravatar.cc/300?img=12',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Dewi Lestari',
                'role' => 'Legal Consultant',
                'image_url' => 'https://i.pravatar.cc/300?img=47',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'role' => 'Automation Engineer',
                'image_url' => 'https://i.pravatar.cc/300?img=33',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Rina Kartika',
                'role' => 'UI/UX Designer',
                'image_url' => 'https://i.pravatar.cc/300?img=68',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Hendra Pratama',
                'role' => 'DevOps Engineer',
                'image_url' => 'https://i.pravatar.cc/300?img=27',
                'order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Maya Sari',
                'role' => 'Business Analyst',
                'image_url' => 'https://i.pravatar.cc/300?img=52',
                'order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($contributors as $contributor) {
            Contributor::create($contributor);
        }
    }
}
