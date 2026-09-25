@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('इमेल प्रमाणीकरण'))

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8 text-center">
      <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </div>
      <h1 class="text-xl font-bold text-slate-800 mb-2">{{ \App\Support\FrontendLocale::text('इमेल प्रमाणीकरण गर्नुहोस्') }}</h1>
      <p class="text-sm text-slate-600 mb-6">
        {{ \App\Support\FrontendLocale::text('हामीले तपाईंको इमेल') }} <strong>{{ auth()->user()->email }}</strong> {{ \App\Support\FrontendLocale::text('मा एउटा प्रमाणीकरण लिङ्क पठाएका छौं। कृपया आफ्नो इनबक्स जाँच गर्नुहोस्।') }}
      </p>

      @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">{{ session('success') }}</div>
      @endif

      <form method="POST" action="{{ \App\Support\FrontendLocale::route('verification.resend') }}">
        @csrf
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm mb-4">
          {{ \App\Support\FrontendLocale::text('पुनः इमेल पठाउनुहोस्') }}
        </button>
      </form>

      <form method="POST" action="{{ \App\Support\FrontendLocale::route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 underline">{{ \App\Support\FrontendLocale::text('लगआउट') }}</button>
      </form>
    </div>
  </div>
</div>
@endsection
