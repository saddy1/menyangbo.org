@extends('layouts.app')
@section('title', 'मेयाङ्बो वंशावली — सरल तर विस्तृत')
@section('meta_description', 'मेन्याङ्बो कल्याणकारी संघको आधिकारिक वेबसाइट — वंशावली, सदस्य सूची, कार्यक्रम र ग्यालेरी')

@section('content')
<style>
.banner-hero-article {
    height: clamp(180px, 52vw, 320px);
}
@media (min-width: 640px) {
    .banner-hero-article {
        height: clamp(320px, 58vh, 600px);
    }
}
@media (min-width: 1024px) {
    .banner-hero-article {
        height: clamp(480px, 78vh, 880px);
    }
}
</style>

@php
$banners    = $homeSections->get('banner',       collect());
$atAGlance  = $homeSections->get('at_a_glance',  collect());
$timeline   = $homeSections->get('timeline',      collect());
$keyFigures = $homeSections->get('key_figures',   collect());
$notes      = $homeSections->get('notes',         collect());

$colorMap = [
    'emerald' => ['bg'=>'bg-emerald-50','ring'=>'ring-emerald-100','icon'=>'text-emerald-600','badge'=>'ring-emerald-200 text-emerald-700 bg-emerald-50','bar'=>'from-emerald-500 via-blue-500 to-fuchsia-500'],
    'sky'     => ['bg'=>'bg-sky-50',    'ring'=>'ring-sky-100',    'icon'=>'text-sky-600',    'badge'=>'ring-sky-200 text-sky-700 bg-sky-50',             'bar'=>'from-sky-500 via-indigo-500 to-cyan-500'],
    'rose'    => ['bg'=>'bg-rose-50',   'ring'=>'ring-rose-100',   'icon'=>'text-rose-600',   'badge'=>'ring-rose-200 text-rose-700 bg-rose-50',          'bar'=>'from-rose-500 via-orange-500 to-amber-500'],
    'fuchsia' => ['bg'=>'bg-fuchsia-50','ring'=>'ring-fuchsia-100','icon'=>'text-fuchsia-600','badge'=>'ring-fuchsia-200 text-fuchsia-700 bg-fuchsia-50', 'bar'=>'from-fuchsia-500 via-purple-500 to-blue-500'],
    'amber'   => ['bg'=>'bg-amber-50',  'ring'=>'ring-amber-100',  'icon'=>'text-amber-600',  'badge'=>'ring-amber-200 text-amber-700 bg-amber-50',       'bar'=>'from-amber-500 via-lime-500 to-emerald-500'],
    'teal'    => ['bg'=>'bg-teal-50',   'ring'=>'ring-teal-100',   'icon'=>'text-teal-600',   'badge'=>'ring-teal-200 text-teal-700 bg-teal-50',          'bar'=>'from-teal-500 via-cyan-500 to-indigo-500'],
    'blue'    => ['bg'=>'bg-blue-50',   'ring'=>'ring-blue-100',   'icon'=>'text-blue-600',   'badge'=>'ring-blue-200 text-blue-700 bg-blue-50',          'bar'=>'from-blue-500 via-sky-500 to-cyan-500'],
    'indigo'  => ['bg'=>'bg-indigo-50', 'ring'=>'ring-indigo-100', 'icon'=>'text-indigo-600', 'badge'=>'ring-indigo-200 text-indigo-700 bg-indigo-50',    'bar'=>'from-indigo-500 via-blue-500 to-sky-500'],
    'purple'  => ['bg'=>'bg-purple-50', 'ring'=>'ring-purple-100', 'icon'=>'text-purple-600', 'badge'=>'ring-purple-200 text-purple-700 bg-purple-50',    'bar'=>'from-purple-500 via-fuchsia-500 to-rose-500'],
    'orange'  => ['bg'=>'bg-orange-50', 'ring'=>'ring-orange-100', 'icon'=>'text-orange-600', 'badge'=>'ring-orange-200 text-orange-700 bg-orange-50',    'bar'=>'from-orange-500 via-amber-500 to-yellow-500'],
    'default' => ['bg'=>'bg-slate-50',  'ring'=>'ring-slate-200',  'icon'=>'text-slate-600',  'badge'=>'ring-slate-200 text-slate-700 bg-slate-50',       'bar'=>'from-slate-400 via-slate-500 to-slate-600'],
];
@endphp

