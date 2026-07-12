@extends('public.layouts.app')

@section('title', $newsItem->title . ' - ' . \App\Models\Setting::appName())

@section('content')
<div class="news-reading-page">
    <div class="news-progress" id="news-progress" aria-hidden="true"><span></span></div>

    <article class="py-10 md:py-14">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto mb-6">
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-purple-200/90 hover:text-white text-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Semua Berita
                </a>
            </div>

            <div class="news-reading-shell max-w-3xl mx-auto overflow-hidden">
                @if($newsItem->coverUrl())
                <div class="news-reading-cover">
                    <img src="{{ $newsItem->coverUrl() }}" alt="{{ $newsItem->title }}">
                </div>
                @endif

                <div class="news-reading-body">
                    <header class="news-reading-header">
                        <div class="news-reading-meta">
                            <time datetime="{{ optional($newsItem->published_at ?? $newsItem->created_at)->toDateString() }}">
                                {{ optional($newsItem->published_at ?? $newsItem->created_at)->translatedFormat('d F Y') }}
                            </time>
                            @if($newsItem->author_name)
                            <span class="news-meta-dot" aria-hidden="true"></span>
                            <span>{{ $newsItem->author_name }}</span>
                            @endif
                            <span class="news-meta-dot" aria-hidden="true"></span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($newsItem->views_count) }} pembaca
                            </span>
                        </div>

                        <h1 class="news-reading-title">{{ $newsItem->title }}</h1>

                        @if($newsItem->excerpt)
                        <p class="news-reading-dek">{{ $newsItem->excerpt }}</p>
                        @endif
                    </header>

                    <div class="news-content">
                        {!! $newsItem->content !!}
                    </div>

                    <footer class="news-reading-footer">
                        <div class="news-reading-stats">
                            <strong>{{ number_format($newsItem->views_count) }}</strong> orang telah membaca berita ini
                        </div>
                        <div class="news-share-row">
                            <span class="news-share-label">Bagikan</span>
                            <a href="{{ $shareUrls['threads'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke Threads">Threads</a>
                            <a href="{{ $shareUrls['twitter'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke X / Twitter">X</a>
                            <a href="{{ $shareUrls['facebook'] }}" target="_blank" rel="noopener" class="share-btn" title="Share ke Facebook">Facebook</a>
                            <a href="{{ $shareUrls['whatsapp'] }}" target="_blank" rel="noopener" class="share-btn share-btn--wa" title="Share ke WhatsApp">WhatsApp</a>
                        </div>
                    </footer>
                </div>
            </div>

            @if($related->count())
            <section class="max-w-3xl mx-auto mt-12 md:mt-16">
                <h2 class="text-xl md:text-2xl font-bold text-white mb-5">Berita Lainnya</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($related as $rel)
                    <a href="{{ route('news.show', $rel->slug) }}" class="news-related-card">
                        @if($rel->coverUrl())
                        <img src="{{ $rel->coverUrl() }}" alt="" class="news-related-thumb">
                        @endif
                        <div class="news-related-copy">
                            <div class="text-xs text-white/50 mb-1.5">{{ number_format($rel->views_count) }} pembaca</div>
                            <div class="text-white font-semibold leading-snug text-sm md:text-[0.95rem]">{{ $rel->title }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bar = document.querySelector('#news-progress span');
    const article = document.querySelector('.news-reading-shell');
    if (!bar || !article) return;

    function updateProgress() {
        const rect = article.getBoundingClientRect();
        const total = article.offsetHeight - window.innerHeight;
        const scrolled = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
        const pct = total > 0 ? (scrolled / total) * 100 : 0;
        bar.style.width = pct + '%';
    }

    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
    updateProgress();
});
</script>
@endpush
