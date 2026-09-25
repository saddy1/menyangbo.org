<nav aria-label="{{ __('Language') }}" class="language-switcher">
    @foreach(['ne' => ['flag' => '🇳🇵', 'label' => 'ने', 'name' => 'नेपाली'], 'en' => ['flag' => '🇬🇧', 'label' => 'EN', 'name' => 'English']] as $language => $option)
        <a href="{{ \App\Support\FrontendLocale::url(request()->fullUrl(), $language) }}"
           lang="{{ $language }}" hreflang="{{ $language }}"
           title="{{ $option['name'] }}"
           aria-label="{{ $language === 'en' ? 'Switch to English' : 'नेपालीमा हेर्नुहोस्' }}"
           @if(\App\Support\FrontendLocale::locale() === $language) aria-current="true" @endif
           class="language-option">
            <span class="language-flag" aria-hidden="true">{{ $option['flag'] }}</span>
            <span class="language-label" aria-hidden="true">{{ $option['label'] }}</span>
        </a>
    @endforeach
</nav>
