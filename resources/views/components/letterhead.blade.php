{{-- Shared letterhead for printed documents. Logo always uses site logo upload. --}}
@php
    $letterheadLogo = \App\Models\Setting::logoPath();
    $letterheadHtml = \App\Models\Setting::letterheadHtml();
    $letterheadName = \App\Models\Setting::appName();
@endphp
<div class="letterhead" style="position:relative; text-align:center; min-height:64px;">
    @if($letterheadLogo)
    <div class="letterhead-logo" style="position:absolute; left:0; top:0;">
        <img src="{{ $letterheadLogo }}" alt="{{ $letterheadName }}" style="max-height:85px; width:auto; display:block;">
    </div>
    @endif
    <div class="letterhead-body" style="text-align:center;">
        <div class="letterhead-name" style="font-size:24px; font-weight:700; color:#111; margin:0 0 6px 0; text-align:center; line-height:1.2;">{{ $letterheadName }}</div>
        @if($letterheadHtml !== '')
        <div class="letterhead-content" style="font-size:16px; color:#4b5563; line-height:1.5; text-align:center;">
            {!! $letterheadHtml !!}
        </div>
        @endif
    </div>
</div>
