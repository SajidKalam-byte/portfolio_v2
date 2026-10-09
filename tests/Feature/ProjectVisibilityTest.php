<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_project_is_visible(): void
    {
        $project = Project::factory()->create(['title' => 'Visible Work']);

        $this->get($project->url())->assertOk()->assertSee('Visible Work');
        $this->get('/projects')->assertSee('Visible Work');
    }

    public function test_draft_project_is_hidden_everywhere(): void
    {
        $draft = Project::factory()->draft()->create(['title' => 'Secret Draft']);

        $this->get($draft->url())->assertNotFound();
        $this->get('/projects')->assertDontSee('Secret Draft');
        $this->get('/sitemap.xml')->assertDontSee($draft->slug);
    }

    public function test_scheduled_project_is_hidden_until_its_publish_date(): void
    {
        $scheduled = Project::factory()->scheduled()->create();

        $this->get($scheduled->url())->assertNotFound();

        $this->travel(8)->days();

        $this->get($scheduled->url())->assertOk();
    }

    public function test_related_projects_never_include_the_current_project(): void
    {
        $project = Project::factory()->create(['title' => 'Main Project', 'category' => 'machine-learning']);
        Project::factory()->count(3)->create(['category' => 'machine-learning']);

        $response = $this->get($project->url())->assertOk();

        // The title appears in the header, breadcrumb, and structured data — but not as a related card link.
        $this->assertStringNotContainsString(
            'href="'.$project->url().'" class="stretched-link"',
            $response->getContent(),
        );
    }

    public function test_listing_paginates_after_twelve_projects(): void
    {
        Project::factory()->count(14)->create();

        $this->get('/projects')->assertOk()->assertSee('rel="next"', false);
        $this->get('/projects?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/projects?page=2').'">', false);
    }

    public function test_case_study_markdown_is_escaped(): void
    {
        $project = Project::factory()->create([
            'case_study_content' => "## Heading\n\n<script>alert('x')</script>\n\n[bad](javascript:alert(1))",
        ]);

        $html = $this->get($project->url())->assertOk()->getContent();

        $this->assertStringNotContainsString("<script>alert('x')</script>", $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
    }
}
