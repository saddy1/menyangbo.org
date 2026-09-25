@extends('layouts.app')
@section('title', $notice->title . \App\Support\FrontendLocale::text(' — सूचना'))
@section('meta_description', Str::limit(strip_tags($notice->body ?: $notice->title), 150))

@section('content')

{{-- ── PDF notice: full-width, no sidebar ── --}}
@if($notice->attachment_type === 'pdf' && $notice->attachment_path)

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="h-1.5" style="background:linear-gradient(90deg,#f59e0b,#ef4444)"></div>

    {{-- Header --}}
    <div class="px-4 py-4 sm:px-6 flex flex-wrap items-start justify-between gap-3 border-b border-slate-100">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
                <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}" class="font-semibold text-blue-600 hover:underline">{{ \App\Support\FrontendLocale::text('सूचनाहरू') }}</a>
                <span class="text-slate-300">/</span>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 font-bold text-amber-700">{{ $notice->display_date_time }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold leading-tight text-slate-900">{{ $notice->title }}</h1>
        </div>
        <a href="{{ $notice->attachment_url }}" target="_blank" rel="noopener"
           class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-700 text-white text-sm font-semibold transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            {{ \App\Support\FrontendLocale::text('New Tab मा खोल्नुहोस्') }}
        </a>
    </div>

    {{-- Inline PDF (full-width, scrollable) --}}
    <iframe src="{{ $notice->attachment_url }}#toolbar=1&view=FitH"
            style="width:100%; height:100vh; min-height:600px; border:none; display:block;"
            title="{{ $notice->attachment_name }}">
        <div class="p-8 text-center">
            <p class="text-slate-600 mb-4">{{ \App\Support\FrontendLocale::text('तपाईंको ब्राउजरले PDF देखाउन सक्दैन।') }}</p>
            <a href="{{ $notice->attachment_url }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold text-sm">
                {{ \App\Support\FrontendLocale::text('PDF खोल्नुहोस्') }}
            </a>
        </div>
    </iframe>

    @if($notice->body)
    <div class="px-4 sm:px-6 py-5 border-t border-slate-100">
        <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed whitespace-pre-line text-justify hyphens-auto">
            {{ $notice->body }}
        </div>
    </div>
    @endif

    @if($notice->link)
    <div class="px-4 sm:px-6 pb-5">
        <a href="{{ $notice->link }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
            {{ $notice->link_text ?? \App\Support\FrontendLocale::text('थप जानकारी हेर्नुहोस्') }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>
    @endif
</div>

{{-- ── Image / text notice: two-column with sidebar ── --}}
@else

<div class="grid gap-5 lg:grid-cols-[1fr_320px]">
    <article class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="h-1.5" style="background:linear-gradient(90deg,#f59e0b,#ef4444)"></div>

        <div class="px-4 py-4 sm:px-6 sm:py-5">
            <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
                <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}" class="font-semibold text-blue-600 hover:underline">{{ \App\Support\FrontendLocale::text('सूचनाहरू') }}</a>
                <span class="text-slate-300">/</span>
                <span class="rounded-full bg-amber-50 px-2.5 py-1 font-bold text-amber-700">
                    {{ $notice->display_date_time }}
                </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight text-slate-900">{{ $notice->title }}</h1>

            @if($notice->attachment_type === 'image' && $notice->attachment_path)
                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    <div x-data="{ zoom: false }">
                        <div class="relative group/img cursor-zoom-in" @click="zoom = true">
                            <img src="{{ $notice->attachment_url }}"
                                 alt="{{ $notice->title }}"
                                 class="w-full max-h-[82vh] object-contain bg-white">
                            <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/10 transition-colors flex items-end justify-end p-3">
                                <span class="opacity-0 group-hover/img:opacity-100 transition-opacity bg-black/60 text-white text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5 backdrop-blur-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zm0 0l2 2"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11h6M11 8v6"/>
                                    </svg>
                                    {{ \App\Support\FrontendLocale::text('Zoom') }}
                                </span>
                            </div>
                        </div>
                        <div x-show="zoom" x-cloak
                             @click="zoom = false"
                             @keydown.escape.window="zoom = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-[300] flex items-center justify-center bg-black/92 p-4 cursor-zoom-out">
                            <img src="{{ $notice->attachment_url }}"
                                 alt="{{ $notice->title }}"
                                 @click.stop
                                 class="max-w-full max-h-[92vh] object-contain rounded-xl shadow-2xl cursor-default">
                            <button @click.stop="zoom = false"
                                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($notice->body)
                <div class="prose prose-slate max-w-none mt-5 text-slate-700 leading-relaxed whitespace-pre-line text-justify hyphens-auto">
                    {{ $notice->body }}
                </div>
            @endif

            @if($notice->link)
                <div class="mt-6">
                    <a href="{{ $notice->link }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        {{ $notice->link_text ?? \App\Support\FrontendLocale::text('थप जानकारी हेर्नुहोस्') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </article>

    <aside class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm h-fit">
        <h2 class="mb-3 text-sm font-bold text-slate-800">{{ \App\Support\FrontendLocale::text('अरू सूचनाहरू') }}</h2>
        @forelse($moreNotices as $item)
            <a href="{{ \App\Support\FrontendLocale::route('notices.show', $item) }}" class="block rounded-xl px-3 py-2 hover:bg-slate-50">
                <div class="text-[11px] font-bold text-amber-600">{{ $item->display_date_time }}</div>
                <div class="mt-0.5 line-clamp-2 text-sm font-semibold text-slate-700">{{ $item->title }}</div>
            </a>
        @empty
            <p class="text-sm text-slate-400">{{ \App\Support\FrontendLocale::text('अरू सूचना छैन।') }}</p>
        @endforelse
    </aside>
</div>

@endif
@endsection
