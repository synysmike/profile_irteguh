@extends('public.layouts.app')

@php
    $appName = \App\Models\Setting::appName();
    $portfolioShowDesc = \App\Support\SeoMeta::limitDescription($project->portfolioExcerptText());
    $portfolioShowKeywords = collect([
        $project->portfolio_category,
        ...($project->portfolio_tags ?? []),
        $project->title,
    ])->filter()->unique()->implode(', ');
@endphp

@section('title', $project->title.' - '.$appName)

@push('meta')
@include('public.partials.seo-meta', ['seo' => [
    'title' => $project->title,
    'description' => $portfolioShowDesc,
    'keywords' => $portfolioShowKeywords,
    'url' => route('portfolio.show', $project->slug),
    'image' => $project->portfolioCoverUrl(),
    'type' => 'article',
    'published_time' => optional($project->updated_at ?? $project->created_at)->toIso8601String(),
]])
@endpush

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

            @if($project->publishedReviews->count() > 0)
            @php
                $avgRating = (float) ($project->averageRating() ?? 0);
                $avgRounded = (int) round($avgRating);
                $reviewCount = $project->publishedReviews->count();
            @endphp
            <div class="mb-12">
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="relative px-6 py-8 md:px-10 md:py-10 border-b border-white/10 bg-gradient-to-br from-amber-400/10 via-transparent to-sky-400/5">
                        <div class="absolute -right-6 -top-8 text-[9rem] leading-none text-amber-300/10 select-none pointer-events-none" aria-hidden="true">“</div>
                        <div class="relative grid grid-cols-1 md:grid-cols-[auto_1fr] gap-8 items-center">
                            <div class="flex flex-col items-center md:items-start text-center md:text-left">
                                <div class="text-6xl md:text-7xl font-bold text-white tracking-tight tabular-nums leading-none">
                                    {{ number_format($avgRating, 1) }}
                                </div>
                                <div class="mt-3 flex items-center gap-1" aria-label="Rating {{ number_format($avgRating, 1) }} dari 5">
                                    @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="{{ $i <= $avgRounded ? '#facc15' : '#facc154d' }}" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @endfor
                                </div>
                                <p class="mt-2 text-sm text-white/55">dari 5 bintang</p>
                            </div>
                            <div class="md:border-l md:border-white/10 md:pl-8">
                                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/80 mb-2">Ulasan Klien</p>
                                <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">Dipercaya klien proyek ini</h2>
                                <p class="text-white/70 max-w-xl leading-relaxed">
                                    {{ $reviewCount }} ulasan terverifikasi dari klien yang terlibat langsung dalam pengerjaan proyek.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-white/10">
                        @foreach($project->publishedReviews as $review)
                        @php
                            $name = $review->displayName();
                            $initials = collect(preg_split('/\s+/u', trim($name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');
                            $rating = (int) $review->rating;
                        @endphp
                        <article class="group px-6 py-7 md:px-10 md:py-8 hover:bg-white/[0.03] transition-colors duration-300">
                            <div class="flex gap-4 md:gap-5">
                                <div class="shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-amber-300/30 to-sky-400/20 border border-white/15 flex items-center justify-center text-sm font-semibold text-white tracking-wide">
                                    {{ $initials ?: 'K' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                        <div>
                                            <div class="font-semibold text-white text-lg leading-tight">{{ $name }}</div>
                                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-white/45">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-sm bg-emerald-400/80"></span>
                                                    Klien terverifikasi
                                                </span>
                                                @if($review->submitted_at)
                                                <time datetime="{{ $review->submitted_at->toDateString() }}">{{ $review->submitted_at->translatedFormat('d M Y') }}</time>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5" aria-label="{{ $rating }} dari 5 bintang">
                                            @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="{{ $i <= $rating ? '#facc15' : '#facc154d' }}" aria-hidden="true">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            @endfor
                                        </div>
                                    </div>

                                    @if($review->comment)
                                    <blockquote class="relative text-white/85 text-[1.05rem] leading-relaxed whitespace-pre-wrap pl-4 border-l-2 border-amber-300/40 group-hover:border-amber-300/70 transition-colors duration-300">
                                        {{ $review->comment }}
                                    </blockquote>
                                    @endif

                                    @if($review->media->count())
                                    <div class="mt-5 flex gap-3 overflow-x-auto pb-1 snap-x snap-mandatory">
                                        @foreach($review->media as $media)
                                        <div class="snap-start shrink-0 w-40 sm:w-48 rounded-xl overflow-hidden bg-black/40 ring-1 ring-white/10">
                                            @if($media->isVideo())
                                            <video src="{{ $media->url() }}" controls class="w-full h-28 object-cover bg-black"></video>
                                            @else
                                            <img src="{{ $media->url() }}" alt="Lampiran ulasan {{ $name }}" class="w-full h-28 object-cover transition duration-500 group-hover:scale-[1.03]">
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @php $gallery = $project->portfolioGalleryItems(); @endphp
            @if($project->portfolio_cover_path || count($gallery) > 0)
            <div class="glass-card rounded-xl p-6 mb-12">
                <h2 class="text-2xl font-bold text-white mb-4">Foto / Video Progres</h2>
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
                    <a href="{{ route('portfolio.show', $item->slug) }}" class="glass-card rounded-xl p-5 hover:bg-white/10 transition flex flex-col min-w-0">
                        <div class="text-xs text-white/50 mb-2">{{ $item->portfolio_category }} · {{ $item->portfolioYearValue() }}</div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $item->title }}</h3>
                        <p class="text-white/60 text-sm mb-3">{{ \Illuminate\Support\Str::limit($item->portfolioExcerptText(), 90) }}</p>
                        @if(($item->published_reviews_count ?? 0) > 0)
                        @php $relAvg = (float) ($item->published_reviews_avg_rating ?? 0); $relRound = (int) round($relAvg); @endphp
                        <div class="mt-auto pt-3 border-t border-white/10 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-0.5" aria-hidden="true">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="{{ $i <= $relRound ? '#facc15' : '#facc154d' }}">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            <div class="text-xs text-white/55 tabular-nums">
                                {{ number_format($relAvg, 1) }} · {{ $item->published_reviews_count }} ulasan
                            </div>
                        </div>
                        @endif
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
