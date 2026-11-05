@extends('public.layouts.app')

@section('title', 'Portfolio - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">Katalog Portfolio</h1>
                <p class="text-xl text-white/80">Jelajahi proyek yang kami kerjakan</p>
            </div>

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

            @if($projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($projects as $project)
                @php
                    $cardDescription = \Illuminate\Support\Str::limit(
                        preg_replace('/\s+/u', ' ', trim(strip_tags($project->portfolioExcerptText()))) ?: '',
                        1000
                    );
                @endphp
                <a href="{{ route('portfolio.show', $project->slug) }}"
                   class="glass-card rounded-xl p-6 hover:scale-105 transition transform group flex flex-col overflow-hidden min-w-0">
                    <div class="w-full h-48 bg-white/10 rounded-lg mb-4 flex items-center justify-center overflow-hidden shrink-0">
                        @if($project->portfolioCoverUrl())
                        <img src="{{ $project->portfolioCoverUrl() }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                        @else
                        <span class="text-white/50">Pratinjau Proyek</span>
                        @endif
                    </div>
                    <div class="mb-4 min-w-0 flex-1">
                        <div class="mb-2">
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $project->statusBadgeClass() }}">
                                {{ $project->statusLabel() }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            @if($project->portfolio_category)
                            <span class="text-xs text-white/50 bg-white/10 px-3 py-1 rounded-full">{{ $project->portfolio_category }}</span>
                            @endif
                            @if($project->portfolioYearValue())
                            <span class="text-xs text-white/50">{{ $project->portfolioYearValue() }}</span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-200 transition break-words">
                            {{ $project->title }}
                        </h3>
                        @if($cardDescription !== '')
                        <p class="text-white/70 text-sm leading-relaxed break-words overflow-hidden [display:-webkit-box] [-webkit-box-orient:vertical] [-webkit-line-clamp:6]">
                            {{ $cardDescription }}
                        </p>
                        @endif
                    </div>
                    @if($project->portfolio_tags && count($project->portfolio_tags) > 0)
                    <div class="flex flex-wrap gap-2 mb-4 min-w-0">
                        @foreach(array_slice($project->portfolio_tags, 0, 3) as $tag)
                        <span class="text-xs text-white/60 bg-white/5 px-2 py-1 rounded truncate max-w-full">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="text-white/70 text-sm group-hover:text-white transition mt-auto">
                        Lihat detail proyek →
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="glass-card rounded-xl p-12 text-center">
                <p class="text-white/70 text-lg">Belum ada proyek publik yang sesuai filter.</p>
                <a href="{{ route('portfolio.index') }}" class="text-purple-300 hover:text-purple-200 transition mt-4 inline-block">
                    Hapus filter
                </a>
            </div>
            @endif

            @if(!empty($githubTeamRepos))
            <div class="mt-14 pt-10 border-t border-white/10">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white">Project GitHub Tim</h2>
                    <p class="text-sm text-white/60 mt-1">Repo publik per tim</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($githubTeamRepos as $group)
                    @php
                        $team = $group['team'];
                        $repos = $group['repos'];
                        $error = $group['error'];
                        $shownRepos = array_slice($repos, 0, 5);
                        $moreCount = max(0, count($repos) - count($shownRepos));
                    @endphp
                    <section class="glass-card rounded-xl p-4 flex flex-col min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-semibold text-white truncate">{{ $team->name }}</h3>
                                <a href="{{ $team->normalizedGithubUrl() }}" target="_blank" rel="noopener"
                                   class="text-xs text-white/50 hover:text-white transition truncate block">
                                    {{ $team->githubLogin() }}
                                </a>
                            </div>
                            @if($team->activeContributors->isNotEmpty())
                            <div class="flex -space-x-1.5 shrink-0" title="{{ $team->activeContributors->pluck('name')->join(', ') }}">
                                @foreach($team->activeContributors->take(4) as $member)
                                <img src="{{ $member->image_url }}"
                                     alt="{{ $member->name }}"
                                     class="w-6 h-6 rounded-full object-cover ring-1 ring-black/40"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&size=48&background=312e81&color=e0e7ff'">
                                @endforeach
                                @if($team->activeContributors->count() > 4)
                                <span class="w-6 h-6 rounded-full bg-white/10 text-[10px] text-white/70 flex items-center justify-center ring-1 ring-black/40">+{{ $team->activeContributors->count() - 4 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>

                        @if($error && count($repos) === 0)
                        <p class="text-xs text-white/50">{{ $error }}</p>
                        @elseif(count($repos) === 0)
                        <p class="text-xs text-white/50">Belum ada repo publik.</p>
                        @else
                        <ul class="space-y-1.5 flex-1">
                            @foreach($shownRepos as $repo)
                            <li>
                                <a href="{{ $repo['html_url'] }}" target="_blank" rel="noopener"
                                   class="flex items-center justify-between gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-white/5 transition min-w-0">
                                    <span class="text-white/85 truncate">{{ $repo['name'] }}</span>
                                    <span class="text-[11px] text-white/40 shrink-0 tabular-nums">
                                        @if(!empty($repo['language'])){{ $repo['language'] }} · @endif★{{ $repo['stargazers_count'] }}
                                    </span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @if($moreCount > 0)
                        <a href="{{ $team->normalizedGithubUrl() }}?tab=repositories" target="_blank" rel="noopener"
                           class="mt-2 text-xs text-white/50 hover:text-white transition">
                            +{{ $moreCount }} repo lainnya
                        </a>
                        @endif
                        @endif
                    </section>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