{{-- ══════════════════════════════════════════════
     1. ADMIN CONTROLLED BANNERS (slideshow)
══════════════════════════════════════════════ --}}
@if($banners->count())
@php $sortedBanners = $banners->sortByDesc('created_at')->values(); @endphp
<section class="mb-8"
    x-data="{
        cur: 0,
        total: {{ $sortedBanners->count() }},
        _t: null,
        init() { if (this.total > 1) this._start(); },
        _start() { this._t = setInterval(() => { this.cur = (this.cur + 1) % this.total; }, 5000); },
        go(i)   { this.cur = i;                           clearInterval(this._t); this._start(); },
        prev()  { this.cur = (this.cur - 1 + this.total) % this.total; clearInterval(this._t); this._start(); },
        next()  { this.cur = (this.cur + 1) % this.total; clearInterval(this._t); this._start(); }
    }">

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm banner-hero-article">

        @foreach($sortedBanners as $i => $banner)
            @php
                $bannerUrl = $banner->link_url;
                $isExternal = $bannerUrl && Str::startsWith($bannerUrl, ['http://', 'https://']);
                $c = $colorMap[$banner->color ?? 'blue'] ?? $colorMap['blue'];
            @endphp
            <div x-show="cur === {{ $i }}"
                 x-transition:enter="transition ease-in-out duration-700"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in-out duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0"
                 @if($i > 0) style="display:none" @endif>

                @if($banner->image_path)
                    <img src="{{ asset($banner->image_path) }}"
                         alt="{{ $banner->title }}"
                         class="h-full w-full object-cover">
                @else
                    <div class="absolute inset-0 bg-gradient-to-br {{ $c['bar'] }}"></div>
                    <div class="absolute inset-0 opacity-20"
                         style="background-image: radial-gradient(circle at 20% 30%, white 0 2px, transparent 3px), radial-gradient(circle at 70% 70%, white 0 2px, transparent 3px); background-size: 44px 44px;"></div>
                @endif

                @if($banner->subtitle || $bannerUrl)
                    <div class="absolute right-3 top-3 flex max-w-[calc(100%-1.5rem)] items-center gap-2 rounded-full bg-white/90 px-2.5 py-2 text-xs shadow-lg ring-1 ring-black/5 backdrop-blur sm:right-4 sm:top-4">
                        @if($banner->subtitle)
                            <span class="truncate font-bold text-slate-700">{{ $banner->subtitle }}</span>
                        @endif
                        @if($bannerUrl)
                            <a href="{{ $bannerUrl }}"
                               @if($isExternal) target="_blank" rel="noopener" @endif
                               class="inline-flex shrink-0 items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 font-bold text-white transition hover:bg-slate-800">
                                {{ $banner->link_label ?: 'Open' }}
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Prev / Next arrows (multi-banner only) --}}
        @if($sortedBanners->count() > 1)
        <button @click="prev()"
                class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-black/30 hover:bg-black/55 text-white flex items-center justify-center backdrop-blur-sm transition-all sm:left-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button @click="next()"
                class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-black/30 hover:bg-black/55 text-white flex items-center justify-center backdrop-blur-sm transition-all sm:right-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        {{-- Dot indicators --}}
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex items-center gap-1.5">
            @foreach($sortedBanners as $i => $banner)
            <button @click="go({{ $i }})"
                    class="rounded-full transition-all duration-300"
                    :class="cur === {{ $i }} ? 'w-5 h-2 bg-white shadow' : 'w-2 h-2 bg-white/50 hover:bg-white/80'">
            </button>
            @endforeach
        </div>
        @endif

    </div>
</section>
@endif


