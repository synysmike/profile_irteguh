@extends('public.layouts.app')

@section('title', 'Berita - ' . \App\Models\Setting::appName())

@section('content')
<section class="py-16 md:py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-12">
            <p class="text-sm uppercase tracking-[0.2em] text-purple-200 mb-3">Update Terbaru</p>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Berita</h1>
            <p class="text-lg text-white/75">Artikel, insight, dan kabar dari {{ \App\Models\Setting::appName() }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            @forelse($news as $item)
            <a href="{{ route('news.show', $item->slug) }}" class="glass-card rounded-2xl overflow-hidden hover:bg-white/10 transition group flex flex-col">
                <div class="aspect-[16/10] bg-white/10 overflow-hidden">
                    @if($item->coverUrl())
                    <img src="{{ $item->coverUrl() }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-white/40 text-sm">Tanpa cover</div>
                    @endif
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-center gap-3 text-xs text-white/50 mb-3">
                        <span>{{ optional($item->published_at ?? $item->created_at)->format('d M Y') }}</span>
                        <span>·</span>
                        <span>{{ number_format($item->views_count) }} pembaca</span>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2 group-hover:text-purple-200 transition">{{ $item->title }}</h2>
                    <p class="text-sm text-white/70 mb-4 flex-1">{{ Str::limit($item->excerpt ?: strip_tags($item->content), 120) }}</p>
                    <span class="text-purple-300 text-sm font-medium">Baca selengkapnya →</span>
                </div>
            </a>
            @empty
            <div class="md:col-span-2 lg:col-span-3 glass-card rounded-2xl p-10 text-center text-white/70">
                Belum ada berita yang dipublikasikan.
            </div>
            @endforelse
        </div>

        @if($news->hasPages())
        <div class="mt-10 flex justify-center">
            {{ $news->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
