@extends('layouts.app')
@section('title', 'सूचनाहरू — मेन्याङ्बो कल्याणकारी संघ')
@section('meta_description', 'मेन्याङ्बो कल्याणकारी संघका सबै सूचनाहरू')

@section('content')
<div class="mb-5 flex items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
            <a href="{{ route('home') }}" class="hover:text-blue-600">गृहपृष्ठ</a>
            <span>/</span>
            <span class="text-slate-600 font-medium">सूचनाहरू</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900">सूचनाहरू</h1>
    </div>
    <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ $notices->count() }}</span>
</div>

@if($notices->isEmpty())
<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white py-12 text-center">
    <div class="text-4xl mb-3 text-slate-300">🔔</div>
    <p class="font-semibold text-slate-500">अहिले कुनै सूचना छैन।</p>
</div>
@else
<div class="grid gap-3">
    @foreach($notices as $i => $notice)
    <a href="{{ route('notices.show', $notice) }}"
       class="group rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-200 transition overflow-hidden">
        <div class="flex items-stretch">
            <div class="w-1.5 shrink-0" style="background:linear-gradient(180deg,#f59e0b,#ef4444)"></div>
            <div class="w-12 shrink-0 flex items-center justify-center bg-slate-50 border-r border-slate-100">
                <span class="text-sm font-black text-slate-300 group-hover:text-amber-500">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="min-w-0 flex-1 px-4 py-3">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                        {{ $notice->display_date_time }}
                    </span>
                    <span class="text-[11px] font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition">View notice →</span>
                </div>
                <h2 class="font-bold text-slate-800 text-base leading-snug">{{ $notice->title }}</h2>
                @if($notice->attachment_type === 'image')
                    <div x-data="{ zoom: false }" class="mt-2">
                        <div class="relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50 cursor-zoom-in group/img"
                             @click.stop.prevent="zoom = true">
                            <img src="{{ $notice->attachment_url }}" alt="{{ $notice->title }}"
                                 class="h-32 w-full object-cover transition-transform duration-300 group-hover/img:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/20 transition-colors flex items-center justify-center">
                                <svg class="w-7 h-7 text-white opacity-0 group-hover/img:opacity-100 transition-opacity drop-shadow-lg"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0zm0 0l2 2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 11h6M11 8v6"/>
                                </svg>
                            </div>
                        </div>
                        {{-- Lightbox --}}
                        <div x-show="zoom" x-cloak
                             @click.stop="zoom = false"
                             @keydown.escape.window="zoom = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-[300] flex items-center justify-center bg-black/90 p-4 cursor-zoom-out">
                            <img src="{{ $notice->attachment_url }}" alt="{{ $notice->title }}"
                                 @click.stop
                                 class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl cursor-default">
                            <button @click.stop="zoom = false"
                                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @elseif($notice->attachment_path)
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5z"/>
                        </svg>
                        PDF — View inside
                    </div>
                @endif
                @if($notice->body)
                    <p class="mt-1 text-sm text-slate-500 leading-relaxed line-clamp-2 text-justify hyphens-auto">{{ $notice->body }}</p>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif
@endsection
