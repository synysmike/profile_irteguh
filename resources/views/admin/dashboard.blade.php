@extends('admin.layout')

@section('title', 'Dashboard Admin - Ir Teguh Solution')

@section('content')
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h2>
            <p class="text-gray-600">Ringkasan dan statistik website</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="text-gray-600 text-sm mb-2">Total Studi Kasus</div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['case_studies'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="text-gray-600 text-sm mb-2">Total Slide</div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['slides'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
                <div class="text-gray-600 text-sm mb-2">Proyek Unggulan</div>
                <div class="text-3xl font-bold text-gray-800">{{ $stats['featured_projects'] }}</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow mb-8 border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Aksi Cepat</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.case-studies.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <div class="text-gray-800 font-semibold">Kelola Studi Kasus</div>
                </a>
                <a href="{{ route('admin.slides.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                    <div class="text-4xl mb-3">🖼️</div>
                    <div class="text-gray-800 font-semibold">Kelola Slide</div>
                </a>
                <a href="{{ route('admin.contributors.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                    <div class="text-4xl mb-3">👥</div>
                    <div class="text-gray-800 font-semibold">Kelola Tim</div>
                </a>
                <a href="{{ route('admin.visitors.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                    <div class="text-4xl mb-3">📊</div>
                    <div class="text-gray-800 font-semibold">Statistik Pengunjung</div>
                </a>
            </div>
            
            <!-- Accounting Section -->
            @if(Route::has('admin.suppliers.index') && Route::has('admin.customers.index'))
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Pembukuan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('admin.suppliers.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                    <div class="text-4xl mb-3">🏪</div>
                    <div class="text-gray-800 font-semibold">Tempat Grosir</div>
                </a>
                <a href="{{ route('admin.customers.index') }}" class="block p-6 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                    <div class="text-4xl mb-3">👤</div>
                    <div class="text-gray-800 font-semibold">Customer</div>
                </a>
            @endif
                    <a href="{{ route('home') }}" target="_blank" class="block p-6 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                        <div class="text-4xl mb-3">🌐</div>
                        <div class="text-gray-800 font-semibold">Lihat Website</div>
                        <div class="text-gray-500 text-xs mt-2">Buka di tab baru</div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Case Studies -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Studi Kasus Terbaru</h3>
            </div>
            <div class="p-6">
                @if($recentCaseStudies->count() > 0)
                <div class="space-y-3">
                    @foreach($recentCaseStudies as $caseStudy)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div>
                            <h4 class="text-gray-800 font-semibold">{{ $caseStudy->title }}</h4>
                            <p class="text-gray-600 text-sm">{{ $caseStudy->category }} • {{ $caseStudy->year }}</p>
                        </div>
                    <a href="{{ route('admin.case-studies.edit', $caseStudy->id) }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition text-sm font-medium">
                        Edit
                    </a>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-600">Belum ada studi kasus.</p>
            @endif
        </div>
    </div>
@endsection
