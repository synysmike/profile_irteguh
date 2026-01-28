<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slide;

class SlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing slides if re-seeding
        Slide::truncate();
        
        $slides = [
            [
                'title' => 'Solusi IT & Kreatif Terintegrasi',
                'description' => 'Mengubah tantangan menjadi peluang melalui otomasi, infrastruktur IT, desain kreatif, dan layanan hukum.',
                'image_url' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=1200&h=600&fit=crop',
                'link_url' => '/services',
                'link_text' => 'Lihat Layanan Kami',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'title' => 'Portfolio Kami',
                'description' => 'Jelajahi proyek-proyek unggulan kami yang telah membantu berbagai klien mencapai tujuan bisnis mereka.',
                'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&h=600&fit=crop',
                'link_url' => '/portfolio',
                'link_text' => 'Lihat Portfolio',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'title' => 'Hubungi Kami',
                'description' => 'Mari diskusikan bagaimana kami dapat membantu mengubah bisnis Anda dengan solusi terintegrasi kami.',
                'image_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&h=600&fit=crop',
                'link_url' => '/contact',
                'link_text' => 'Hubungi Kami',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            Slide::create($slide);
        }
    }
}
