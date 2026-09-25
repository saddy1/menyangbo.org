<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendLanguageTest extends TestCase
{
    public function test_public_pages_render_in_both_languages(): void
    {
        foreach (['home', 'tree.index', 'admin.people.directory', 'committee.index', 'notices.public', 'gallery.index', 'calendar.index', 'feedback.create', 'login', 'register', 'password.request'] as $name) {
            foreach (['en', 'ne'] as $locale) {
                $response = $this->get(route($name, ['lang' => $locale]));
                $response->assertOk()->assertHeader('Content-Language', $locale);
                $response->assertSee('<html lang="'.$locale.'">', false);
                $response->assertSee($locale === 'en' ? 'Menyanbo Welfare Association' : 'मेन्याङ्बो कल्याणकारी संघ');
            }
        }
    }

    public function test_public_content_details_render_in_both_languages(): void
    {
        $person = \App\Models\Person::firstOrFail();
        foreach (['en', 'ne'] as $locale) {
            $this->get(route('member.page', ['person' => $person->id, 'lang' => $locale]))
                ->assertOk()->assertHeader('Content-Language', $locale);
            $notice = \App\Models\Notice::where('is_active', true)->first();
            if ($notice) {
                $this->get(route('notices.show', ['notice' => $notice->id, 'lang' => $locale]))->assertOk()->assertSee($notice->title);
            }
        }
    }

    public function test_feedback_validation_uses_selected_language(): void
    {
        $response = $this->postJson(route('feedback.store', ['lang' => 'ne']), []);
        $response->assertUnprocessable()->assertJsonValidationErrors('description');
        $this->assertSame('सुझाव अनिवार्य छ।', $response->json('errors.description.0'));
        $response = $this->postJson(route('feedback.store', ['lang' => 'en']), []);
        $response->assertUnprocessable()->assertJsonValidationErrors('description');
        $this->assertSame('The description field is required.', $response->json('errors.description.0'));
    }

    public function test_preference_persists_and_invalid_languages_are_ignored(): void
    {
        $this->get('/login?lang=en')->assertSee('Log in');
        $this->get('/login')->assertHeader('Content-Language', 'en');
        $this->get('/login?lang=invalid')->assertHeader('Content-Language', 'en');
        $this->get('/login?lang=ne')->assertHeader('Content-Language', 'ne');
    }

    public function test_admin_is_not_localized(): void
    {
        $this->withSession(['frontend_locale' => 'en'])->get('/admin?lang=ne')
            ->assertOk()->assertHeaderMissing('Content-Language');
    }

    public function test_language_switch_preserves_calendar_filters_and_seo_uses_language_urls(): void
    {
        $response = $this->get('/calendar?year=2082&month=5&lang=en');
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $link = $xpath->query('//a[@hreflang="ne"]')->item(0)->getAttribute('href');
        parse_str(parse_url($link, PHP_URL_QUERY), $query);
        $this->assertSame(['lang' => 'ne', 'month' => '5', 'year' => '2082'], $query);
        $response->assertSee('href="https://menyanbo.org/calendar?lang=en"', false);
        $response->assertSee('hreflang="ne"', false);
        $response->assertSee('content="en_US"', false);
    }
}
