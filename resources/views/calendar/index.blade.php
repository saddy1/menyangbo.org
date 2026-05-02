@extends('layouts.app')

@section('title', 'पात्रो')
@section('meta_description', 'नेपाली र अंग्रेजी मिति र समय')

@section('content')
@php
    $weekdays = ['आइत', 'सोम', 'मंगल', 'बुध', 'बिही', 'शुक्र', 'शनि'];
@endphp

<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <aside class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-2">Today</p>
        <h1 class="text-2xl font-extrabold text-slate-900">{{ $today['weekday'] }}</h1>

        <div class="mt-5 rounded-2xl bg-blue-700 p-5 text-white">
            <div class="text-sm text-blue-100">नेपाली मिति</div>
            <div class="mt-1 text-3xl font-black">{{ $today['bs']['label'] }}</div>
            <div class="mt-3 text-sm text-blue-100">{{ $today['ad']['label'] }} · {{ $today['ad']['time'] }}</div>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 p-4">
            <div class="text-xs font-semibold text-slate-400">A.D. Date</div>
            <div class="mt-1 font-bold text-slate-800">{{ $today['ad']['date'] }}</div>
        </div>
    </aside>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-1">Nepali Calendar</p>
                <h2 class="text-xl font-extrabold text-slate-900">
                    {{ \App\Support\NepaliCalendar::nepaliNumber($calendar['year']) }} {{ $calendar['month_name'] }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar.index', $calendar['prev']) }}"
                   class="rounded-xl border px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">← Previous</a>
                <a href="{{ route('calendar.index') }}"
                   class="rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Today</a>
                <a href="{{ route('calendar.index', $calendar['next']) }}"
                   class="rounded-xl border px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Next →</a>
            </div>
        </div>

        <form method="GET" action="{{ route('calendar.index') }}" class="mb-5 flex flex-wrap gap-2">
            <select name="year" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected($year === $calendar['year'])>{{ \App\Support\NepaliCalendar::nepaliNumber($year) }}</option>
                @endforeach
            </select>
            <select name="month" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                @for($month = 1; $month <= 12; $month++)
                    <option value="{{ $month }}" @selected($month === $calendar['month'])>{{ \App\Support\NepaliCalendar::monthName($month) }}</option>
                @endfor
            </select>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Show</button>
        </form>

        <div class="grid grid-cols-7 overflow-hidden rounded-2xl border border-slate-200">
            @foreach($weekdays as $day)
                <div class="bg-slate-100 px-2 py-3 text-center text-xs font-bold text-slate-500">{{ $day }}</div>
            @endforeach

            @for($i = 0; $i < $calendar['first_weekday']; $i++)
                <div class="min-h-20 border-t border-slate-100 bg-slate-50"></div>
            @endfor

            @for($day = 1; $day <= $calendar['days']; $day++)
                @php $isToday = $calendar['today_day'] === $day; @endphp
                <div class="min-h-20 border-t border-slate-100 p-2 {{ $isToday ? 'bg-blue-50 ring-2 ring-inset ring-blue-500' : 'bg-white' }}">
                    <div class="flex items-start justify-between gap-1">
                        <span class="grid h-8 w-8 place-items-center rounded-full text-sm font-black {{ $isToday ? 'bg-blue-600 text-white' : 'text-slate-800' }}">
                            {{ \App\Support\NepaliCalendar::nepaliNumber($day) }}
                        </span>
                        @if($isToday)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">आज</span>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </section>
</div>
@endsection
