<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function pages(): array
    {
        return [
            'home' => ['/', 'Backend thinking.'],
            'about' => ['/about', 'I follow problems all the way'],
            'experience' => ['/experience', 'Corunit Media Corp'],
            'projects' => ['/projects', 'Lanexis Customer Portal'],
            'emr case study' => ['/projects/emr-patient-intake-platform', 'Access control and auditability'],
            'lanexis case study' => ['/projects/lanexis-customer-portal', 'manual synchronization'],
            'ml case study' => ['/projects/sms-spam-classification', 'reported 98% classification accuracy'],
            'contact' => ['/contact', 'Send a message'],
            'resume' => ['/resume', 'Summary'],
        ];
    }

    #[DataProvider('pages')]
    public function test_public_pages_render_with_seo_metadata(string $uri, string $expectedText): void
    {
        $response = $this->get($uri);

        $response->assertOk()
            ->assertSee($expectedText, false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<meta name="description"', false);

        // Exactly one <h1> per page.
        $this->assertSame(1, substr_count($response->getContent(), '<h1'), "Expected one <h1> on {$uri}");
    }

    public function test_every_page_has_a_unique_title(): void
    {
        $titles = collect(self::pages())
            ->map(fn (array $page) => $this->extractTitle($this->get($page[0])->getContent()));

        $this->assertSame($titles->count(), $titles->unique()->count(), 'Page titles must be unique.');
    }

    public function test_home_page_emits_valid_person_and_website_structured_data(): void
    {
        $graph = $this->structuredData($this->get('/')->getContent());
        $types = array_column($graph, '@type');

        $this->assertContains('Person', $types);
        $this->assertContains('WebSite', $types);

        $person = collect($graph)->firstWhere('@type', 'Person');
        $this->assertSame('Sajid Kalam', $person['name']);
        $this->assertContains('https://github.com/SajidKalam-byte', $person['sameAs']);
        // worksFor stays off until confirmed in settings.
        $this->assertArrayNotHasKey('worksFor', $person);
    }

    public function test_about_page_only_lists_completed_education_as_alumni_of(): void
    {
        $graph = $this->structuredData($this->get('/about')->getContent());
        $profile = collect($graph)->firstWhere('@type', 'ProfilePage');
        $alumniOf = array_column($profile['mainEntity']['alumniOf'], 'name');

        $this->assertSame(['University of North Bengal'], $alumniOf);
    }

    public function test_project_page_emits_creative_work_and_breadcrumbs(): void
    {
        $graph = $this->structuredData($this->get('/projects/chess-for-two')->getContent());
        $types = array_column($graph, '@type');

        $this->assertContains('CreativeWork', $types);
        $this->assertContains('BreadcrumbList', $types);
        $this->assertNotContains('AggregateRating', $types);
    }

    public function test_confidential_projects_hide_external_links(): void
    {
        $this->get('/projects/private-client-work')
            ->assertOk()
            ->assertSee('Confidential')
            ->assertDontSee('Source on GitHub');
    }

    public function test_verified_live_links_are_shown(): void
    {
        $this->get('/projects/chess-for-two')
            ->assertSee('https://chess-for-2.vercel.app', false)
            ->assertSee('https://github.com/SajidKalam-byte/Chess_for_2', false);
    }

    public function test_unknown_project_returns_styled_404(): void
    {
        $this->get('/projects/does-not-exist')
            ->assertNotFound()
            ->assertSee('This page took a')
            ->assertSee('noindex', false);
    }

    public function test_filtered_project_listing_is_noindexed_and_canonicalised(): void
    {
        $this->get('/projects?category=machine-learning')
            ->assertOk()
            ->assertSee('SMS Spam Classification')
            ->assertDontSee('Lanexis Customer Portal')
            ->assertSee('<link rel="canonical" href="'.url('/projects').'">', false);
    }

    public function test_phone_number_is_hidden_by_default(): void
    {
        config(['portfolio.defaults.phone' => '+91 90000 00000']);

        $this->get('/contact')->assertDontSee('+91 90000 00000');
    }

    public function test_only_the_configured_public_email_is_rendered(): void
    {
        $this->get('/contact')
            ->assertSee('sajid.kalam.dev@gmail.com')
            ->assertDontSee('sk5102552@gmail.com');
    }

    private function extractTitle(string $html): string
    {
        preg_match('/<title>(.*?)<\/title>/s', $html, $m);

        return trim($m[1] ?? '');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function structuredData(string $html): array
    {
        $this->assertMatchesRegularExpression('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html);
        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);

        $data = json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $data['@context']);

        return $data['@graph'];
    }
}
