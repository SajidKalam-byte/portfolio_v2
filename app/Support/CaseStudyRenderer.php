<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Renders a project's Markdown case study into safe HTML.
 *
 * - Raw HTML in the Markdown is escaped and unsafe links are dropped.
 * - h2/h3 headings get stable ids; h2s form the table of contents.
 * - A paragraph containing only [[architecture]] or [[highlights]] is replaced
 *   by the matching structured block. Blocks with data that aren't placed
 *   explicitly are appended at the end.
 */
class CaseStudyRenderer
{
    private const BLOCKS = [
        'architecture' => 'projects.partials.architecture',
        'highlights' => 'projects.partials.highlights',
    ];

    /**
     * @return array{html: string, toc: list<array{id: string, text: string}>}
     */
    public function render(Project $project): array
    {
        $key = 'case-study:'.$project->id.':'.($project->updated_at?->timestamp ?? 0);

        return ContentCache::remember($key, fn () => $this->build($project));
    }

    /**
     * Adds a zero-padded data-index ("01", "02", …) to every <h2> in order,
     * rendered by CSS as the editorial section number.
     */
    public static function numberSections(string $html): string
    {
        $index = 0;

        return preg_replace_callback('/<h2(?=[\s>])/', function () use (&$index) {
            return '<h2 data-index="'.str_pad((string) ++$index, 2, '0', STR_PAD_LEFT).'"';
        }, $html) ?? $html;
    }

    /**
     * @return array{html: string, toc: list<array{id: string, text: string}>}
     */
    private function build(Project $project): array
    {
        $markdown = trim((string) $project->case_study_content);
        $html = $markdown === '' ? '' : $this->converter()->convert($markdown)->getContent();

        $usedIds = [];
        $html = $this->addHeadingIds($html, $usedIds);
        $html = $this->insertBlocks($html, $project);

        return [
            'html' => $html,
            'toc' => $this->tableOfContents($html),
        ];
    }

    private function converter(): MarkdownConverter
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 20,
            'external_link' => [
                'internal_hosts' => array_filter([$host]),
                'open_in_new_window' => true,
                'noopener' => 'external',
                'noreferrer' => 'external',
                'nofollow' => '',
                'html_class' => 'external-link',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new ExternalLinkExtension);

        return new MarkdownConverter($environment);
    }

    /**
     * @param  array<string, true>  $usedIds
     */
    private function addHeadingIds(string $html, array &$usedIds): string
    {
        return preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function (array $m) use (&$usedIds) {
            $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5));
            $base = Str::slug($text) ?: 'section';
            $id = $base;

            for ($i = 2; isset($usedIds[$id]); $i++) {
                $id = "{$base}-{$i}";
            }

            $usedIds[$id] = true;

            return "<h{$m[1]} id=\"{$id}\">{$m[2]}</h{$m[1]}>";
        }, $html) ?? $html;
    }

    private function insertBlocks(string $html, Project $project): string
    {
        foreach (self::BLOCKS as $name => $view) {
            if (! $this->hasBlockData($project, $name)) {
                $html = str_replace("<p>[[{$name}]]</p>", '', $html);

                continue;
            }

            $block = view($view, ['project' => $project])->render();
            $placeholder = "<p>[[{$name}]]</p>";

            $html = str_contains($html, $placeholder)
                ? str_replace($placeholder, $block, $html)
                : $html.$block;
        }

        return $html;
    }

    private function hasBlockData(Project $project, string $block): bool
    {
        return match ($block) {
            'architecture' => $project->architectureLayers() !== [],
            'highlights' => ! empty($project->highlights),
            default => false,
        };
    }

    /**
     * @return list<array{id: string, text: string}>
     */
    private function tableOfContents(string $html): array
    {
        preg_match_all('/<h2 id="([^"]+)"[^>]*>(.*?)<\/h2>/s', $html, $matches, PREG_SET_ORDER);

        return array_map(fn (array $m) => [
            'id' => $m[1],
            'text' => trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5)),
        ], $matches);
    }
}
