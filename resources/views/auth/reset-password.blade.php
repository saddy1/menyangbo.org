@extends('layouts.app')
@section('title', 'नयाँ पासवर्ड')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-8">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
      <h1 class="text-2xl font-bold text-center text-slate-800 mb-6">नयाँ पासवर्ड सेट गर्नुहोस्</h1>

      @if($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">इमेल</label>
          <input type="email" name="email" value="{{ $email ?? old('email') }}" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">नयाँ पासवर्ड</label>
          <input type="password" name="password" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
            placeholder="कम्तिमा ८ अक्षर">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">पासवर्ड पुनः लेख्नुहोस्</label>
          <input type="password" name="password_confirmation" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
        </div>
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
          पासवर्ड परिवर्तन गर्नुहोस्
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