{{-- ══════════════════════════════════════════════
     3. STATISTICS
══════════════════════════════════════════════ --}}
<section class="mb-10">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-center hover:shadow-md transition-shadow">
            <div class="text-3xl font-extrabold text-blue-600 mb-1">{{ number_format($stats['people']) }}</div>
            <div class="text-sm font-semibold text-slate-700">कुल सदस्य</div>
            <div class="text-xs text-slate-400 mt-0.5">Total Members</div>
            <div class="mt-2 flex items-center justify-center gap-2 text-[11px] font-semibold text-slate-500">
                <span class="text-blue-600">{{ number_format($stats['male']) }} Male</span>
                <span class="text-slate-300">|</span>
                <span class="text-pink-600">{{ number_format($stats['female']) }} Female</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-center hover:shadow-md transition-shadow">
            <div class="text-3xl font-extrabold text-emerald-600 mb-1">{{ number_format($stats['generations']) }}</div>
            <div class="text-sm font-semibold text-slate-700">पुस्ता</div>
            <div class="text-xs text-slate-400 mt-0.5">Generations</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-center hover:shadow-md transition-shadow">
            <div class="text-3xl font-extrabold text-amber-600 mb-1">{{ number_format($stats['unions']) }}</div>
            <div class="text-sm font-semibold text-slate-700">परिवार</div>
            <div class="text-xs text-slate-400 mt-0.5">Families</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-center hover:shadow-md transition-shadow">
            <div class="text-3xl font-extrabold text-rose-500 mb-1">{{ number_format($stats['deceased']) }}</div>
            <div class="text-sm font-semibold text-slate-700">दिवंगत</div>
            <div class="text-xs text-slate-400 mt-0.5">Deceased</div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-center">
            <div class="text-xl font-extrabold text-blue-700">{{ number_format($stats['male']) }}</div>
            <div class="text-xs font-semibold text-blue-700/70">पुरुष / Male</div>
        </div>
        <div class="rounded-xl border border-pink-100 bg-pink-50 px-4 py-3 text-center">
            <div class="text-xl font-extrabold text-pink-700">{{ number_format($stats['female']) }}</div>
            <div class="text-xs font-semibold text-pink-700/70">महिला / Female</div>
        </div>
        <div class="rounded-xl border border-purple-100 bg-purple-50 px-4 py-3 text-center">
            <div class="text-xl font-extrabold text-purple-700">{{ number_format($stats['other']) }}</div>
            <div class="text-xs font-semibold text-purple-700/70">अन्य / Other</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center">
            <div class="text-xl font-extrabold text-slate-700">{{ number_format($stats['unknown']) }}</div>
            <div class="text-xs font-semibold text-slate-500">Unknown</div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     4. QUICK ACTIONS
══════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-10">
    <a href="{{ route('tree.index') }}"
       class="group rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col items-center gap-2 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
        <span class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-100 transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </span>
        <span class="font-semibold text-slate-700 text-sm">वंशावली Tree</span>
        <span class="text-[11px] text-slate-400">परिवार वृक्ष हेर्नुहोस्</span>
    </a>
    <a href="{{ route('admin.people.directory') }}"
       class="group rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col items-center gap-2 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
        <span class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-100 transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </span>
        <span class="font-semibold text-slate-700 text-sm">सदस्य सूची</span>
        <span class="text-[11px] text-slate-400">पारिवारिक निर्देशिका</span>
    </a>
    <a href="{{ route('committee.index') }}"
       class="group rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col items-center gap-2 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
        <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:bg-amber-100 transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </span>
        <span class="font-semibold text-slate-700 text-sm">कार्यसमिति</span>
        <span class="text-[11px] text-slate-400">समिति सदस्यहरू</span>
    </a>
    <a href="{{ route('feedback.create') }}"
       class="group rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col items-center gap-2 hover:shadow-lg hover:-translate-y-1 transition-all text-center">
        <span class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600 group-hover:bg-rose-100 transition-colors">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </span>
        <span class="font-semibold text-slate-700 text-sm">सुझाव</span>
        <span class="text-[11px] text-slate-400">मत / प्रतिक्रिया</span>
    </a>
</div>

