@extends('layouts.app')
@section('title','Sujhav')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white border rounded-xl">
  <h1 class="text-xl font-bold mb-4">सुझाव पठाउनुहोस्</h1>

  <form method="POST" action="{{ route('feedback.store') }}" class="space-y-4">
    @csrf

    {{-- Honeypot (hidden) --}}
    <input type="text" name="hp_field" class="hidden" autocomplete="off">

    <div>
      <label class="block text-sm mb-1">नाम</label>
      <input name="name" value="{{ old('name') }}" class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm mb-1">इमेल (यदि छ)</label>
      <input name="email" type="email" value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm mb-1">सम्पर्क/फोन (यदि छ)</label>
      <input name="contact" value="{{ old('contact') }}" class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="block text-sm mb-1">विवरण *</label>
      <textarea name="description" rows="5" required class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
      @error('description') <div class="text-rose-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center justify-end">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">पठाउनुहोस्</button>
    </div>
  </form>
</div>
@endsection
