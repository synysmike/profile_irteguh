@extends('public.layouts.app')

@section('title', 'Portfolio - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">Katalog Portfolio</h1>
                <p class="text-xl text-white/80">Jelajahi studi kasus dan proyek kami</p>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-xl p-6 mb-12">
                <form method="GET" action="{{ route('portfolio.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-white/80 text-sm mb-2">Kategori</label>
                        <select name="category" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white backdrop-blur-md">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-white/80 text-sm mb-2">Tahun</label>
                        <select name="year" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white backdrop-blur-md">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Portfolio Grid -->
            @if($caseStudies->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($caseStudies as $caseStudy)
                <a href="{{ route('case-study.show', $caseStudy->slug) }}" 
                   class="glass-card rounded-xl p-6 hover:scale-105 transition transform group">
                    @if($caseStudy->visuals && count($caseStudy->visuals) > 0)
                    <div class="w-full h-48 bg-white/10 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
                        <span class="text-white/50">Pratinjau Gambar</span>
                    </div>
                    @endif
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs text-white/50 bg-white/10 px-3 py-1 rounded-full">{{ $caseStudy->category }}</span>
                            <span class="text-xs text-white/50">{{ $caseStudy->year }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-200 transition">
                            {{ $caseStudy->title }}
                        </h3>
                        <p class="text-white/70 text-sm mb-4">
                            {{ Str::limit($caseStudy->excerpt ?? $caseStudy->challenge, 120) }}
                        </p>
                    </div>
                    @if($caseStudy->tags && count($caseStudy->tags) > 0)
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach(array_slice($caseStudy->tags, 0, 3) as $tag)
                        <span class="text-xs text-white/60 bg-white/5 px-2 py-1 rounded">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="text-white/70 text-sm group-hover:text-white transition">
                        Baca studi kasus →
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="glass-card rounded-xl p-12 text-center">
                <p class="text-white/70 text-lg">Tidak ada studi kasus yang ditemukan sesuai filter Anda.</p>
                <a href="{{ route('portfolio.index') }}" class="text-purple-300 hover:text-purple-200 transition mt-4 inline-block">
                    Hapus filter
                </a>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