{{-- ══════════════════════════════════════════════
     5. EVENTS
══════════════════════════════════════════════ --}}
@if($events->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        कार्यक्रम / सूचनाहरू
    </h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($events as $event)
        <article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all flex flex-col">
            @if($event->photo_path)
            <div class="aspect-video bg-slate-100 shrink-0">
                <img src="{{ asset($event->photo_path) }}" alt="{{ $event->title }}" class="h-full w-full object-cover">
            </div>
            @else
            <div class="h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 shrink-0"></div>
            @endif
            <div class="p-4 flex flex-col flex-1">
                <h3 class="font-semibold text-slate-800 leading-snug">{{ $event->title }}</h3>
                <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-1.5">
                    @if($event->event_date)
                    <span class="text-[11px] text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $event->event_date->format('Y M d') }}
                    </span>
                    @endif
                    @if($event->location)
                    <span class="text-[11px] text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $event->location }}
                    </span>
                    @endif
                </div>
                @if($event->description)
                <p class="text-sm text-slate-600 mt-2 leading-relaxed flex-1">{{ Str::limit($event->description, 120) }}</p>
                @endif
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════
     6. GALLERY (from Media model — saves to public/media/)
══════════════════════════════════════════════ --}}
@if($gallery->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        फोटो ग्यालेरी
    </h2>
    <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($gallery as $photo)
        <figure class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="aspect-video bg-slate-100">
                <img src="{{ asset($photo->file_path) }}" alt="{{ $photo->name ?? '' }}" class="h-full w-full object-cover">
            </div>
            @if($photo->category)
            <figcaption class="text-xs text-slate-600 px-3 py-2 leading-snug">{{ $photo->category }}</figcaption>
            @endif
        </figure>
        @endforeach
    </div>
    <div class="mt-4 flex justify-center">
        <a href="{{ route('gallery.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors shadow-sm">
            थप फोटोहरू हेर्नुहोस्
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════
     7. एक नजरमा  (DB-driven)
══════════════════════════════════════════════ --}}
@if($atAGlance->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        एक नजरमा
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($atAGlance as $item)
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">{{ $item->title }}</div>
            <div class="text-sm text-slate-800 leading-snug">{{ $item->body }}</div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════
     8. घटनाक्रम  (DB-driven)
══════════════════════════════════════════════ --}}
@if($timeline->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        मुख्य घटनाक्रम
    </h2>
    <ol class="relative border-s-2 border-slate-200 ps-6 space-y-4">
        @foreach($timeline as $event)
        <li class="relative">
            <span class="absolute -start-[29px] top-3 w-3.5 h-3.5 rounded-full bg-blue-600 ring-4 ring-white"></span>
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4 hover:shadow-md transition-shadow">
                <h3 class="font-semibold text-slate-800 flex flex-wrap items-center gap-2">
                    {{ $event->title }}
                    @if($event->subtitle)
                    <span class="text-[11px] font-medium rounded-full border border-slate-200 px-2 py-0.5 text-slate-500 bg-slate-50">
                        {{ $event->subtitle }}
                    </span>
                    @endif
                </h3>
                @php $bullets = $event->bullets(); @endphp
                @if(count($bullets))
                <ul class="list-disc ms-5 mt-2 text-sm text-slate-600 space-y-0.5">
                    @foreach($bullets as $b)<li>{{ $b }}</li>@endforeach
                </ul>
                @endif
            </div>
        </li>
        @endforeach
    </ol>
</section>
@endif

{{-- ══════════════════════════════════════════════
     9. प्रमुख व्यक्तित्व  (DB-driven)
══════════════════════════════════════════════ --}}
@if($keyFigures->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        प्रमुख व्यक्तित्व
    </h2>
    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
        @foreach($keyFigures as $figure)
        @php $c = $colorMap[$figure->color ?? 'default'] ?? $colorMap['default']; @endphp
        <article class="rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all overflow-hidden">
            <div class="h-1.5 bg-gradient-to-r {{ $c['bar'] }}"></div>
            <div class="px-4 pt-4 pb-3 flex items-start gap-3">
                <span class="shrink-0 grid place-items-center w-10 h-10 rounded-xl {{ $c['bg'] }} ring-1 {{ $c['ring'] }}">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <h3 class="font-semibold text-slate-800 text-sm leading-snug">{{ $figure->title }}</h3>
                    @if($figure->subtitle)
                    <span class="inline-block text-[10px] mt-0.5 px-2 py-0.5 rounded-full ring-1 {{ $c['badge'] }}">{{ $figure->subtitle }}</span>
                    @endif
                </div>
            </div>
            @if($figure->body)
            <p class="text-xs text-slate-600 px-4 pb-4 leading-relaxed">{{ $figure->body }}</p>
            @endif
        </article>
        @endforeach
    </div>
</section>
@endif



{{-- ══════════════════════════════════════════════
     11. नोटहरू  (DB-driven)
══════════════════════════════════════════════ --}}
@if($notes->count())
<section class="mb-10">
    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="inline-block w-1 h-5 bg-blue-600 rounded-full"></span>
        बसोबास/प्रसार र नोटहरू
    </h2>

    @php
        $regularNotes     = $notes->where('subtitle', '!=', 'collapsible')->values();
        $collapsibleNotes = $notes->where('subtitle', 'collapsible')->values();
    @endphp

    @if($regularNotes->count())
    <div class="grid gap-4 md:grid-cols-2 mb-4">
        @foreach($regularNotes as $note)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
            <h3 class="font-semibold text-slate-800 mb-2">{{ $note->title }}</h3>
            @php $bullets = $note->bullets(); @endphp
            @if(count($bullets))
            <ul class="list-disc ms-5 text-sm text-slate-600 space-y-1">
                @foreach($bullets as $b)<li>{{ $b }}</li>@endforeach
            </ul>
            @else
            <p class="text-sm text-slate-600">{{ $note->body }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @foreach($collapsibleNotes as $note)
    <details class="rounded-2xl border border-slate-200 bg-white p-5 open:shadow-sm mb-3 cursor-pointer">
        <summary class="font-semibold text-slate-800 select-none list-none flex items-center justify-between">
            {{ $note->title }}
            <svg class="w-4 h-4 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>
        @php $bullets = $note->bullets(); @endphp
        @if(count($bullets))
        <ul class="list-disc ms-5 mt-3 text-sm text-slate-600 space-y-1">
            @foreach($bullets as $b)<li>{{ $b }}</li>@endforeach
        </ul>
        @else
        <p class="text-sm text-slate-600 mt-3">{{ $note->body }}</p>
        @endif
    </details>
    @endforeach

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white shadow-sm p-4 flex flex-wrap items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-cyan-50 ring-1 ring-cyan-100 grid place-items-center shrink-0">
            <svg class="w-5 h-5 text-cyan-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM8 12h8v2H8v-2zm0 4h8v2H8v-2zm6-9 5 5h-5V7z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-slate-800 text-sm">मुन्‍धुम अनुसार सृष्टिको पहिलो मानव</h3>
            <p class="text-xs text-slate-500 mt-0.5">शिक्षण/सन्दर्भका लागि उपयोगी पूरा कागजात।</p>
        </div>
        <a href="{{ asset('मुन्धुम अनुसार सृष्टिको पहिलो मानव.pdf') }}"
           class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold text-xs hover:bg-emerald-700 transition-colors shadow">
            हेर्नुहोस्
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 3l7 7-7 7v-4H3v-6h11V3z"/></svg>
        </a>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════
     POPUP MODAL (PostEvent show_popup, once per session)
══════════════════════════════════════════════ --}}
@if($popup)
<div id="popupOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" style="display:none!important">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        @if($popup->photo_path)
        <div class="aspect-video bg-slate-100">
            <img src="{{ asset($popup->photo_path) }}" alt="{{ $popup->title }}" class="w-full h-full object-cover">
        </div>
        @else
        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
        @endif
        <div class="p-5">
            <h2 class="font-bold text-slate-800 text-lg leading-snug">{{ $popup->title }}</h2>
            @if($popup->event_date)
            <p class="text-xs text-slate-400 mt-1">{{ $popup->event_date->format('Y M d') }}</p>
            @endif
            @if($popup->description)
            <p class="text-sm text-slate-600 mt-3 leading-relaxed">{{ $popup->description }}</p>
            @endif
            <button onclick="closePopup()" class="mt-5 w-full py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition-colors">
                बन्द गर्नुहोस्
            </button>
        </div>
    </div>
</div>
<script>
(function () {
    var key = 'popup_seen_{{ $popup->id }}';
    if (!sessionStorage.getItem(key)) {
        var el = document.getElementById('popupOverlay');
        if (el) el.style.cssText = 'display:flex!important';
    }
})();
function closePopup() {
    var el = document.getElementById('popupOverlay');
    if (el) el.style.cssText = 'display:none!important';
    sessionStorage.setItem('popup_seen_{{ $popup->id }}', '1');
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closePopup(); });
</script>
@endif

