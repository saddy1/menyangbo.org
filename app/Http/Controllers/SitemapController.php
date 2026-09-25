<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Page;
use App\Support\Seo;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $entries = [];
        foreach (['home', 'committee.index', 'tree.index', 'notices.public', 'gallery.index', 'calendar.index', 'feedback.create'] as $name) {
            $entries[] = ['url' => Seo::url(route($name, [], false))];
        }
        foreach (Page::where('is_published', true)->select('slug', 'updated_at')->cursor() as $page) {
            $entries[] = ['url' => Seo::url(route('page.show', $page->slug, false)), 'lastmod' => $page->updated_at?->toAtomString()];
        }
        foreach (Notice::where('is_active', true)->select('id', 'updated_at')->cursor() as $notice) {
            $entries[] = ['url' => Seo::url(route('notices.show', $notice->id, false)), 'lastmod' => $notice->updated_at?->toAtomString()];
        }

        $entries = array_merge($entries, array_map(fn ($entry) => array_merge($entry, ['url' => $entry['url'].'?lang=en']), $entries));

        return response()->view('sitemap', compact('entries'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
