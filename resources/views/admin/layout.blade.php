<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Ir Teguh Solution')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-ajax.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .nav-dropdown-wrap .nav-dropdown { display: none; }
        .nav-dropdown-wrap:hover .nav-dropdown { display: block; }
        .nav-dropdown-wrap.open .nav-dropdown { display: block; }
        @media (max-width: 1023px) {
            .nav-dropdown-wrap:hover .nav-dropdown { display: none; }
            .nav-dropdown-wrap.open .nav-dropdown { display: block; }
        }
        @media print {
            nav, .no-print, #admin-mobile-menu, #admin-menu-toggle { display: none !important; }
            .print-header-only { display: block !important; }
        }
    </style>
</head>
<body class="admin-body">
    <div class="print-header-only hidden border-b border-gray-300 pb-3 mb-4">
        <div class="flex items-center gap-4">
            @php $printLogoUrl = \App\Models\Setting::logoPath(); @endphp
            @if($printLogoUrl)
            <span class="site-logo-wrap site-logo-wrap--print">
                <img src="{{ $printLogoUrl }}" alt="{{ \App\Models\Setting::appName() }}" class="site-logo" width="160" height="40">
            </span>
            @endif
            <h1 class="text-lg font-bold text-gray-900">{{ \App\Models\Setting::appName() }}</h1>
        </div>
    </div>

    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="container mx-auto px-3 sm:px-4 @if(request()->routeIs('admin.keuangan.*', 'admin.projects.*')) max-w-[1600px] @endif">
            <div class="flex items-center justify-between h-14 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <button type="button" id="admin-menu-toggle" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:bg-gray-100" aria-expanded="false" aria-controls="admin-mobile-menu" aria-label="Buka menu">
                        <svg class="w-6 h-6 admin-menu-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg class="w-6 h-6 admin-menu-icon-close hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="text-base sm:text-xl font-bold text-gray-800 hover:text-purple-600 transition truncate">{{ \App\Models\Setting::appName() }}</a>
                    <span class="text-xs text-gray-400 font-medium uppercase tracking-wider hidden sm:inline shrink-0">Admin</span>
                </div>

                {{-- Desktop nav --}}
                <div class="hidden lg:flex items-center gap-1 flex-wrap justify-end">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">Dashboard</a>

                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.case-studies.*', 'admin.slides.*', 'admin.hero-texts.*', 'admin.services.*', 'admin.contributors.*', 'admin.contact.*', 'admin.news.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Konten
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute left-0 top-full pt-1 w-52 z-50">
                            <div class="py-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                <a href="{{ route('admin.case-studies.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Studi Kasus</a>
                                <a href="{{ route('admin.news.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.news.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">Berita</a>
                                <a href="{{ route('admin.slides.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Slide</a>
                                <a href="{{ route('admin.hero-texts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Teks Hero</a>
                                <a href="{{ route('admin.services.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Layanan</a>
                                <a href="{{ route('admin.contributors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tim</a>
                                <a href="{{ route('admin.contact.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 relative">
                                    Kontak
                                    @if(\App\Models\ContactMessage::unread()->count() > 0)
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-red-500 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">{{ \App\Models\ContactMessage::unread()->count() }}</span>
                                    @endif
                                </a>
                                @if(Route::has('admin.site-logo.edit'))
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <a href="{{ route('admin.site-logo.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logo & Korp Surat</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.visitors.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Statistik
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute left-0 top-full pt-1 w-56 z-50">
                            <div class="py-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                <a href="{{ route('admin.visitors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.visitors.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">Visitor Counter</a>
                            </div>
                        </div>
                    </div>

                    @if(Route::has('admin.keuangan.dashboard'))
                    <a href="{{ route('admin.keuangan.dashboard') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.keuangan.*', 'admin.projects.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">Keuangan</a>
                    @endif

                    @if(Route::has('admin.suppliers.index') && Route::has('admin.customers.index'))
                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.suppliers.*', 'admin.customers.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Pembukuan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute right-0 top-full pt-1 w-52 z-50">
                            <div class="py-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                <a href="{{ route('admin.suppliers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Tempat Grosir (Vendor)</a>
                                <a href="{{ route('admin.customers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Customer (Klien)</a>
                                @if(Route::has('admin.keuangan.master.klien-vendor'))
                                <div class="border-t border-gray-100 mt-1 pt-1">
                                    <a href="{{ route('admin.keuangan.master.klien-vendor') }}" class="block px-4 py-2 text-sm text-purple-600 hover:bg-purple-50">Semua Menu Keuangan →</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->canManageUsers())
                    <a href="{{ route('admin.users.index') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">User</a>
                    @endif

                    <div class="border-l border-gray-200 h-6 mx-2" aria-hidden="true"></div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition">Logout</button>
                    </form>
                </div>

                {{-- Mobile logout shortcut --}}
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="px-2 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-md">Logout</button>
                </form>
            </div>
        </div>

        {{-- Mobile menu panel --}}
        <div id="admin-mobile-menu" class="lg:hidden hidden border-t border-gray-200 bg-white max-h-[calc(100vh-3.5rem)] overflow-y-auto">
            <div class="container mx-auto px-3 py-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-purple-50 text-purple-700' : 'text-gray-700 hover:bg-gray-50' }}">Dashboard</a>

                <details class="group rounded-md" {{ request()->routeIs('admin.case-studies.*', 'admin.slides.*', 'admin.hero-texts.*', 'admin.services.*', 'admin.contributors.*', 'admin.contact.*', 'admin.news.*', 'admin.site-logo.*') ? 'open' : '' }}>
                    <summary class="px-3 py-2.5 text-sm font-medium text-gray-700 cursor-pointer list-none flex items-center justify-between hover:bg-gray-50 rounded-md">
                        Konten
                        <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="pl-3 pb-2 space-y-0.5">
                        <a href="{{ route('admin.case-studies.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Studi Kasus</a>
                        <a href="{{ route('admin.news.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Berita</a>
                        <a href="{{ route('admin.slides.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Slide</a>
                        <a href="{{ route('admin.hero-texts.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Teks Hero</a>
                        <a href="{{ route('admin.services.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Layanan</a>
                        <a href="{{ route('admin.contributors.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Tim</a>
                        <a href="{{ route('admin.contact.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Kontak</a>
                        @if(Route::has('admin.site-logo.edit'))
                        <a href="{{ route('admin.site-logo.edit') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Logo & Korp Surat</a>
                        @endif
                    </div>
                </details>

                <a href="{{ route('admin.visitors.index') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.visitors.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-700 hover:bg-gray-50' }}">Statistik / Visitor</a>

                @if(Route::has('admin.keuangan.dashboard'))
                <a href="{{ route('admin.keuangan.dashboard') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.keuangan.*', 'admin.projects.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-700 hover:bg-gray-50' }}">Keuangan</a>
                @endif

                @if(Route::has('admin.suppliers.index'))
                <details class="group rounded-md" {{ request()->routeIs('admin.suppliers.*', 'admin.customers.*') ? 'open' : '' }}>
                    <summary class="px-3 py-2.5 text-sm font-medium text-gray-700 cursor-pointer list-none flex items-center justify-between hover:bg-gray-50 rounded-md">
                        Pembukuan
                        <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="pl-3 pb-2 space-y-0.5">
                        <a href="{{ route('admin.suppliers.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Tempat Grosir (Vendor)</a>
                        <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md">Customer (Klien)</a>
                    </div>
                </details>
                @endif

                @if(auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-700 hover:bg-gray-50' }}">User</a>
                @endif
            </div>
        </div>
    </nav>

    <div class="admin-content container mx-auto px-3 sm:px-4 py-4 sm:py-8 @if(request()->routeIs('admin.keuangan.*', 'admin.projects.*')) max-w-[1600px] @endif">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
            <p class="text-green-600 text-sm sm:text-base">{{ session('success') }}</p>
        </div>
        @endif

        @yield('content')
    </div>

    <script>
    (function() {
        function initAdminMobileMenu() {
            var toggle = document.getElementById('admin-menu-toggle');
            var panel = document.getElementById('admin-mobile-menu');
            if (!toggle || !panel) return;

            toggle.addEventListener('click', function() {
                var open = panel.classList.toggle('hidden') === false;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.querySelector('.admin-menu-icon-open')?.classList.toggle('hidden', open);
                toggle.querySelector('.admin-menu-icon-close')?.classList.toggle('hidden', !open);
            });
        }

        function initNavDropdowns() {
            document.querySelectorAll('nav .nav-dropdown-trigger').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var wrap = this.closest('.nav-dropdown-wrap');
                    if (!wrap) return;
                    wrap.classList.toggle('open');
                    document.querySelectorAll('nav .nav-dropdown-wrap').forEach(function(w) {
                        if (w !== wrap) w.classList.remove('open');
                    });
                });
            });
            document.addEventListener('click', function(e) {
                if (e.target.closest('.nav-dropdown-wrap')) return;
                document.querySelectorAll('nav .nav-dropdown-wrap.open').forEach(function(w) {
                    w.classList.remove('open');
                });
            });
        }

        function boot() {
            initAdminMobileMenu();
            initNavDropdowns();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
    </script>
    @stack('scripts')
</body>
</html>
