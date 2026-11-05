@extends('public.layouts.app')

@section('title', $project->title . ' - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="mb-8 text-white/60 text-sm">
                <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a> /
                <a href="{{ route('portfolio.index') }}" class="hover:text-white transition">Portfolio</a> /
                <span class="text-white">{{ $project->title }}</span>
            </nav>

            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <div class="mb-3">
                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $project->statusBadgeClass() }}">
                        {{ $project->statusLabel() }}
                    </span>
                </div>
                <div class="flex items-center gap-3 mb-6">
                    @if($project->portfolio_category)
                    <span class="text-sm text-white/80 bg-white/10 px-4 py-2 rounded-full">{{ $project->portfolio_category }}</span>
                    @endif
                    @if($project->portfolioYearValue())
                    <span class="text-sm text-white/60">{{ $project->portfolioYearValue() }}</span>
                    @endif
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">{{ $project->title }}</h1>
                @if($project->portfolio_client_context)
                <p class="text-white/80 text-lg mb-6">
                    <strong>Klien/Konteks:</strong> {{ $project->portfolio_client_context }}
                </p>
                @endif
                @if($project->portfolio_tags && count($project->portfolio_tags) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($project->portfolio_tags as $tag)
                    <span class="text-xs text-white/70 bg-white/10 px-3 py-1 rounded-full">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            @php $gallery = $project->portfolioGalleryItems(); @endphp
            @if($project->portfolio_cover_path || count($gallery) > 0)
            <div class="glass-card rounded-xl p-6 mb-12">
                <h2 class="text-2xl font-bold text-white mb-4">Visual</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($project->portfolio_cover_path)
                    <div class="bg-white/10 rounded-lg overflow-hidden md:col-span-2">
                        <img src="{{ asset('storage/' . ltrim($project->portfolio_cover_path, '/')) }}" alt="{{ $project->title }}" class="w-full max-h-96 object-cover">
                    </div>
                    @endif
                    @foreach($gallery as $visual)
                    <div class="bg-white/10 rounded-lg overflow-hidden {{ $visual['type'] === 'video' ? '' : 'h-64' }}">
                        @if($visual['type'] === 'video')
                        <video src="{{ $visual['url'] }}" controls class="w-full max-h-80 bg-black"></video>
                        @else
                        <img src="{{ $visual['url'] }}" alt="{{ $visual['caption'] ?: $project->title }}" class="w-full h-64 object-cover">
                        @endif
                        @if(!empty($visual['caption']))
                        <div class="px-3 py-2 text-sm text-white/70">{{ $visual['caption'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($project->portfolio_challenge)
            <div class="glass-card rounded-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-white mb-4">Tantangan</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">{{ $project->portfolio_challenge }}</div>
            </div>
            @endif

            @if($project->portfolio_solution)
            <div class="glass-card rounded-xl p-8 mb-8">
                <h2 class="text-3xl font-bold text-white mb-4">Solusi</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">{{ $project->portfolio_solution }}</div>
            </div>
            @endif

            @if($project->portfolio_outcome)
            <div class="glass-card rounded-xl p-8 mb-12">
                <h2 class="text-3xl font-bold text-white mb-4">Hasil</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">{{ $project->portfolio_outcome }}</div>
            </div>
            @elseif($project->description)
            <div class="glass-card rounded-xl p-8 mb-12">
                <h2 class="text-3xl font-bold text-white mb-4">Deskripsi</h2>
                <div class="text-white/80 text-lg leading-relaxed whitespace-pre-line">{{ $project->description }}</div>
            </div>
            @endif

            @if($related->count() > 0)
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-white mb-6">Proyek Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($related as $item)
                    <a href="{{ route('portfolio.show', $item->slug) }}" class="glass-card rounded-xl p-5 hover:bg-white/10 transition">
                        <div class="text-xs text-white/50 mb-2">{{ $item->portfolio_category }} · {{ $item->portfolioYearValue() }}</div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $item->title }}</h3>
                        <p class="text-white/60 text-sm">{{ \Illuminate\Support\Str::limit($item->portfolioExcerptText(), 90) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="text-center">
                <a href="{{ route('portfolio.index') }}" class="text-white/80 hover:text-white transition">← Kembali ke Portfolio</a>
            </div>
        </div>
    </div>
</section>
@endsection
