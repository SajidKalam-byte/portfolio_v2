<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\PortfolioContent;
use App\Support\ContentCache;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(PortfolioContent $content): Response
    {
        $xml = ContentCache::remember('sitemap:'.url('/'), function () use ($content) {
            $projects = $content->allProjects();

            $entries = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'monthly'],
                ['loc' => route('projects.index'), 'priority' => '0.9', 'changefreq' => 'monthly',
                    'lastmod' => $projects->max('updated_at')],
                ['loc' => route('about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => route('experience'), 'priority' => '0.8', 'changefreq' => 'monthly'],
                ['loc' => route('resume'), 'priority' => '0.6', 'changefreq' => 'monthly'],
                ['loc' => route('contact'), 'priority' => '0.5', 'changefreq' => 'yearly'],
            ];

            foreach ($projects as $project) {
                /** @var Project $project */
                $entries[] = [
                    'loc' => $project->url(),
                    'lastmod' => $project->updated_at,
                    'priority' => $project->is_featured ? '0.9' : '0.7',
                    'changefreq' => 'monthly',
                ];
            }

            return $this->render($entries);
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * @param  list<array{loc: string, lastmod?: mixed, priority: string, changefreq: string}>  $entries
     */
    private function render(array $entries): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";

            if (! empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.$entry['lastmod']->toAtomString()."</lastmod>\n";
            }

            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        return $xml.'</urlset>'."\n";
    }
}
