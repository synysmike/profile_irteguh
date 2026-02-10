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

            <!-- Services Grid (dikelola dari admin) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                @forelse($services as $service)
                <div class="glass-card rounded-xl p-8 hover:scale-105 transition transform">
                    @if($service->icon)
                    <div class="text-4xl mb-4">{{ $service->icon }}</div>
                    @endif
                    <h3 class="text-2xl font-bold text-white mb-4">{{ $service->title }}</h3>
                    @if($service->features && count($service->features) > 0)
                    <ul class="space-y-2 text-white/70 mb-6">
                        @foreach($service->features as $item)
                        <li>• {{ $item }}</li>
                        @endforeach
                    </ul>
                    @endif
                    <a href="{{ route('contact') }}" class="text-purple-300 hover:text-purple-200 transition">
                        Minta Penawaran →
                    </a>
                </div>
                @empty
                <div class="col-span-full text-center py-12 text-white/70">
                    <p>Daftar layanan sedang disiapkan. Silakan hubungi kami untuk informasi lebih lanjut.</p>
                    <a href="{{ route('contact') }}" class="inline-block mt-4 text-purple-300 hover:text-purple-200 transition">Hubungi Kami →</a>
                </div>
                @endforelse
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
