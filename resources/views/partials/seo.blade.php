@php
    $seoTitle = trim(html_entity_decode(strip_tags($__env->yieldContent('title', \App\Support\FrontendLocale::text('वंशावली'))), ENT_QUOTES, 'UTF-8'));
    $seoTitle .= ' — ' . \App\Support\FrontendLocale::text(\App\Support\Seo::NAME);
    $seoDescription = \App\Support\Seo::description($__env->yieldContent('meta_description')) ?: \App\Support\FrontendLocale::text(\App\Support\Seo::DESCRIPTION);
    $seoCanonical = \App\Support\Seo::canonical(request());
    $seoImage = \App\Support\Seo::url('/menyanbo_logo.png');
    $seoHome = \App\Support\Seo::url();
    $seoNoindex = \App\Support\Seo::noindex(request());
    $seoGraph = [
        ['@type' => 'Organization', '@id' => $seoHome . '#organization', 'name' => \App\Support\FrontendLocale::text(\App\Support\Seo::NAME), 'url' => $seoHome, 'logo' => $seoImage],
        ['@type' => 'WebSite', '@id' => $seoHome . '#website', 'name' => \App\Support\FrontendLocale::text(\App\Support\Seo::NAME), 'url' => $seoHome, 'inLanguage' => \App\Support\FrontendLocale::locale(), 'publisher' => ['@id' => $seoHome . '#organization']],
        ['@type' => 'WebPage', '@id' => $seoCanonical . '#webpage', 'url' => $seoCanonical, 'name' => $seoTitle, 'description' => $seoDescription, 'inLanguage' => \App\Support\FrontendLocale::locale(), 'isPartOf' => ['@id' => $seoHome . '#website']],
    ];
@endphp
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $seoDescription }}">
<meta name="robots" content="{{ $seoNoindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">
<link rel="canonical" href="{{ $seoCanonical }}">
@foreach(['ne', 'en'] as $alternateLocale)
<link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ \App\Support\Seo::canonical(request(), $alternateLocale) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ \App\Support\Seo::canonical(request(), 'ne') }}">
<link rel="icon" type="image/png" href="{{ asset('menyanbo_logo.png') }}">
<meta property="og:locale" content="{{ \App\Support\FrontendLocale::locale() === 'en' ? 'en_US' : 'ne_NP' }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ \App\Support\FrontendLocale::text(\App\Support\Seo::NAME) }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoCanonical }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:alt" content="{{ \App\Support\FrontendLocale::text(\App\Support\Seo::NAME) }} {{ \App\Support\FrontendLocale::text('Logo') }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<meta name="twitter:image:alt" content="{{ \App\Support\FrontendLocale::text(\App\Support\Seo::NAME) }} {{ \App\Support\FrontendLocale::text('Logo') }}">
@if(!$seoNoindex)
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $seoGraph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
