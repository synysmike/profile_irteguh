@extends('public.layouts.app')

@php
    $appName = \App\Models\Setting::appName();
    $saleTitle = $sale->publicTitle();
    $saleItemLabels = $sale->saleItems->pluck('description');
    $saleDesc = \App\Support\SeoMeta::fromLabels(
        'Detail penjualan '.$sale->invoice_number,
        $saleItemLabels
    );
@endphp

@section('title', $saleTitle.' - '.$appName)

@push('meta')
@include('public.partials.seo-meta', ['seo' => [
    'title' => $saleTitle,
    'description' => $saleDesc,
    'keywords' => collect([$sale->invoice_number, ...$saleItemLabels->take(6)])->filter()->implode(', '),
    'url' => $sale->publicUrl(),
    'type' => 'article',
    'published_time' => optional($sale->sale_date ?? $sale->created_at)->toIso8601String(),
]])
@endpush

@section('content')
<section class="py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <nav class="mb-8 text-white/60 text-sm">
                <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a> /
                <a href="{{ route('sales.public.index') }}" class="hover:text-white transition">Penjualan</a> /
                <span class="text-white">{{ $sale->invoice_number }}</span>
            </nav>

            <div class="glass-card rounded-2xl p-8 md:p-12 mb-12">
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <span class="text-sm text-white/80 bg-white/10 px-4 py-2 rounded-full">Penjualan POS</span>
                    @if($sale->sale_date)
                    <span class="text-sm text-white/60">{{ $sale->sale_date->translatedFormat('d F Y') }}</span>
                    @endif
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">{{ $sale->publicTitle() }}</h1>
                <p class="text-white/70 text-lg mb-2">No. Faktur: {{ $sale->invoice_number }}</p>
                @if($sale->customer?->name)
                <p class="text-white/60">Pelanggan: {{ $sale->customer->name }}</p>
                @endif
            </div>

            @if($sale->publishedReviews->count() > 0)
            @php
                $avgRating = (float) $sale->publishedReviews->avg('rating');
                $avgRounded = (int) round($avgRating);
            @endphp
            <div class="mb-12">
                <div class="glass-card rounded-2xl overflow-hidden">
                    <div class="px-6 py-6 md:px-8 border-b border-white/10 bg-gradient-to-br from-amber-400/10 via-transparent to-sky-400/5">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-amber-200/80 mb-2">Ulasan Klien</p>
                                <h2 class="text-2xl font-bold text-white">Feedback pembelian</h2>
                            </div>
                            <div class="text-right">
                                <div class="text-4xl font-bold text-white tabular-nums">{{ number_format($avgRating, 1) }}</div>
                                <div class="mt-1 flex items-center justify-end gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="{{ $i <= $avgRounded ? '#facc15' : '#facc154d' }}" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="divide-y divide-white/10">
                        @foreach($sale->publishedReviews as $review)
                        <article class="px-6 py-5 md:px-8">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <div class="font-semibold text-white">{{ $review->displayName() }}</div>
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="{{ $i <= (int) $review->rating ? '#facc15' : '#facc154d' }}">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    @endfor
                                </div>
                            </div>
                            @if($review->comment)
                            <p class="text-white/80 leading-relaxed whitespace-pre-wrap">{{ $review->comment }}</p>
                            @endif
                            @if($review->submitted_at)
                            <div class="text-xs text-white/40 mt-2">{{ $review->submitted_at->format('d/m/Y') }}</div>
                            @endif
                        </article>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <div class="glass-card rounded-xl p-6 md:p-8 mb-12">
                <h2 class="text-2xl font-bold text-white mb-4">Detail Item</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-white/50 border-b border-white/10">
                                <th class="py-2 pr-3 font-medium">Item</th>
                                <th class="py-2 px-3 font-medium text-right">Qty</th>
                                <th class="py-2 px-3 font-medium text-right">Harga</th>
                                <th class="py-2 pl-3 font-medium text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($sale->saleItems as $item)
                            <tr class="text-white/80">
                                <td class="py-3 pr-3">
                                    <div class="text-white font-medium">{{ $item->description }}</div>
                                    @if($item->notes)
                                    <div class="text-xs text-white/45 mt-0.5">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right tabular-nums">{{ number_format((int) $item->quantity, 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right tabular-nums">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                <td class="py-3 pl-3 text-right tabular-nums text-white">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 pt-4 border-t border-white/10 space-y-1 text-sm">
                    <div class="flex justify-between text-white/70">
                        <span>Subtotal</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if((float) $sale->ppn_amount > 0)
                    <div class="flex justify-between text-white/70">
                        <span>{{ $sale->tax_name ?: 'Pajak' }}</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $sale->ppn_amount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-white text-lg font-semibold pt-1">
                        <span>Total</span>
                        <span class="tabular-nums">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</span>
                    </div>
                </div>
                @if($sale->notes)
                <p class="mt-4 text-sm text-white/60 whitespace-pre-wrap">{{ $sale->notes }}</p>
                @endif
            </div>

            @if($related->count() > 0)
            <div class="mb-12">
                <h2 class="text-3xl font-bold text-white mb-6">Penjualan Lainnya</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($related as $item)
                    <a href="{{ route('sales.public.show', $item) }}" class="glass-card rounded-xl p-5 hover:bg-white/10 transition flex flex-col min-w-0">
                        <div class="text-xs text-white/50 mb-2">{{ $item->sale_date?->format('d M Y') }}</div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $item->publicTitle() }}</h3>
                        <p class="text-white/60 text-sm mb-3">{{ $item->invoice_number }}</p>
                        <div class="mt-auto text-sm text-white/80 tabular-nums">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="text-center">
                <a href="{{ route('sales.public.index') }}" class="text-white/80 hover:text-white transition">← Kembali ke Penjualan</a>
            </div>
        </div>
    </div>
</section>
@endsection
