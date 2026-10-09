<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Support\CaseStudyRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStudyRendererTest extends TestCase
{
    use RefreshDatabase;

    public function test_headings_get_unique_ids_and_build_the_table_of_contents(): void
    {
        $project = Project::factory()->create([
            'case_study_content' => "## The problem\n\nText\n\n### Detail\n\n## The problem\n\nMore",
            'architecture' => null,
            'highlights' => null,
        ]);

        $result = app(CaseStudyRenderer::class)->render($project);

        $this->assertStringContainsString('<h2 id="the-problem">', $result['html']);
        $this->assertStringContainsString('<h2 id="the-problem-2">', $result['html']);
        $this->assertStringContainsString('<h3 id="detail">', $result['html']);
        $this->assertSame(['the-problem', 'the-problem-2'], array_column($result['toc'], 'id'));
    }

    public function test_placeholders_are_replaced_by_structured_blocks(): void
    {
        $project = Project::factory()->create([
            'case_study_content' => "## Intro\n\n[[architecture]]\n\n## After",
            'architecture' => ['layers' => [['layer' => 'Data', 'title' => 'PostgreSQL']]],
            'highlights' => ['Fast'],
        ]);

        $result = app(CaseStudyRenderer::class)->render($project);
        $html = $result['html'];

        $this->assertStringNotContainsString('[[architecture]]', $html);
        $this->assertLessThan(strpos($html, 'id="after"'), strpos($html, 'id="architecture"'), 'Architecture sits where the placeholder was.');
        // Highlights had no placeholder, so they are appended.
        $this->assertGreaterThan(strpos($html, 'id="after"'), strpos($html, 'id="at-a-glance"'));
        $this->assertSame(['intro', 'architecture', 'after', 'at-a-glance'], array_column($result['toc'], 'id'));
    }

    public function test_sections_are_numbered_in_order(): void
    {
        $html = CaseStudyRenderer::numberSections('<h2 id="a">A</h2><p>x</p><h2>B</h2><h3>c</h3>');

        $this->assertSame('<h2 data-index="01" id="a">A</h2><p>x</p><h2 data-index="02">B</h2><h3>c</h3>', $html);
    }
}
