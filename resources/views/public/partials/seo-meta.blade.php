@php
    /** @var array $seo */
    $seo = \App\Support\SeoMeta::make($seo ?? []);
@endphp
<meta name="description" content="{{ $seo['description'] }}">
@if(!empty($seo['keywords']))
<meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['url'] }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:locale" content="{{ $seo['locale'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['url'] }}">
@if(!empty($seo['image']))
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:image:url" content="{{ $seo['image'] }}">
@if($seo['image_is_https'])
<meta property="og:image:secure_url" content="{{ $seo['image'] }}">
@endif
@if(!empty($seo['image_type']))
<meta property="og:image:type" content="{{ $seo['image_type'] }}">
@endif
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $seo['title'] }}">
<meta itemprop="image" content="{{ $seo['image'] }}">
@endif
@if(!empty($seo['published_time']))
<meta property="article:published_time" content="{{ $seo['published_time'] }}">
@endif
@if(!empty($seo['author']))
<meta property="article:author" content="{{ $seo['author'] }}">
@endif
<meta name="twitter:card" content="{{ !empty($seo['image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if(!empty($seo['image']))
<meta name="twitter:image" content="{{ $seo['image'] }}">
<meta name="twitter:image:alt" content="{{ $seo['title'] }}">
@endif
