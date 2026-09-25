<?php

namespace Tests\Feature;

use App\Support\Seo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_metadata_is_escaped_and_structured_data_is_valid(): void
    {
        $this->app['view']->startSection('title', 'A "title" & test');
        $this->app['view']->startSection('meta_description', 'A "description" & text');
        $html = view('partials.seo')->render();
        $this->assertStringContainsString('A &quot;description&quot; &amp; text', $html);
        $this->assertStringContainsString('property="og:image"', $html);
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schema = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Organization', $schema['@graph'][0]['@type']);
        $this->assertStringContainsString('A "title" & test', $schema['@graph'][2]['name']);
    }

    public function test_canonical_keeps_gallery_pagination_but_discards_tracking(): void
    {
        config(['seo.url' => 'https://menyanbo.org']);
        $request = Request::create('/gallery?page=2&utm_source=test');
        $request->setRouteResolver(fn () => Route::getRoutes()->getByName('gallery.index'));
        $this->assertSame('https://menyanbo.org/gallery?page=2', Seo::canonical($request));
        $this->assertSame('Hello & world', Seo::description('<p>Hello &amp;</p><p>world</p>'));
    }

    public function test_internal_endpoints_are_noindex_but_public_pages_are_not(): void
    {
        foreach (['login', 'password.reset', 'tree.json', 'admin.people.directory.all', 'person.show'] as $name) {
            $request = Request::create('/');
            $request->setRouteResolver(fn () => Route::getRoutes()->getByName($name));
            $this->assertTrue(Seo::noindex($request), $name);
        }
        $request = Request::create('/members');
        $request->setRouteResolver(fn () => Route::getRoutes()->getByName('admin.people.directory'));
        $this->assertFalse(Seo::noindex($request));
        Route::get('/admin/seo-test', fn () => 'test');
        $this->get('/admin/seo-test')->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_sitemap_only_contains_published_content(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach (['pages', 'notices'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->id();
                $blueprint->string('slug')->nullable();
                $blueprint->boolean($table === 'pages' ? 'is_published' : 'is_active');
                $blueprint->timestamps();
            });
        }
        DB::table('pages')->insert([
            ['slug' => 'published', 'is_published' => true, 'updated_at' => '2026-01-01 00:00:00'],
            ['slug' => 'draft', 'is_published' => false, 'updated_at' => null],
        ]);
        DB::table('notices')->insert([['is_active' => true], ['is_active' => false]]);
        $response = $this->get('/sitemap.xml');
        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $urls = array_map(fn ($entry) => (string) $entry->loc, iterator_to_array($xml->url, false));
        $this->assertContains(Seo::url('/page/published'), $urls);
        $this->assertNotContains(Seo::url('/page/draft'), $urls);
        $this->assertContains(Seo::url(route('notices.show', 1, false)), $urls);
        $this->assertNotContains(Seo::url(route('notices.show', 2, false)), $urls);
        $this->assertNotContains(Seo::url('/login'), $urls);
    }
}
