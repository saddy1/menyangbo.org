@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('धन्यवाद — मेन्याङ्बो'))
@section('content')
<section class="themed-page feedback-success">
    <div class="theme-panel">
        <span class="success-check" aria-hidden="true">✓</span>
        <span class="home-eyebrow">{{ __('Message received') }}</span>
        <h1>{{ \App\Support\FrontendLocale::text('धन्यवाद!') }}</h1>
        <p>{{ \App\Support\FrontendLocale::text('तपाईंको सुझाव सफलतापूर्वक पठाइयो।') }}<br>{{ \App\Support\FrontendLocale::text('तपाईंको प्रतिक्रिया हाम्रो लागि अमूल्य छ।') }}</p>
        <a class="home-button home-button-primary" href="{{ \App\Support\FrontendLocale::route('home') }}">{{ \App\Support\FrontendLocale::text('मुख्य पृष्ठमा फर्कनुहोस्') }} <span aria-hidden="true">→</span></a>
    </div>
</section>
@endsection
