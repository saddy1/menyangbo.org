<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Seo
{
    public const NAME = 'मेन्याङ्बो कल्याणकारी संघ';

    public const DESCRIPTION = 'मेन्याङ्बो कल्याणकारी संघको आधिकारिक वेबसाइट। मेन्याङ्बो वंशावली, कार्यसमिति, सूचना, फोटो ग्यालेरी र नेपाली पात्रो हेर्नुहोस्।';

    public static function description(?string $text): string
    {
        $text = strip_tags(preg_replace('/<[^>]+>/', ' $0 ', $text ?? ''));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 160);
    }

    public static function url(string $path = '/'): string
    {
        return rtrim(config('seo.url'), '/').'/'.ltrim($path, '/');
    }

    public static function canonical(Request $request, ?string $locale = null): string
    {
        $url = self::url($request->getPathInfo());
        $query = [];
        $page = filter_var($request->query('page'), FILTER_VALIDATE_INT);
        if ($request->routeIs('gallery.index') && $page > 1) {
            $query['page'] = $page;
        }

        if (($locale ?? \App\Support\FrontendLocale::locale()) === 'en') {
            $query['lang'] = 'en';
        }

        return $url.($query ? '?'.http_build_query($query) : '');
    }

    public static function noindex(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'api/*') || $request->routeIs(
            'login', 'register', 'password.*', 'verification.*', 'auth.*',
            'admin.*', 'my.requests', 'request.*', 'feedback.thanks',
            'tree.json', 'tree.unconnected', 'people.*', 'person.show'
        ) && !$request->routeIs('admin.people.directory');
    }
}
