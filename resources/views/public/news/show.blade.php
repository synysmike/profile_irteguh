@extends('public.layouts.app')

@section('title', $newsItem->title . ' - ' . \App\Models\Setting::appName())

@section('content')
<article class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-purple-200 hover:text-white text-sm mb-8 transition">
                ← Semua Berita
            </a>

            <header class="mb-8">
                <div class="flex flex-wrap items-center gap-3 text-sm text-white/55 mb-4">
                    <span>{{ optional($newsItem->published_at ?? $newsItem->created_at)->format('d F Y') }}</span>
                    @if($newsItem->author_name)
                    <span>·</span>
                    <span>{{ $newsItem->author_name }}</span>
                    @endif
                    <span>·</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ number_format($newsItem->views_count) }} pembaca
                    </span>
                </div>
                <h1 class="text-3xl md:text-5xl font-bold text-white leading-tight mb-4">{{ $newsItem->title }}</h1>
                @if($newsItem->excerpt)
                <p class="text-lg text-white/75">{{ $newsItem->excerpt }}</p>
                @endif
            </header>

            @if($newsItem->coverUrl())
            <div class="rounded-2xl overflow-hidden mb-8 border border-white/10 shadow-xl">
                <img src="{{ $newsItem->coverUrl() }}" alt="{{ $newsItem->title }}" class="w-full max-h-[480px] object-cover">
            </div>
            @endif

            <div class="news-content glass-card rounded-2xl p-6 md:p-10 mb-8">
                {!! $newsItem->content !!}
            </div>

            {{-- Share + reader count --}}
            <div class="glass-card rounded-2xl p-5 md:p-6 mb-12">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-white/80 text-sm">
                        <span class="font-semibold text-white">{{ number_format($newsItem->views_count) }}</span> orang telah membaca berita ini
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs uppercase tracking-wider text-white/50 mr-1">Bagikan</span>
                        <a href="{{ $shareUrls['threads'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke Threads">
                            Threads
                        </a>
                        <a href="{{ $shareUrls['twitter'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke X / Twitter">
                            X
                        </a>
                        <a href="{{ $shareUrls['facebook'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke Facebook">
                            Facebook
                        </a>
                        <a href="{{ $shareUrls['whatsapp'] }}" target="_blank" rel="noopener" class="share-btn share-btn--wa" title="Share ke WhatsApp">
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            @if($related->count())
            <section>
                <h2 class="text-2xl font-bold text-white mb-6">Berita Lainnya</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($related as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="glass-card rounded-xl p-4 hover:bg-white/10 transition">
                        <div class="text-xs text-white/50 mb-2">{{ number_format($rel->views_count) }} pembaca</div>
                        <div class="text-white font-semibold leading-snug">{{ $rel->title }}</div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>
</article>
@endsection
