<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Solusi Digital untuk Pendidikan Modern',
                'excerpt' => 'Bagaimana teknologi membantu sekolah dan kampus meningkatkan efisiensi operasional.',
                'content' => '<p>Transformasi digital di sektor pendidikan semakin mendesak. Dari sistem absensi hingga pembelajaran hybrid, integrasi IT yang tepat dapat mengurangi beban administratif dan meningkatkan pengalaman belajar.</p><p>Kami membantu institusi merancang infrastruktur yang aman, skalabel, dan mudah dikelola.</p>',
                'author_name' => 'Tim Editorial',
            ],
            [
                'title' => 'Otomasi Workflow untuk Bisnis UMKM',
                'excerpt' => 'Langkah praktis mengotomatisasi proses bisnis tanpa biaya berlebihan.',
                'content' => '<p>UMKM sering terjebak pada pekerjaan berulang yang memakan waktu. Dengan otomasi workflow yang tepat, proses invoice, follow-up pelanggan, dan pelaporan dapat berjalan lebih rapi.</p><ul><li>Integrasi pesan WhatsApp</li><li>Notifikasi otomatis</li><li>Dashboard sederhana</li></ul>',
                'author_name' => 'Tim Editorial',
            ],
        ];

        foreach ($items as $item) {
            $slug = \Illuminate\Support\Str::slug($item['title']);
            News::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'author_name' => $item['author_name'],
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 10)),
                    'views_count' => rand(12, 180),
                ]
            );
        }
    }
}
