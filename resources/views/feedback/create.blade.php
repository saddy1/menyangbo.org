@extends('layouts.app')
@section('title', 'सुझाव पठाउनुहोस् — मेन्याङ्बो')

@section('content')
<div class="min-h-[70vh] flex items-start justify-center py-6">
<div class="w-full max-w-xl">

    {{-- Card --}}
    <div class="bg-white rounded-3xl shadow-lg border border-blue-100 overflow-hidden">

        {{-- Header --}}
        <div class="relative px-8 py-8 overflow-hidden"
             style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #1e3a8a 100%);">
            {{-- Decorative circles --}}
            <div class="absolute -top-6 -right-6 w-32 h-32 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #93c5fd, transparent)"></div>
            <div class="absolute -bottom-4 -left-4 w-20 h-20 rounded-full opacity-15"
                 style="background: radial-gradient(circle, #bfdbfe, transparent)"></div>

            <div class="relative flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-md bg-white/15 backdrop-blur-sm ring-1 ring-white/25">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-white leading-tight">सुझाव पठाउनुहोस्</h1>
                    <p class="text-sm text-blue-200 mt-0.5">तपाईंको मत र विचार हाम्रो लागि महत्त्वपूर्ण छ।</p>
                </div>
            </div>
        </div>

        {{-- Divider stripe --}}
        <div class="h-1" style="background: linear-gradient(90deg,#1d4ed8,#3b82f6,#60a5fa,#3b82f6,#1d4ed8)"></div>

        {{-- Form --}}
        <form method="POST" action="{{ route('feedback.store') }}"
              class="px-7 py-7 space-y-5"
              x-data="{ chars: {{ strlen(old('description','')) }}, sending: false }"
              @submit="sending = true">
            @csrf
            <input type="text" name="hp_field" class="hidden" autocomplete="off">

            @if($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700 flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- Name --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                    नाम
                    <span class="normal-case font-normal tracking-normal text-slate-400 ml-1">(ऐच्छिक)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-blue-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="तपाईंको पूरा नाम"
                           class="w-full border border-slate-200 bg-blue-50/30 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-800
                                  placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-500
                                  hover:border-blue-300 transition-all">
                </div>
            </div>

            {{-- Email + Phone --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                        इमेल <span class="normal-case font-normal tracking-normal text-slate-400 ml-1">(यदि छ)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-blue-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="name@example.com"
                               class="w-full border border-slate-200 bg-blue-50/30 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-800
                                      placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-500
                                      hover:border-blue-300 transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">
                        फोन <span class="normal-case font-normal tracking-normal text-slate-400 ml-1">(यदि छ)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-blue-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                        <input type="text" name="contact" value="{{ old('contact') }}"
                               placeholder="98XXXXXXXX"
                               class="w-full border border-slate-200 bg-blue-50/30 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-800
                                      placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-500
                                      hover:border-blue-300 transition-all">
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">
                        विवरण <span class="text-rose-500 ml-0.5">*</span>
                    </label>
                    <span class="text-[11px] font-semibold tabular-nums"
                          :class="chars > 4800 ? 'text-rose-500' : 'text-slate-400'"
                          x-text="chars + ' / 5000'"></span>
                </div>
                <textarea name="description" rows="5" required maxlength="5000"
                          placeholder="तपाईंको सुझाव, विचार वा प्रश्न यहाँ लेख्नुहोस्…"
                          @input="chars = $event.target.value.length"
                          class="w-full border border-slate-200 bg-blue-50/30 rounded-xl px-4 py-3 text-sm text-slate-800
                                 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-blue-500
                                 hover:border-blue-300 transition-all resize-y leading-relaxed"
                          >{{ old('description') }}</textarea>
                @error('description')
                <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-1">
                <button type="submit"
                        :disabled="sending"
                        class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl font-bold text-sm text-white
                               transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0
                               disabled:opacity-70 disabled:cursor-not-allowed disabled:translate-y-0"
                        style="background: linear-gradient(135deg,#1d4ed8,#1e3a8a);">
                    <span x-show="!sending" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        सुझाव पठाउनुहोस्
                    </span>
                    <span x-show="sending" class="flex items-center gap-2" style="display:none">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        पठाउँदैछ…
                    </span>
                </button>
            </div>

            <p class="text-center text-[11px] text-slate-400">
                तपाईंको जानकारी सुरक्षित राखिनेछ र कसैसँग साझा गरिने छैन।
            </p>
        </form>
    </div>

</div>
</div>
@endsection
