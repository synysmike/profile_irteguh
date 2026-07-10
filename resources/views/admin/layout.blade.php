<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Ir Teguh Solution')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-ajax.js'])
    @stack('scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        /* Dropdown: tertutup default, terbuka saat hover atau class .open */
        .nav-dropdown-wrap .nav-dropdown { display: none; }
        .nav-dropdown-wrap:hover .nav-dropdown { display: block; }
        .nav-dropdown-wrap.open .nav-dropdown { display: block; }
        @media print {
            nav, .no-print { display: none !important; }
            .print-header-only { display: block !important; }
        }
    </style>
</head>
<body>
    {{-- Print header: visible only when printing --}}
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
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-gray-800 hover:text-purple-600 transition">{{ \App\Models\Setting::appName() }}</a>
                    <span class="text-xs text-gray-400 font-medium uppercase tracking-wider hidden sm:inline">Admin</span>
                </div>
                <div class="flex items-center gap-1">
                    {{-- Dashboard --}}
                    <a href="{{ route('admin.dashboard') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">Dashboard</a>

                    {{-- Konten Website --}}
                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.case-studies.*', 'admin.slides.*', 'admin.hero-texts.*', 'admin.services.*', 'admin.contributors.*', 'admin.contact.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Konten
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute left-0 top-full pt-1 w-52 z-50">
                            <div class="py-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                <a href="{{ route('admin.case-studies.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Studi Kasus</a>
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
                                    <a href="{{ route('admin.site-logo.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logo Situs</a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Statistik & Pesan --}}
                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.visitors.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Statistik & Pesan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute left-0 top-full pt-1 w-52 z-50">
                            <div class="py-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                <a href="{{ route('admin.visitors.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengunjung</a>
                            </div>
                        </div>
                    </div>

                    {{-- Keuangan (Pembukuan & Pajak) --}}
                    @if(Route::has('admin.keuangan.dashboard'))
                    <a href="{{ route('admin.keuangan.dashboard') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.keuangan.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">Keuangan</a>
                    @endif
                    @if(Route::has('admin.projects.index'))
                    <a href="{{ route('admin.projects.index') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.projects.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">Project</a>
                    @endif
                    {{-- Pembukuan (quick link: Tempat Grosir & Customer, terkait Keuangan) --}}
                    @if(Route::has('admin.suppliers.index') && Route::has('admin.customers.index'))
                    <div class="relative nav-dropdown-wrap">
                        <button type="button" class="nav-dropdown-trigger px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 inline-flex items-center gap-1 {{ request()->routeIs('admin.suppliers.*', 'admin.customers.*') ? 'bg-purple-50 text-purple-700' : '' }}">
                            Pembukuan
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="nav-dropdown absolute left-0 top-full pt-1 w-52 z-50">
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

                    {{-- Pengaturan (User) --}}
                    @if(auth()->user()->canManageUsers())
                    <a href="{{ route('admin.users.index') }}" class="nav-link px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">User</a>
                    @endif

                    <div class="border-l border-gray-200 h-6 mx-2" aria-hidden="true"></div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 @if(request()->routeIs('admin.keuangan.*')) max-w-[1600px] @endif">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
        @endif

        @yield('content')
    </div>

    {{-- Dropdown: buka/tutup on click (untuk layar sentuh), hover via CSS --}}
    <script>
    (function() {
        function initNavDropdowns() {
            document.querySelectorAll('.nav-dropdown-trigger').forEach(function(btn) {
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
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNavDropdowns);
        } else {
            initNavDropdowns();
        }
    })();
    </script>
</body>
</html>
