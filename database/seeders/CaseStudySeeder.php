<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseStudy;
use Illuminate\Support\Str;

class CaseStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing case studies if re-seeding
        CaseStudy::truncate();
        
        $caseStudies = [
            [
                'title' => 'Deployment Docker untuk Akses Publik yang Aman',
                'slug' => 'deployment-docker-akses-publik-aman',
                'client_context' => 'Lembaga pendidikan yang memerlukan akses publik yang aman ke layanan internal',
                'challenge' => 'Klien perlu mengekspos layanan internal ke internet publik sambil menjaga keamanan dan skalabilitas. Setup server tradisional sulit dikelola dan diskalakan, serta konfigurasi keamanan yang kompleks.',
                'solution' => 'Mengimplementasikan strategi containerisasi berbasis Docker dengan reverse proxy Nginx dan terminasi HTTPS. Menyiapkan manajemen sertifikat SSL otomatis menggunakan Let\'s Encrypt, mengonfigurasi aturan firewall, dan mengimplementasikan orkestrasi container untuk scaling dan update yang mudah.',
                'outcome' => 'Berhasil mendeploy beberapa layanan dengan update zero-downtime, meningkatkan postur keamanan dengan pembaruan SSL otomatis, dan mengurangi overhead manajemen server sebesar 60%. Solusi ini dapat diskalakan secara horizontal dan dapat menangani lonjakan lalu lintas dengan efisien.',
                'visuals' => null,
                'tags' => ['Docker', 'Nginx', 'HTTPS', 'Keamanan', 'DevOps'],
                'category' => 'Infrastruktur IT',
                'year' => 2024,
                'excerpt' => 'Solusi deployment containerized dengan konfigurasi SSL otomatis dan reverse proxy untuk akses publik yang aman.',
                'featured' => true,
                'order' => 1,
            ],
            [
                'title' => 'Workflow Pemrosesan Dokumen Otomatis',
                'slug' => 'workflow-pemrosesan-dokumen-otomatis',
                'client_context' => 'Perusahaan jasa bisnis yang memproses ratusan dokumen setiap hari',
                'challenge' => 'Pemrosesan dokumen manual memakan waktu, rawan kesalahan, dan memerlukan sumber daya manusia yang signifikan. Perusahaan menghabiskan lebih dari 8 jam setiap hari untuk tugas penanganan dokumen yang berulang.',
                'solution' => 'Mengembangkan sistem workflow otomatis menggunakan script Python, teknologi OCR, dan integrasi API. Membuat pipeline yang secara otomatis mengekstrak data dari dokumen, memvalidasi informasi, dan merutekan dokumen ke sistem yang sesuai. Mengimplementasikan penanganan kesalahan dan antrian review manual untuk kasus edge.',
                'outcome' => 'Mengurangi waktu pemrosesan dokumen sebesar 85%, dari 8 jam menjadi sekitar 1 jam setiap hari. Tingkat kesalahan menurun dari 15% menjadi kurang dari 2%. Staf sekarang dapat fokus pada tugas bernilai tinggi daripada entri data manual.',
                'visuals' => null,
                'tags' => ['Otomasi', 'Python', 'OCR', 'Workflow', 'Integrasi API'],
                'category' => 'Otomasi & Workflow',
                'year' => 2024,
                'excerpt' => 'Sistem pemrosesan dokumen otomatis yang mengurangi pekerjaan manual sebesar 85% dan meningkatkan akurasi.',
                'featured' => true,
                'order' => 2,
            ],
            [
                'title' => 'Identitas Merek & Desain Website untuk Startup Teknologi',
                'slug' => 'identitas-merek-desain-website-startup',
                'client_context' => 'Startup teknologi tahap awal yang memerlukan identitas merek lengkap',
                'challenge' => 'Startup tidak memiliki identitas merek yang kohesif dan kehadiran online yang profesional. Mereka memerlukan sistem identitas visual lengkap, desain logo, dan website modern untuk membangun kredibilitas dan menarik investor.',
                'solution' => 'Membuat identitas merek komprehensif termasuk logo, palet warna, tipografi, dan pedoman merek. Merancang dan mengembangkan website responsif dengan prinsip UI/UX modern, elemen desain glassmorphism, dan animasi yang halus. Mengimplementasikan praktik SEO terbaik dan waktu loading yang cepat.',
                'outcome' => 'Meluncurkan identitas merek profesional yang beresonansi dengan target audiens. Lalu lintas website meningkat 300% dalam bulan pertama, dan startup berhasil mendapatkan pendanaan seed. Identitas merek sekarang digunakan secara konsisten di semua materi pemasaran.',
                'visuals' => null,
                'tags' => ['Branding', 'Desain Web', 'UI/UX', 'Desain Logo', 'Kreatif'],
                'category' => 'Kreatif/Desain',
                'year' => 2024,
                'excerpt' => 'Identitas merek lengkap dan desain website yang membantu startup mendapatkan pendanaan dan membangun kehadiran pasar.',
                'featured' => true,
                'order' => 3,
            ],
            [
                'title' => 'Pendaftaran Perusahaan & Setup Kepatuhan Hukum',
                'slug' => 'pendaftaran-perusahaan-setup-kepatuhan-hukum',
                'client_context' => 'Entitas bisnis baru yang memerlukan pendaftaran hukum lengkap',
                'challenge' => 'Pengusaha perlu mendaftarkan perusahaan mereka tetapi tidak familiar dengan proses pendaftaran bisnis Indonesia, dokumentasi yang diperlukan, dan persyaratan kepatuhan yang berkelanjutan.',
                'solution' => 'Menyediakan layanan hukum komprehensif termasuk verifikasi nama perusahaan, persiapan dokumen, pengajuan pendaftaran, perolehan NPWP, dan aplikasi izin usaha. Membuat kalender kepatuhan dan menyediakan dukungan berkelanjutan untuk persyaratan regulasi.',
                'outcome' => 'Berhasil mendaftarkan perusahaan dalam 3 minggu (lebih cepat dari timeline tipikal 6-8 minggu). Semua lisensi dan izin yang diperlukan diperoleh. Klien sekarang memiliki pemahaman yang jelas tentang persyaratan kepatuhan dan mempertahankan status hukum yang tepat.',
                'visuals' => null,
                'tags' => ['Layanan Hukum', 'Pendaftaran Perusahaan', 'Kepatuhan', 'Layanan Bisnis'],
                'category' => 'Layanan Hukum/Bisnis',
                'year' => 2024,
                'excerpt' => 'Pendaftaran perusahaan lengkap dan setup kepatuhan hukum yang diselesaikan dalam waktu rekor.',
                'featured' => true,
                'order' => 4,
            ],
            [
                'title' => 'Migrasi Infrastruktur Cloud',
                'slug' => 'migrasi-infrastruktur-cloud',
                'client_context' => 'Bisnis menengah yang bermigrasi dari on-premise ke cloud',
                'challenge' => 'Bisnis menjalankan aplikasi kritis pada server on-premise yang menua dengan skalabilitas terbatas dan biaya perawatan tinggi. Downtime selama migrasi perlu diminimalkan.',
                'solution' => 'Merancang dan mengeksekusi strategi migrasi bertahap ke infrastruktur cloud AWS. Mengimplementasikan infrastruktur sebagai kode menggunakan Terraform, menyiapkan backup otomatis, mengonfigurasi load balancing, dan membangun sistem monitoring dan alerting. Menggunakan strategi deployment blue-green untuk memastikan zero downtime.',
                'outcome' => 'Berhasil memigrasikan semua layanan dengan zero downtime. Mengurangi biaya infrastruktur sebesar 40% sambil meningkatkan skalabilitas dan keandalan. Uptime sistem meningkat dari 95% menjadi 99,9%. Kemampuan disaster recovery meningkat secara signifikan.',
                'visuals' => null,
                'tags' => ['Cloud', 'AWS', 'Migrasi', 'DevOps', 'Infrastruktur'],
                'category' => 'Infrastruktur IT',
                'year' => 2023,
                'excerpt' => 'Migrasi cloud zero-downtime yang mengurangi biaya sebesar 40% dan meningkatkan keandalan.',
                'featured' => false,
                'order' => 5,
            ],
            [
                'title' => 'Integrasi Platform E-commerce',
                'slug' => 'integrasi-platform-ecommerce',
                'client_context' => 'Bisnis ritel yang mengintegrasikan beberapa saluran penjualan',
                'challenge' => 'Bisnis mengelola inventori dan pesanan di beberapa platform secara manual, menyebabkan overselling, ketidaksesuaian inventori, dan keterlambatan pemenuhan pesanan.',
                'solution' => 'Membangun platform integrasi terpusat yang menghubungkan platform e-commerce, sistem manajemen inventori, dan pemenuhan pesanan. Mengimplementasikan sinkronisasi inventori real-time, pemrosesan pesanan otomatis, dan dashboard pelaporan terpadu.',
                'outcome' => 'Menghilangkan masalah overselling sepenuhnya. Waktu pemrosesan pesanan berkurang dari 4 jam menjadi 15 menit. Akurasi inventori meningkat menjadi 99,5%. Bisnis sekarang dapat berkembang ke saluran penjualan tambahan tanpa peningkatan overhead operasional yang proporsional.',
                'visuals' => null,
                'tags' => ['E-commerce', 'Integrasi API', 'Otomasi', 'Manajemen Inventori'],
                'category' => 'Otomasi & Workflow',
                'year' => 2023,
                'excerpt' => 'Integrasi e-commerce multi-platform yang menghilangkan overselling dan merampingkan operasi.',
                'featured' => false,
                'order' => 6,
            ],
        ];

        foreach ($caseStudies as $caseStudy) {
            if (!isset($caseStudy['slug'])) {
                $caseStudy['slug'] = Str::slug($caseStudy['title']);
            }
            CaseStudy::create($caseStudy);
        }
    }
}
