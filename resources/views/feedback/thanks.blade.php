@extends('layouts.app')
@section('title', 'धन्यवाद — मेन्याङ्बो')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8">
    <div class="w-full max-w-sm text-center">

        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="h-1.5" style="background:linear-gradient(90deg,#1d4ed8,#3b82f6,#1d4ed8)"></div>

            <div class="px-8 py-10">
                {{-- Icon --}}
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6"
                     style="background:linear-gradient(135deg,#1d4ed8,#1e3a8a)">
                    <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-3xl font-extrabold text-slate-900 mb-3">धन्यवाद!</h1>
                <p class="text-slate-500 leading-relaxed text-sm mb-8">
                    तपाईंको सुझाव सफलतापूर्वक पठाइयो।<br>
                    तपाईंको प्रतिक्रिया हाम्रो लागि अमूल्य छ।
                </p>

                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white shadow-md
                          hover:shadow-lg hover:-translate-y-0.5 transition-all"
                   style="background:linear-gradient(135deg,#1d4ed8,#1e3a8a)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    मुख्य पृष्ठमा फर्कनुहोस्
                </a>
            </div>

            <div class="h-1" style="background:linear-gradient(90deg,#3b82f6,#1d4ed8,#3b82f6)"></div>
        </div>

        <p class="text-xs text-slate-400 mt-5">हामी तपाईंको प्रतिक्रियालाई गम्भीरतापूर्वक लिन्छौं।</p>
    </div>
</div>
@endsection
