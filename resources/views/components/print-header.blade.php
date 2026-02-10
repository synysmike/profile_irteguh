{{-- Print header: show when printing. Include in pages that need a printed header. --}}
<div class="print-header hidden print:block border-b border-gray-300 pb-3 mb-4">
    <div class="flex items-center gap-4">
        @php $siteLogoUrl = \App\Models\Setting::logoPath(); @endphp
        @if($siteLogoUrl)
        <span class="site-logo-wrap site-logo-wrap--print">
            <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name', 'Ir Teguh Solution') }}" class="site-logo" width="160" height="40">
        </span>
        @endif
        <div>
            <h1 class="text-lg font-bold text-gray-900">{{ config('app.name', 'Ir Teguh Solution') }}</h1>
            @if(isset($printSubtitle))
            <p class="text-sm text-gray-600">{{ $printSubtitle }}</p>
            @endif
        </div>
    </div>
</div>
