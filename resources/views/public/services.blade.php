@extends('public.layouts.app')

@section('title', 'Layanan - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-16">
                <h1 class="text-5xl font-bold text-white mb-6">Layanan Kami</h1>
                <p class="text-xl text-white/80">Solusi komprehensif untuk kebutuhan bisnis Anda</p>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                <!-- IT Infrastructure -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">🖥️</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Infrastruktur IT</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Deployment & containerisasi Docker</li>
                        <li>• Manajemen & perawatan server</li>
                        <li>• Setup infrastruktur cloud</li>
                        <li>• Konfigurasi jaringan & keamanan</li>
                        <li>• Administrasi database</li>
                        <li>• Setup reverse proxy HTTPS</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>

                <!-- Automation & Workflow -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">⚙️</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Otomasi & Workflow</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Otomasi proses bisnis</li>
                        <li>• Optimasi workflow</li>
                        <li>• Integrasi API</li>
                        <li>• Penjadwalan tugas & otomasi</li>
                        <li>• Pipeline pemrosesan data</li>
                        <li>• Script otomasi kustom</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>

                <!-- Creative Design -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">🎨</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Desain Kreatif</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Identitas merek & desain logo</li>
                        <li>• Desain UI/UX</li>
                        <li>• Desain & pengembangan web</li>
                        <li>• Materi pemasaran</li>
                        <li>• Grafis media sosial</li>
                        <li>• Desain cetak</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>

                <!-- Legal & Business Services -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">⚖️</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Layanan Hukum & Bisnis</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Pendaftaran perusahaan</li>
                        <li>• Bantuan izin usaha</li>
                        <li>• Dokumentasi hukum</li>
                        <li>• Konsultasi kepatuhan</li>
                        <li>• Konsultasi bisnis</li>
                        <li>• Review kontrak</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>

                <!-- Consulting -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">💼</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Konsultasi IT</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Strategi teknologi</li>
                        <li>• Desain arsitektur sistem</li>
                        <li>• Audit keamanan</li>
                        <li>• Optimasi performa</li>
                        <li>• Migrasi teknologi</li>
                        <li>• Pelatihan & dukungan</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>

                <!-- Repair & Maintenance -->
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    <div class="text-4xl mb-4">🔧</div>
                    <h3 class="text-2xl font-bold text-white mb-4">Perbaikan & Perawatan</h3>
                    <ul class="space-y-2 text-white/70 mb-6">
                        <li>• Perbaikan hardware</li>
                        <li>• Troubleshooting software</li>
                        <li>• Perawatan sistem</li>
                        <li>• Pemulihan data</li>
                        <li>• Penghapusan virus</li>
                        <li>• Paket perawatan berkala</li>
                    </ul>
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="glass-card rounded-2xl p-12 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Siap Memulai?</h2>
                <p class="text-white/80 text-lg mb-8">
                    Mari diskusikan bagaimana kami dapat membantu mengubah bisnis Anda dengan solusi terintegrasi kami.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('contact') }}" class="px-8 py-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                        Hubungi Kami
                    </a>
                    <a href="{{ route('portfolio.index') }}" class="px-8 py-4 bg-white/20 backdrop-blur-md text-white rounded-lg hover:bg-white/30 transition border border-white/30 font-semibold">
                        Lihat Karya Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
