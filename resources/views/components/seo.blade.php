@props(['seo'])

@php
    /** @var \App\Support\Seo\Seo $seo */
    $canonical = $seo->resolvedCanonical();
    $image = $seo->resolvedImage();
    $twitter = config('portfolio.seo.twitter_handle');
@endphp

<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<meta name="robots" content="{{ $seo->resolvedRobots() }}">
<meta name="author" content="{{ config('portfolio.identity.name') }}">
<link rel="canonical" href="{{ $canonical }}">
@if ($verification = config('portfolio.seo.google_site_verification'))
    <meta name="google-site-verification" content="{{ $verification }}">
@endif

<meta property="og:site_name" content="{{ config('portfolio.identity.name') }}">
<meta property="og:locale" content="{{ config('portfolio.seo.locale') }}">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:title" content="{{ $seo->fullTitle() }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:alt" content="{{ $seo->resolvedImageAlt() }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@foreach ($seo->properties as $property => $content)
    <meta property="{{ $property }}" content="{{ $content }}">
@endforeach
@if ($seo->type === 'profile')
    <meta property="profile:first_name" content="{{ \Illuminate\Support\Str::before(config('portfolio.identity.name'), ' ') }}">
    <meta property="profile:last_name" content="{{ \Illuminate\Support\Str::after(config('portfolio.identity.name'), ' ') }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->description }}">
<meta name="twitter:image" content="{{ $image }}">
<meta name="twitter:image:alt" content="{{ $seo->resolvedImageAlt() }}">
@if ($twitter)
    <meta name="twitter:site" content="{{ $twitter }}">
    <meta name="twitter:creator" content="{{ $twitter }}">
@endif

@if ($seo->schema)
    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! \App\Support\Seo\JsonLd::graph($seo->schema) !!}</script>
@endif