{{-- ═══════════════════════════════════════════════════════
     POPUP BANNER NOTICES — Alpine.js, fits screen, dark green + gold
═══════════════════════════════════════════════════════ --}}
@if(isset($popups) && $popups->count() > 0)
<style>
/* ─── POPUP ───────────────────────────────────────── */
.popup-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: rgba(11,36,21,.92);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.popup-card {
    position: relative;
    width: 100%;
    max-width: 720px;
    max-height: calc(100dvh - 32px);
    max-height: calc(100vh - 32px);
    display: flex;
    flex-direction: column;
    border-radius: 1.5rem;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 32px 80px rgba(0,0,0,0.6);
}
.popup-img-area {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
    background: #fff;
}
.popup-img-area img {
    display: block;
    width: 100%;
    height: auto;
}
.popup-footer {
    flex-shrink: 0;
    background: #0b2415;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.popup-close {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(220,38,38,0.9);
    border: 2px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
    backdrop-filter: blur(4px);
}
.popup-close:hover { background: #b91c1c; transform: scale(1.1); }
.popup-img-area::-webkit-scrollbar { width: 4px; }
.popup-img-area::-webkit-scrollbar-track { background: #f3f4f6; }
.popup-img-area::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
@keyframes popup-progress { from { width: 0 } to { width: 100% } }
.popup-progress-bar { animation: popup-progress 8s linear forwards; }
</style>

<div x-data="{
        open: false,
        currentIndex: 0,
        total: {{ $popups->count() }},
        init() {
            this.$nextTick(() => { this.open = true; });
        },
        next() {
            if (this.currentIndex < this.total - 1) {
                this.currentIndex++;
                this.$nextTick(() => {
                    const el = document.querySelector('[data-imgarea=\'' + this.currentIndex + '\']');
                    if (el) el.scrollTop = 0;
                });
            } else {
                this.close();
            }
        },
        close() {
            this.open = false;
        }
     }"
     x-show="open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="close()"
     class="popup-overlay"
     style="display:none"
     @click.self="close()">

    <div class="popup-card"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Close button --}}
        <button @click="close()" class="popup-close" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Progress bar --}}
        <div class="absolute top-0 left-0 right-0 h-1 z-20 bg-white/20">
            <div class="h-full bg-[#e2a024] popup-progress-bar" :key="currentIndex"></div>
        </div>

        {{-- Slides --}}
        @foreach($popups as $index => $notice)
        <div x-show="currentIndex === {{ $index }}"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="flex flex-col min-h-0 flex-1">

            <div class="popup-img-area" data-imgarea="{{ $index }}">
                @if($notice->link_url)
                <a href="{{ $notice->link_url }}" target="_blank" rel="noopener">
                    <img src="{{ asset($notice->image_path) }}"
                         alt="{{ $notice->title }}"
                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                </a>
                @else
                <img src="{{ asset($notice->image_path) }}"
                     alt="{{ $notice->title }}"
                     loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                @endif
            </div>

            <div class="popup-footer">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-white text-sm truncate mb-1.5">{{ $notice->title }}</h3>
                    <div class="flex gap-1.5 items-center">
                        @foreach($popups as $di => $dp)
                        <div class="h-1 rounded-full transition-all duration-500"
                             :class="currentIndex === {{ $di }}
                                 ? 'w-6 bg-[#e2a024]'
                                 : (currentIndex > {{ $di }} ? 'w-2 bg-white/20' : 'w-2 bg-white/40')"></div>
                        @endforeach
                        <span class="ml-1 text-[10px] text-white/50 font-semibold tracking-widest uppercase">
                            {{ $index + 1 }} / {{ $popups->count() }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    @if($notice->link_url)
                    <a href="{{ $notice->link_url }}" target="_blank" rel="noopener"
                       class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold border border-white/20 flex items-center gap-1.5 transition">
                        Open
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                    <button @click.prevent="next()"
                            class="px-4 py-2 rounded-xl bg-[#e2a024] hover:bg-[#f5c355] text-[#0b2415] text-xs font-bold flex items-center gap-1.5 transition">
                        <span x-text="currentIndex < total - 1 ? 'Next →' : 'Close'"></span>
                    </button>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endif

@endsection
