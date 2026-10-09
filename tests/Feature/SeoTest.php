<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Support\Seo\JsonLd;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_valid_xml_with_absolute_urls_for_published_content(): void
    {
        $this->seed(DatabaseSeeder::class);
        $draft = Project::factory()->draft()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'Sitemap must be well-formed XML.');

        // Iterate explicitly: every child is keyed "url", so collect() would keep only the last one.
        $locs = collect(iterator_to_array($xml->url, false))->map(fn ($url) => (string) $url->loc);

        $this->assertTrue($locs->every(fn ($loc) => str_starts_with($loc, 'http')), 'All URLs must be absolute.');
        $this->assertContains(url('/'), $locs->all());
        $this->assertContains(url('/projects/emr-patient-intake-platform'), $locs->all());
        $this->assertNotContains(url('/projects/'.$draft->slug), $locs->all());
        $adminPath = config('portfolio.admin.path');
        $this->assertFalse($locs->contains(fn ($loc) => str_contains($loc, "/{$adminPath}")), 'Admin URLs must never be listed.');
    }

    public function test_robots_txt_allows_crawling_and_points_to_sitemap_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nAllow: /", false)
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false)
            ->assertDontSee('Disallow');
    }

    public function test_robots_txt_blocks_indexing_outside_production(): void
    {
        $this->get('/robots.txt')->assertSee('Disallow: /');
    }

    public function test_pages_are_noindexed_outside_production_and_indexable_in_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->app['env'] = 'production';

        $this->get('/')->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_json_ld_cannot_break_out_of_its_script_tag(): void
    {
        $project = Project::factory()->create(['title' => 'Evil </script><script>alert(1)</script>']);

        $html = $this->get($project->url())->assertOk()->getContent();

        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);

        $hexEscaped = trim(json_encode('</script>', JSON_HEX_TAG | JSON_UNESCAPED_SLASHES), '"');

        $this->assertStringNotContainsString('</script>', $m[1]);
        $this->assertStringContainsString($hexEscaped, $m[1]);
        $this->assertNotNull(json_decode($m[1], true));
    }

    public function test_breadcrumb_positions_are_sequential(): void
    {
        $project = Project::factory()->create();

        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $this->get($project->url())->getContent(), $m);
        $breadcrumbs = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'BreadcrumbList');

        $this->assertSame([1, 2, 3], array_column($breadcrumbs['itemListElement'], 'position'));
    }

    public function test_json_ld_drops_empty_values(): void
    {
        $json = JsonLd::graph([['@type' => 'Person', 'name' => 'A', 'email' => null, 'sameAs' => []]]);

        $this->assertSame('{"@context":"https://schema.org","@graph":[{"@type":"Person","name":"A"}]}', $json);
    }
}
