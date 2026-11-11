@extends('public.layouts.app')

@section('title', $newsItem->title . ' - ' . \App\Models\Setting::appName())

@push('meta')
@include('public.partials.seo-meta', ['seo' => [
    'title' => $newsItem->title,
    'description' => $newsItem->excerpt ?: strip_tags($newsItem->content),
    'url' => $newsItem->publicUrl(),
    'image' => $newsItem->shareImageUrlForPreview(),
    'type' => 'article',
    'published_time' => optional($newsItem->published_at ?? $newsItem->created_at)->toIso8601String(),
    'author' => $newsItem->author_name,
]])
@endpush

@section('content')
<div class="news-reading-page">
    <div class="news-progress" id="news-progress" aria-hidden="true"><span></span></div>

    <div class="news-reading-wrap">
        <nav class="news-reading-nav">
            <a href="{{ route('news.index') }}" class="news-back-link">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Semua Berita
            </a>
        </nav>

        <article class="news-reading-shell">
            @if($newsItem->coverUrl())
            <figure class="news-reading-cover">
                <img src="{{ $newsItem->coverUrl() }}" alt="{{ $newsItem->title }}">
            </figure>
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
                        <span>{{ number_format($newsItem->views_count) }} pembaca</span>
                    </div>

                    <h1 class="news-reading-title">{{ $newsItem->title }}</h1>

                    @if($newsItem->excerpt)
                    <p class="news-reading-dek">{{ $newsItem->excerpt }}</p>
                    @endif
                </header>

                <div class="news-content">
                    {!! $newsItem->content !!}
                </div>
            </div>

            <footer class="news-reading-footer">
                <div class="news-reading-stats">
                    <strong>{{ number_format($newsItem->views_count) }}</strong>
                    <span>pembaca</span>
                </div>
                <div class="news-share-row">
                    <span class="news-share-label">Bagikan</span>
                    <a href="{{ $shareUrls['threads'] }}" target="_blank" rel="noopener" class="share-btn">Threads</a>
                    <a href="{{ $shareUrls['twitter'] }}" target="_blank" rel="noopener" class="share-btn">X</a>
                    <a href="{{ $shareUrls['facebook'] }}" target="_blank" rel="noopener" class="share-btn">Facebook</a>
                    <a href="{{ $shareUrls['whatsapp'] }}" target="_blank" rel="noopener" class="share-btn share-btn--wa">WhatsApp</a>
                </div>
            </footer>
        </article>

        @if($related->count())
        <section class="news-related">
            <div class="news-related-head">
                <h2>Berita Lainnya</h2>
            </div>
            <div class="news-related-grid">
                @foreach($related as $rel)
                <a href="{{ route('news.show', $rel->slug) }}" class="news-related-card">
                    <div class="news-related-media">
                        @if($rel->coverUrl())
                        <img src="{{ $rel->coverUrl() }}" alt="">
                        @else
                        <div class="news-related-placeholder">Berita</div>
                        @endif
                    </div>
                    <div class="news-related-copy">
                        <span class="news-related-meta">{{ number_format($rel->views_count) }} pembaca</span>
                        <h3>{{ $rel->title }}</h3>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
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
        const total = Math.max(article.offsetHeight - window.innerHeight, 1);
        const scrolled = Math.min(Math.max(-rect.top, 0), total);
        bar.style.width = ((scrolled / total) * 100) + '%';
    }

    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
    updateProgress();
});
</script>
@endpush
