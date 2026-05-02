@extends('admin.layout')

@section('title', 'Nepali Calendar Years')

@section('content')
@php
  $monthNames = [
    1 => 'बैशाख',
    2 => 'जेठ',
    3 => 'असार',
    4 => 'श्रावण',
    5 => 'भदौ',
    6 => 'आश्विन',
    7 => 'कार्तिक',
    8 => 'मंसिर',
    9 => 'पुष',
    10 => 'माघ',
    11 => 'फागुन',
    12 => 'चैत्र',
  ];
@endphp

<div class="py-6 max-w-6xl mx-auto">
  <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Nepali Calendar Years</h1>
      <p class="text-sm text-slate-500 mt-1">Set the number of days in each Nepali month. Public calendar uses these values.</p>
    </div>
    <a href="{{ route('calendar.index') }}" target="_blank" class="rounded-xl border px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
      View Calendar
    </a>
  </div>

  <form method="POST" action="{{ route('admin.calendar-years.store') }}" class="mb-6 rounded-2xl border bg-white p-5 shadow-sm">
    @csrf
    <div class="mb-4">
      <label class="block text-sm font-bold text-slate-700 mb-1">BS Year</label>
      <input type="number" name="year" value="{{ old('year') }}" placeholder="2085"
        class="w-full sm:w-48 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      @foreach($monthNames as $index => $name)
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">{{ $name }}</label>
          <input type="number" name="month_days[]" min="28" max="33" value="{{ old('month_days.'.($index - 1), 30) }}"
            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
        </div>
      @endforeach
    </div>
    <div class="mt-4">
      <button class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-bold text-white hover:bg-blue-700">Save Year</button>
    </div>
  </form>

  <div class="space-y-4">
    @forelse($years as $year)
      <form method="POST" action="{{ route('admin.calendar-years.update', $year) }}" class="rounded-2xl border bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')
        <div class="mb-4 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <label class="text-sm font-bold text-slate-700">Year</label>
            <input type="number" name="year" value="{{ $year->year }}"
              class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
          </div>
          <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800">Update</button>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
          @foreach($monthNames as $index => $name)
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1">{{ $name }}</label>
              <input type="number" name="month_days[]" min="28" max="33" value="{{ $year->month_days[$index - 1] ?? 30 }}"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
            </div>
          @endforeach
        </div>
      </form>

      <form method="POST" action="{{ route('admin.calendar-years.destroy', $year) }}" class="-mt-3 text-right" onsubmit="return confirm('Delete BS {{ $year->year }} calendar year?')">
        @csrf
        @method('DELETE')
        <button class="text-xs font-semibold text-rose-600 hover:underline">Delete {{ $year->year }}</button>
      </form>
    @empty
      <div class="rounded-2xl border border-dashed bg-white p-8 text-center text-slate-400">No calendar years added yet.</div>
    @endforelse
  </div>
</div>
@endsection
