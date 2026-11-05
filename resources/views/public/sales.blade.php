@extends('public.layouts.app')

@section('title', 'Penjualan - Ir Teguh Solution')

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-white mb-6">Histori Penjualan</h1>
                <p class="text-xl text-white/80">Transaksi POS yang dipublikasikan dari kasir kami</p>
            </div>

            <div class="glass-card rounded-xl p-6 mb-12">
                <form method="GET" action="{{ route('sales.public.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-white/80 text-sm mb-2">Tahun</label>
                        <select name="year" class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white backdrop-blur-md">
                            <option value="">Semua Tahun</option>
                            @foreach($years as $y)
                            <option value="{{ $y }}" {{ (string) request('year') === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="submit" class="w-full md:w-auto px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            @if($sales->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($sales as $sale)
                <a href="{{ route('sales.public.show', $sale) }}"
                   class="glass-card rounded-xl p-6 hover:scale-105 transition transform group flex flex-col overflow-hidden min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="text-xs text-white/50 bg-white/10 px-3 py-1 rounded-full">POS</span>
                        <span class="text-xs text-white/50">{{ $sale->sale_date?->format('d M Y') }}</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-200 transition break-words">
                        {{ $sale->publicTitle() }}
                    </h3>
                    <p class="text-sm text-white/55 mb-3">{{ $sale->invoice_number }}</p>
                    @if($sale->saleItems->count())
                    <ul class="text-sm text-white/70 space-y-1 mb-4 flex-1">
                        @foreach($sale->saleItems->take(3) as $item)
                        <li class="truncate">• {{ $item->description }}</li>
                        @endforeach
                        @if($sale->saleItems->count() > 3)
                        <li class="text-white/45">+{{ $sale->saleItems->count() - 3 }} item lainnya</li>
                        @endif
                    </ul>
                    @endif
                    <div class="mt-auto flex items-center justify-between gap-2 pt-3 border-t border-white/10">
                        <span class="text-white font-semibold tabular-nums">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</span>
                        @if($sale->publishedReviews->count() > 0)
                        @php $avg = round((float) $sale->publishedReviews->avg('rating'), 1); @endphp
                        <span class="text-xs text-yellow-300 tabular-nums">★ {{ number_format($avg, 1) }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="glass-card rounded-xl p-12 text-center">
                <p class="text-white/70 text-lg">Belum ada penjualan publik yang ditampilkan.</p>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
