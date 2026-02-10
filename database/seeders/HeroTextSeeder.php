<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroText;

class HeroTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $texts = [
            ['text' => 'Solusi IT & Kreatif Terintegrasi', 'order' => 0, 'is_active' => true],
            ['text' => 'Untuk Pendidikan dan Bisnis', 'order' => 1, 'is_active' => true],
            ['text' => 'Otomasi & Infrastruktur IT', 'order' => 2, 'is_active' => true],
            ['text' => 'Desain Kreatif & Layanan Hukum', 'order' => 3, 'is_active' => true],
        ];

        foreach ($texts as $item) {
            HeroText::updateOrCreate(
                ['text' => $item['text']],
                ['order' => $item['order'], 'is_active' => $item['is_active']]
            );
        }
    }
}
