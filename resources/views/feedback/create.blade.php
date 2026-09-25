@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('सुझाव पठाउनुहोस् — मेन्याङ्बो'))

@section('content')
<div class="themed-page feedback-page">
    @include('partials.page-heading', [
        'eyebrow' => __('Your voice matters'),
        'title' => \App\Support\FrontendLocale::text('सुझाव पठाउनुहोस्'),
        'description' => \App\Support\FrontendLocale::text('तपाईंको मत र विचार हाम्रो लागि महत्त्वपूर्ण छ।'),
    ])
    <div class="feedback-layout">
        <aside class="feedback-intro">
            <div class="page-icon" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.5 8.5 0 0 1-12.3 7.6L3 21l1.9-5.7A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8 10h8M8 14h5"/></svg></div>
            <h2>{{ __('Help our community grow') }}</h2>
            <p>{{ __('Share your ideas, ask a question, or let us know how we can improve this website.') }}</p>
            <div class="feedback-location"><span class="home-eyebrow">{{ \App\Support\FrontendLocale::text('सम्पर्क') }}</span><p>{{ \App\Support\FrontendLocale::text('मेन्याङ्बो कल्याणकारी संघ') }}<br>{{ \App\Support\FrontendLocale::text('धरान–१५, सुनसरी, नेपाल') }}</p></div>
            <p class="feedback-hint">{{ __('Only your message is required. Contact details are optional.') }}</p>
        </aside>
        <form method="POST" action="{{ \App\Support\FrontendLocale::route('feedback.store') }}" class="theme-panel feedback-form"
              x-data="{ chars: {{ mb_strlen(old('description', '')) }}, sending: false }" @submit="sending = true">
            @csrf
            <input type="text" name="hp_field" class="hidden" tabindex="-1" aria-hidden="true" autocomplete="off">
            <div class="form-heading"><h2>{{ __('Your message') }}</h2><span>{{ __('We’re listening.') }}</span></div>
            @if($errors->any())
                <div class="form-errors" role="alert"><strong>{{ \App\Support\FrontendLocale::text('कृपया तलका त्रुटिहरू सुधार्नुहोस्:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="theme-field">
                <label for="feedback-name">{{ \App\Support\FrontendLocale::text('नाम') }} <span>{{ \App\Support\FrontendLocale::text('(ऐच्छिक)') }}</span></label>
                <input id="feedback-name" type="text" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" placeholder="{{ \App\Support\FrontendLocale::text('तपाईंको पूरा नाम') }}">
            </div>
            <div class="field-pair">
                <div class="theme-field">
                    <label for="feedback-email">{{ \App\Support\FrontendLocale::text('इमेल') }} <span>{{ \App\Support\FrontendLocale::text('(ऐच्छिक)') }}</span></label>
                    <input id="feedback-email" type="email" name="email" value="{{ old('email') }}" maxlength="190" autocomplete="email" placeholder="name@example.com">
                </div>
                <div class="theme-field">
                    <label for="feedback-phone">{{ \App\Support\FrontendLocale::text('फोन') }} <span>{{ \App\Support\FrontendLocale::text('(ऐच्छिक)') }}</span></label>
                    <input id="feedback-phone" type="tel" name="contact" value="{{ old('contact') }}" maxlength="60" autocomplete="tel" placeholder="98XXXXXXXX">
                </div>
            </div>
            <div class="theme-field">
                <div class="message-label"><label for="feedback-message">{{ \App\Support\FrontendLocale::text('विवरण') }} <span class="required-mark" aria-hidden="true">*</span></label><span id="message-counter" x-text="new Intl.NumberFormat(@js(app()->getLocale() === 'ne' ? 'ne-NP' : 'en'), {useGrouping:false}).format(chars) + ' / ' + @js(\App\Support\FrontendLocale::number(5000))">{{ \App\Support\FrontendLocale::number(mb_strlen(old('description', ''))) }} / {{ \App\Support\FrontendLocale::number(5000) }}</span></div>
                <textarea id="feedback-message" name="description" rows="6" required minlength="5" maxlength="5000" aria-describedby="message-counter" @input="chars = Array.from($event.target.value).length" placeholder="{{ \App\Support\FrontendLocale::text('तपाईंको सुझाव, विचार वा प्रश्न यहाँ लेख्नुहोस्…') }}">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="home-button home-button-primary feedback-submit" :disabled="sending">
                <span x-show="!sending">{{ \App\Support\FrontendLocale::text('सुझाव पठाउनुहोस्') }} <span aria-hidden="true">↗</span></span>
                <span x-show="sending" x-cloak>{{ \App\Support\FrontendLocale::text('पठाउँदैछ…') }}</span>
            </button>
            <p class="form-privacy">{{ \App\Support\FrontendLocale::text('तपाईंको जानकारी सुरक्षित राखिनेछ र कसैसँग साझा गरिने छैन।') }}</p>
        </form>
    </div>
</div>
@endsection
