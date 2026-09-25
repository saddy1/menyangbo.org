<?php

namespace App\Support;

class FrontendLocale
{
    public static function locale(): string
    {
        return request()->attributes->get('frontend_locale', 'ne');
    }

    public static function text(string $text): string
    {
        // Shared camera/marriage components also appear in the admin panel.
        return request()->attributes->has('frontend_locale') ? __($text, [], self::locale()) : $text;
    }

    public static function url(string $url, ?string $locale = null): string
    {
        if (!request()->attributes->has('frontend_locale')) {
            return $url;
        }
        $parts = parse_url($url);
        if ($parts === false || isset($parts['scheme']) && !in_array($parts['scheme'], ['http', 'https'], true)) {
            return $url;
        }
        if (isset($parts['host']) && !in_array($parts['host'], [request()->getHost(), parse_url(config('app.url'), PHP_URL_HOST), parse_url(config('seo.url'), PHP_URL_HOST)], true)) {
            return $url;
        }
        $path = $parts['path'] ?? '';
        if (preg_match('~^/(admin(?:/|$)|login/admin|storage/|notices/)~', $path) || str_starts_with($url, '#')) {
            return $url;
        }
        parse_str($parts['query'] ?? '', $query);
        $query['lang'] = $locale ?? self::locale();
        $base = explode('?', explode('#', $url, 2)[0], 2)[0];

        return $base.'?'.http_build_query($query).(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

    public static function route($name, $parameters = [], $absolute = true): string
    {
        return self::url(route($name, $parameters, $absolute));
    }

    public static function number($value): string
    {
        return self::locale() === 'ne' ? NepaliCalendar::nepaliNumber($value) : strtr((string) $value, array_combine(preg_split('//u', '०१२३४५६७८९', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)));
    }

    public static function birthLabel(?string $label): string
    {
        if (!$label || self::locale() !== 'en') {
            return $label ?? '';
        }

        return self::number(implode(' ', array_map(fn ($part) => self::text($part), explode(' ', $label))));
    }

    public static function dateLabel(string $label): string
    {
        return self::number(implode(' ', array_map(fn ($part) => self::text($part), explode(' ', $label))));
    }
}
