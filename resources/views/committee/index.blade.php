@extends('layouts.app')
@section('title', 'कार्यसमिति — मेन्याङ्बो')
@section('meta_description', 'मेन्याङ्बो कल्याणकारी संघको कार्यसमितिका पदाधिकारीहरूको विवरण।')

@section('content')

{{-- Page Header --}}
<div class="mb-8">
    <div class="rounded-2xl overflow-hidden"
         style="background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);">
        <div class="px-6 py-8 sm:px-10 sm:py-10 relative overflow-hidden">
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #93c5fd, transparent)"></div>
            <div class="absolute -bottom-6 -left-6 w-28 h-28 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #bfdbfe, transparent)"></div>
            <div class="relative">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-1">कार्यसमिति</h1>
                <p class="text-blue-200 text-sm">मेन्याङ्बो कल्याणकारी संघका पदाधिकारीहरू</p>
            </div>
        </div>
    </div>
</div>

{{-- Tab switcher --}}
<div x-data="{ tab: 'working' }" class="space-y-8">

    <div class="flex gap-2 border-b border-slate-200">
        <button @click="tab = 'working'"
                :class="tab === 'working'
                    ? 'border-b-2 border-blue-600 text-blue-700 font-bold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2.5 text-sm font-medium transition-colors -mb-px">
            कार्यकारी समिति
            @if($working->isNotEmpty())
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">
                    {{ $working->flatten()->count() }}
                </span>
            @endif
        </button>
        <button @click="tab = 'past'"
                :class="tab === 'past'
                    ? 'border-b-2 border-slate-600 text-slate-700 font-bold'
                    : 'text-slate-500 hover:text-slate-700'"
                class="px-5 py-2.5 text-sm font-medium transition-colors -mb-px">
            पूर्व समिति
            @if($past->isNotEmpty())
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                    {{ $past->flatten()->count() }}
                </span>
            @endif
        </button>
    </div>

    {{-- ═══ Working Committees ═══ --}}
    <div x-show="tab === 'working'" x-cloak>
        @forelse($working as $type => $committees)
            @foreach($committees as $committee)
            <div class="mb-10">
                {{-- Committee Title --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-800 text-base sm:text-lg">
                            {{ $committee->name }}
                        </span>
                        @if($committee->term_label)
                        <span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100 font-bold">
                            {{ $committee->term_label }}
                        </span>
                        @endif
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                            {{ $committee->type_label }}
                        </span>
                    </div>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                @if($committee->members->isEmpty())
                    <p class="text-center text-sm text-slate-400 py-6">सदस्यहरू थपिएका छैनन्।</p>
                @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($committee->members as $member)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col items-center text-center
                                hover:shadow-md hover:-translate-y-0.5 transition-all group">
                        {{-- Photo --}}
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-slate-100 ring-2 ring-blue-100 mb-3 shrink-0">
                            @if($member->photo_path)
                                <img src="{{ asset($member->photo_path) }}"
                                     alt="{{ $member->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center"
                                     style="background: linear-gradient(135deg,#1d4ed8,#1e3a8a)">
                                    <svg class="w-9 h-9 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Name & Position --}}
                        <h3 class="font-bold text-slate-800 text-sm leading-tight">{{ $member->name }}</h3>
                        @if($member->position)
                            <span class="mt-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                {{ $member->position }}
                            </span>
                        @endif

                        {{-- Contact info --}}
                        @if($member->contact || $member->email)
                        <div class="mt-2.5 w-full space-y-1 border-t border-slate-100 pt-2.5">
                            @if($member->contact)
                            <a href="tel:{{ $member->contact }}"
                               class="flex items-center justify-center gap-1.5 text-[11px] text-slate-500 hover:text-blue-600 transition-colors">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $member->contact }}
                            </a>
                            @endif
                            @if($member->email)
                            <a href="mailto:{{ $member->email }}"
                               class="flex items-center justify-center gap-1.5 text-[11px] text-slate-500 hover:text-blue-600 transition-colors truncate max-w-full">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">{{ $member->email }}</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        @empty
        <div class="py-16 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm">हाल कुनै कार्यकारी समिति छैन।</p>
        </div>
        @endforelse
    </div>

    {{-- ═══ Past Committees ═══ --}}
    <div x-show="tab === 'past'" x-cloak>
        @forelse($past as $type => $committees)
            @foreach($committees as $committee)
            <div class="mb-8" x-data="{ open: false }">

                {{-- Collapsible header --}}
                <button @click="open = !open"
                        class="w-full flex items-center gap-3 mb-0 group text-left">
                    <div class="h-px flex-1 bg-slate-200 group-hover:bg-slate-300 transition-colors"></div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-600 text-sm sm:text-base">
                            {{ $committee->name }}
                        </span>
                        @if($committee->term_label)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 font-semibold">
                            {{ $committee->term_label }}
                        </span>
                        @endif
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-50 text-slate-400">
                            {{ $committee->type_label }}
                        </span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform"
                             :class="open ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div class="h-px flex-1 bg-slate-200 group-hover:bg-slate-300 transition-colors"></div>
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-5">
                    @if($committee->members->isEmpty())
                        <p class="text-center text-sm text-slate-400 py-4">सदस्यहरू थपिएका छैनन्।</p>
                    @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @foreach($committee->members as $member)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col items-center text-center opacity-80 hover:opacity-100 transition-opacity">
                            <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 ring-2 ring-slate-200 mb-2.5 shrink-0">
                                @if($member->photo_path)
                                    <img src="{{ asset($member->photo_path) }}" alt="{{ $member->name }}" class="w-full h-full object-cover grayscale">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-200">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold text-slate-700 text-sm leading-tight">{{ $member->name }}</h3>
                            @if($member->position)
                                <span class="mt-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                    {{ $member->position }}
                                </span>
                            @endif
                            @if($member->contact || $member->email)
                            <div class="mt-2 w-full space-y-1 border-t border-slate-100 pt-2">
                                @if($member->contact)
                                <div class="flex items-center justify-center gap-1 text-[10px] text-slate-400">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $member->contact }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @empty
        <div class="py-16 text-center text-slate-400">
            <p class="text-sm">कुनै पूर्व समिति छैन।</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
