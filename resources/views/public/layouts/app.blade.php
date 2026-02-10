<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ir Teguh Solution - Solusi IT & Kreatif Terintegrasi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            body { background: #fff !important; }
            .fixed.inset-0, nav, footer, #mobile-menu-btn, #mobile-menu, .no-print { display: none !important; }
            .print-header-only { display: block !important; }
            main { padding-top: 0 !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
    {{-- Print header: visible only when printing --}}
    <div class="print-header-only hidden border-b border-gray-300 pb-3 mb-4">
        <div class="flex items-center gap-4">
            @php $printLogoUrl = \App\Models\Setting::logoPath(); @endphp
            @if($printLogoUrl)
            <span class="site-logo-wrap site-logo-wrap--print">
                <img src="{{ $printLogoUrl }}" alt="{{ config('app.name', 'Ir Teguh Solution') }}" class="site-logo" width="160" height="40">
            </span>
            @endif
            <h1 class="text-lg font-bold text-gray-900">{{ config('app.name', 'Ir Teguh Solution') }}</h1>
        </div>
    </div>
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0YzAtMS4xLS45LTItMi0ySDI2Yy0xLjEgMC0yIC45LTIgMnYyYzAgMS4xLjkgMiAyIDJoOGMxLjEgMCAyLS45IDItMnYtMnoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-20 animate-pulse"></div>
        <div class="absolute top-0 left-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-96 h-96 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-50 backdrop-blur-md bg-white/10 border-b border-white/20">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-2xl font-bold text-white hover:text-purple-200 transition">
                    @php
                        $siteLogoUrl = \App\Models\Setting::logoPath();
                        $logoW = \App\Models\Setting::logoLandingWidth();
                        $logoH = \App\Models\Setting::logoLandingHeight();
                    @endphp
                    @if($siteLogoUrl)
                    <span class="site-logo-wrap site-logo-wrap--nav" style="width: {{ $logoW }}px; height: {{ $logoH }}px;">
                        <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name', 'Ir Teguh Solution') }}" class="site-logo" width="{{ $logoW }}" height="{{ $logoH }}" loading="lazy">
                    </span>
                    @else
                    <span>Ir Teguh Solution</span>
                    @endif
                </a>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('home') }}" class="text-white/90 hover:text-white transition {{ request()->routeIs('home') ? 'text-white font-semibold' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="text-white/90 hover:text-white transition {{ request()->routeIs('about') ? 'text-white font-semibold' : '' }}">Tentang</a>
                    <a href="{{ route('services') }}" class="text-white/90 hover:text-white transition {{ request()->routeIs('services') ? 'text-white font-semibold' : '' }}">Layanan</a>
                    <a href="{{ route('portfolio.index') }}" class="text-white/90 hover:text-white transition {{ request()->routeIs('portfolio.*') ? 'text-white font-semibold' : '' }}">Portfolio</a>
                    <a href="{{ route('contact') }}" class="text-white/90 hover:text-white transition {{ request()->routeIs('contact') ? 'text-white font-semibold' : '' }}">Kontak</a>
                </div>
                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="md:hidden text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2">
                <a href="{{ route('home') }}" class="block text-white/90 hover:text-white transition">Beranda</a>
                <a href="{{ route('about') }}" class="block text-white/90 hover:text-white transition">Tentang</a>
                <a href="{{ route('services') }}" class="block text-white/90 hover:text-white transition">Layanan</a>
                <a href="{{ route('portfolio.index') }}" class="block text-white/90 hover:text-white transition">Portfolio</a>
                <a href="{{ route('contact') }}" class="block text-white/90 hover:text-white transition">Kontak</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="relative z-10 backdrop-blur-md bg-white/10 border-t border-white/20 mt-20">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Ir Teguh Solution</h3>
                    <p class="text-white/70 text-sm">Solusi IT & Kreatif Terintegrasi untuk Pendidikan dan Bisnis</p>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-white/70 hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('about') }}" class="text-white/70 hover:text-white transition">Tentang</a></li>
                        <li><a href="{{ route('services') }}" class="text-white/70 hover:text-white transition">Layanan</a></li>
                        <li><a href="{{ route('portfolio.index') }}" class="text-white/70 hover:text-white transition">Portfolio</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">Kontak</h3>
                    <p class="text-white/70 text-sm mb-2">Surabaya, Indonesia</p>
                    <a href="{{ route('contact') }}" class="text-white/70 hover:text-white transition text-sm">Hubungi Kami →</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-white/20 text-center text-white/50 text-sm">
                <p>&copy; {{ date('Y') }} Ir Teguh Solution. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
