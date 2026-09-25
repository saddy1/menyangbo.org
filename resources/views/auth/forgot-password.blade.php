@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('पासवर्ड बिर्सनु भयो'))

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
      <h1 class="text-2xl font-bold text-center text-slate-800 mb-2">{{ \App\Support\FrontendLocale::text('पासवर्ड रिसेट') }}</h1>
      <p class="text-sm text-center text-slate-500 mb-6">{{ \App\Support\FrontendLocale::text('तपाईंको इमेल दिनुहोस् — हामी रिसेट लिङ्क पठाउछौं') }}</p>

      @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ \App\Support\FrontendLocale::route('password.email') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('इमेल') }}</label>
          <input type="email" name="email" value="{{ old('email') }}" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
            placeholder="your@email.com">
        </div>
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
          {{ \App\Support\FrontendLocale::text('रिसेट लिङ्क पठाउनुहोस्') }}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ \App\Support\FrontendLocale::route('login') }}" class="text-blue-600 hover:underline">{{ \App\Support\FrontendLocale::text('← लगइनमा फर्कनुहोस्') }}</a>
      </p>
    </div>
  </div>
</div>
@endsection
