@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('पात्रो'))
@section('meta_description', \App\Support\FrontendLocale::text('नेपाली र अंग्रेजी मिति र समय'))
@section('content')
@php $weekdays = ['आइत', 'सोम', 'मंगल', 'बुध', 'बिही', 'शुक्र', 'शनि']; @endphp
<div class="themed-page calendar-page">
    @include('partials.page-heading', [
        'eyebrow' => __('Every day, connected'),
        'title' => \App\Support\FrontendLocale::text('पात्रो'),
        'description' => __('Explore Nepali dates alongside the Gregorian calendar.'),
    ])
    <div class="calendar-layout">
        <aside class="calendar-sidebar">
            <div class="calendar-today">
                <span class="home-eyebrow">{{ \App\Support\FrontendLocale::text('Today') }}</span>
                <h2>{{ \App\Support\FrontendLocale::text($today['weekday']) }}</h2>
                <strong class="today-number">{{ \App\Support\FrontendLocale::number($today['bs']['day']) }}</strong>
                <p>{{ \App\Support\FrontendLocale::text(\App\Support\NepaliCalendar::monthName($today['bs']['month'])) }} {{ \App\Support\FrontendLocale::number($today['bs']['year']) }}</p>
                <div class="calendar-ad"><span>{{ \App\Support\FrontendLocale::text('A.D. Date') }}</span><strong>{{ $today['ad']['label'] }}</strong></div>
            </div>
            <p class="calendar-timezone">{{ __('Dates follow Nepal time.') }}</p>
        </aside>
        <section class="theme-panel calendar-panel" aria-labelledby="calendar-month">
            <div class="calendar-toolbar">
                <div><span class="home-eyebrow">{{ \App\Support\FrontendLocale::text('Nepali Calendar') }}</span><h2 id="calendar-month">{{ \App\Support\FrontendLocale::text($calendar['month_name']) }} <span>{{ \App\Support\FrontendLocale::number($calendar['year']) }}</span></h2></div>
                <nav class="calendar-navigation" aria-label="{{ __('Calendar navigation') }}">
                    <a href="{{ \App\Support\FrontendLocale::route('calendar.index', $calendar['prev']) }}" aria-label="{{ __('Previous month') }}" title="{{ __('Previous month') }}">←</a>
                    <a class="calendar-today-link" href="{{ \App\Support\FrontendLocale::route('calendar.index') }}">{{ \App\Support\FrontendLocale::text('Today') }}</a>
                    <a href="{{ \App\Support\FrontendLocale::route('calendar.index', $calendar['next']) }}" aria-label="{{ __('Next month') }}" title="{{ __('Next month') }}">→</a>
                </nav>
            </div>
            <form method="GET" action="{{ \App\Support\FrontendLocale::route('calendar.index') }}" class="calendar-filter">
                <input type="hidden" name="lang" value="{{ \App\Support\FrontendLocale::locale() }}">
                <select aria-label="{{ __('Year') }}" name="year">
                    @foreach($years as $year)<option value="{{ $year }}" @selected($year === $calendar['year'])>{{ \App\Support\FrontendLocale::number($year) }}</option>@endforeach
                </select>
                <select aria-label="{{ __('Month') }}" name="month">
                    @for($month = 1; $month <= 12; $month++)<option value="{{ $month }}" @selected($month === $calendar['month'])>{{ \App\Support\FrontendLocale::text(\App\Support\NepaliCalendar::monthName($month)) }}</option>@endfor
                </select>
                <button type="submit" class="home-button home-button-primary">{{ \App\Support\FrontendLocale::text('Show') }} <span aria-hidden="true">→</span></button>
            </form>
            <div class="month-grid" role="table" aria-labelledby="calendar-month">
                <div class="month-weekdays" role="row">
                    @foreach($weekdays as $day)<div role="columnheader">{{ \App\Support\FrontendLocale::text($day) }}</div>@endforeach
                </div>
                @for($row = 0; $row < (int) ceil(($calendar['first_weekday'] + $calendar['days']) / 7); $row++)
                    <div class="month-week" role="row">
                        @for($column = 0; $column < 7; $column++)
                            @php
                                $day = $row * 7 + $column - $calendar['first_weekday'] + 1;
                                $valid = $day >= 1 && $day <= $calendar['days'];
                                $isToday = $valid && $calendar['today_day'] === $day;
                            @endphp
                            <div role="cell" class="month-day {{ !$valid ? 'is-empty' : '' }} {{ $isToday ? 'is-today' : '' }}" @if($isToday) aria-current="date" @endif>
                                @if($valid)
                                    <span>{{ \App\Support\FrontendLocale::number($day) }}</span>
                                    @if($isToday)<small>{{ \App\Support\FrontendLocale::text('आज') }}</small>@endif
                                @endif
                            </div>
                        @endfor
                    </div>
                @endfor
            </div>
        </section>
    </div>
</div>
@endsection
