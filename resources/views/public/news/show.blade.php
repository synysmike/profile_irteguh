@extends('public.layouts.app')

@section('title', $newsItem->title . ' - ' . \App\Models\Setting::appName())

@php
    $ogTitle = $newsItem->title;
    $ogDescription = \Illuminate\Support\Str::limit(
        $newsItem->excerpt ?: strip_tags($newsItem->content),
        160
    );
    $ogUrl = $newsItem->publicUrl();
    $ogImage = $newsItem->shareImageUrlForPreview();
    $ogImageType = $newsItem->shareImageMimeType();
    $ogImageIsHttps = $ogImage && str_starts_with($ogImage, 'https://');
@endphp

@push('meta')
<meta name="description" content="{{ $ogDescription }}">
<link rel="canonical" href="{{ $ogUrl }}">
<meta property="og:type" content="article">
<meta property="og:site_name" content="{{ \App\Models\Setting::appName() }}">
<meta property="og:locale" content="id_ID">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $ogUrl }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:url" content="{{ $ogImage }}">
@if($ogImageIsHttps)
<meta property="og:image:secure_url" content="{{ $ogImage }}">
@endif
@if($ogImageType)
<meta property="og:image:type" content="{{ $ogImageType }}">
@endif
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogTitle }}">
<meta itemprop="image" content="{{ $ogImage }}">
@endif
<meta property="article:published_time" content="{{ optional($newsItem->published_at ?? $newsItem->created_at)->toIso8601String() }}">
@if($newsItem->author_name)
<meta property="article:author" content="{{ $newsItem->author_name }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
<meta name="twitter:image:alt" content="{{ $ogTitle }}">
@endif
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
