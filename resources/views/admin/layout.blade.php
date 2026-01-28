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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-gray-800">Ir Teguh Solution</a>
                    <span class="text-sm text-gray-500">Admin Panel</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">Dashboard</a>
                    <a href="{{ route('admin.case-studies.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Studi Kasus</a>
                    <a href="{{ route('admin.slides.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Slide</a>
                    <a href="{{ route('admin.contributors.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Tim</a>
                    <a href="{{ route('admin.visitors.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Pengunjung</a>
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-sm text-gray-600 hover:text-gray-800 relative">
                        Pesan Kontak
                        @if(\App\Models\ContactMessage::unread()->count() > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            {{ \App\Models\ContactMessage::unread()->count() }}
                        </span>
                        @endif
                    </a>
                    @if(auth()->user()->canManageUsers())
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-800">User</a>
                    @endif
                    <div class="border-l border-gray-300 h-6 mx-2"></div>
                    @if(Route::has('admin.suppliers.index'))
                    <a href="{{ route('admin.suppliers.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Tempat Grosir</a>
                    @endif
                    @if(Route::has('admin.customers.index'))
                    <a href="{{ route('admin.customers.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Customer</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-600">{{ session('success') }}</p>
        </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
