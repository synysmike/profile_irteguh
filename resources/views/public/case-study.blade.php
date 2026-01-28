@extends('public.layouts.app')

@section('title', $caseStudy->title . ' - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="mb-8 text-white/60 text-sm">
                <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a> / 
                <a href="{{ route('portfolio.index') }}" class="hover:text-white transition">Portfolio</a> / 
                <span class="text-white">{{ $caseStudy->title }}</span>
            </nav>

            <!-- Header -->
            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-sm text-white/80 bg-white/10 px-4 py-2 rounded-full">{{ $caseStudy->category }}</span>
                    <span class="text-sm text-white/60">{{ $caseStudy->year }}</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">{{ $caseStudy->title }}</h1>
                @if($caseStudy->client_context)
                <p class="text-white/80 text-lg mb-6">
                    <strong>Klien/Konteks:</strong> {{ $caseStudy->client_context }}
                </p>
                @endif
                @if($caseStudy->tags && count($caseStudy->tags) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($caseStudy->tags as $tag)
                    <span class="text-xs text-white/70 bg-white/10 px-3 py-1 rounded-full">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Visuals -->
            @if($caseStudy->visuals && count($caseStudy->visuals) > 0)
            <div class="glass-card rounded-xl p-6 mb-12">
                <h2 class="text-2xl font-bold text-white mb-4">Visual</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($caseStudy->visuals as $visual)
                    <div class="bg-white/10 rounded-lg h-64 flex items-center justify-center">
                        <span class="text-white/50">Gambar: {{ basename($visual) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Challenge -->
            <div class="glass-card rounded-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-white mb-4">Tantangan</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">
                    {{ $caseStudy->challenge }}
                </div>
            </div>

            <!-- Solution -->
            <div class="glass-card rounded-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-white mb-4">Solusi</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">
                    {{ $caseStudy->solution }}
                </div>
            </div>

            <!-- Outcome -->
            <div class="glass-card rounded-xl p-8 mb-12">
                <h2 class="text-3xl font-bold text-white mb-4">Hasil</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">
                    {{ $caseStudy->outcome }}
                </div>
            </div>

            <!-- Related Case Studies -->
            @if($related->count() > 0)
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-white mb-6">Proyek Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($related as $relatedCase)
                    <a href="{{ route('case-study.show', $relatedCase->slug) }}" 
                       class="glass-card rounded-xl p-6 hover:scale-105 transition transform">
                        <h3 class="text-lg font-bold text-white mb-2">{{ $relatedCase->title }}</h3>
                        <p class="text-white/70 text-sm">{{ Str::limit($relatedCase->excerpt ?? $relatedCase->challenge, 80) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Navigation -->
            <div class="flex justify-between items-center">
                <a href="{{ route('portfolio.index') }}" class="text-white/80 hover:text-white transition">
                    ← Kembali ke Portfolio
                </a>
                <a href="{{ route('contact') }}" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Diskusikan Proyek Serupa
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
