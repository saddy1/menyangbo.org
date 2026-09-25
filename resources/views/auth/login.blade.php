@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('लगइन'))

@section('content')
@php
  $returnTo = request()->query('next', session('url.intended', url()->previous()));
@endphp
<div class="min-h-[70vh] flex items-center justify-center py-8">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
      <h1 class="text-2xl font-bold text-center text-slate-800 mb-2">{{ \App\Support\FrontendLocale::text('लगइन गर्नुहोस्') }}</h1>
      <p class="text-sm text-center text-slate-500 mb-6">{{ \App\Support\FrontendLocale::text('अनुरोध पठाउन लगइन आवश्यक छ') }}</p>

      @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ \App\Support\FrontendLocale::route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="next" value="{{ $returnTo }}">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('इमेल') }}</label>
          <input type="email" name="email" value="{{ old('email') }}" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
            placeholder="your@email.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('पासवर्ड') }}</label>
          <input type="password" name="password" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
            placeholder="••••••••">
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            <span class="text-slate-600">{{ \App\Support\FrontendLocale::text('सम्झनुहोस्') }}</span>
          </label>
          <a href="{{ \App\Support\FrontendLocale::route('password.request') }}" class="text-blue-600 hover:underline">{{ \App\Support\FrontendLocale::text('पासवर्ड बिर्सनु भयो?') }}</a>
        </div>
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
          {{ \App\Support\FrontendLocale::text('लगइन गर्नुहोस्') }}
        </button>
      </form>

      <div class="my-5 flex items-center gap-3">
        <div class="flex-1 h-px bg-slate-200"></div>
        <span class="text-xs text-slate-400">{{ \App\Support\FrontendLocale::text('अथवा') }}</span>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <a href="{{ \App\Support\FrontendLocale::route('auth.google', ['next' => $returnTo]) }}"
        class="flex items-center justify-center gap-3 w-full border border-slate-300 hover:bg-slate-50 rounded-lg py-2.5 text-sm font-medium transition">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        {{ \App\Support\FrontendLocale::text('Google मार्फत लगइन') }}
      </a>

      <p class="mt-6 text-center text-sm text-slate-600">
        {{ \App\Support\FrontendLocale::text('खाता छैन?') }}
        <a href="{{ \App\Support\FrontendLocale::route('register') }}" class="text-blue-600 font-medium hover:underline">{{ \App\Support\FrontendLocale::text('दर्ता गर्नुहोस्') }}</a>
      </p>
    </div>
  </div>
</div>
@endsection
