<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Infrastruktur IT',
                'icon' => '🖥️',
                'features' => [
                    'Deployment & containerisasi Docker',
                    'Manajemen & perawatan server',
                    'Setup infrastruktur cloud',
                    'Konfigurasi jaringan & keamanan',
                    'Administrasi database',
                    'Setup reverse proxy HTTPS',
                ],
                'order' => 0,
            ],
            [
                'title' => 'Otomasi & Workflow',
                'icon' => '⚙️',
                'features' => [
                    'Otomasi proses bisnis',
                    'Optimasi workflow',
                    'Integrasi API',
                    'Penjadwalan tugas & otomasi',
                    'Pipeline pemrosesan data',
                    'Script otomasi kustom',
                ],
                'order' => 1,
            ],
            [
                'title' => 'Desain Kreatif',
                'icon' => '🎨',
                'features' => [
                    'Identitas merek & desain logo',
                    'Desain UI/UX',
                    'Desain & pengembangan web',
                    'Materi pemasaran',
                    'Grafis media sosial',
                    'Desain cetak',
                ],
                'order' => 2,
            ],
            [
                'title' => 'Layanan Hukum & Bisnis',
                'icon' => '⚖️',
                'features' => [
                    'Pendaftaran perusahaan',
                    'Bantuan izin usaha',
                    'Dokumentasi hukum',
                    'Konsultasi kepatuhan',
                    'Konsultasi bisnis',
                    'Review kontrak',
                ],
                'order' => 3,
            ],
            [
                'title' => 'Konsultasi IT',
                'icon' => '💼',
                'features' => [
                    'Strategi teknologi',
                    'Desain arsitektur sistem',
                    'Audit keamanan',
                    'Optimasi performa',
                    'Migrasi teknologi',
                    'Pelatihan & dukungan',
                ],
                'order' => 4,
            ],
            [
                'title' => 'Perbaikan & Perawatan',
                'icon' => '🔧',
                'features' => [
                    'Perbaikan hardware',
                    'Troubleshooting software',
                    'Perawatan sistem',
                    'Pemulihan data',
                    'Penghapusan virus',
                    'Paket perawatan berkala',
                ],
                'order' => 5,
            ],
        ];

        foreach ($services as $data) {
            Service::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
