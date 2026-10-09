<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\PortfolioContent;
use App\Support\CaseStudyRenderer;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request, PortfolioContent $content, Schema $schema, Site $site): View
    {
        $categories = $content->projectCategoryCounts();
        $category = $request->query('category');
        $category = is_string($category) && isset($categories[$category]) ? $category : null;

        $lead = null;
        $query = Project::published()->ordered();

        if ($category) {
            $query->where('category', $category);
        } elseif ((int) $request->query('page', 1) <= 1) {
            // Unfiltered first page: the lead featured project gets its own hero card.
            $lead = $content->featuredProjects()->first();
        }

        $projects = $query
            ->when($lead, fn ($q) => $q->whereKeyNot($lead->getKey()))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $label = $category ? config("portfolio.project_categories.{$category}") : null;

        $seo = Seo::make(
            $label ? "{$label} projects" : 'Projects & case studies',
            $label
                ? "{$label} work by {$site->name()}: problems, architecture, and engineering decisions."
                : "Case studies by {$site->name()}: a production healthcare EMR platform, an end-to-end B2B customer portal, NLP classifiers, and web applications built with Python, Django, and JavaScript.",
        )
            ->canonical($projects->currentPage() > 1 && ! $category
                ? route('projects.index', ['page' => $projects->currentPage()])
                : route('projects.index'))
            ->schema($schema->breadcrumbs(['Home' => route('home'), 'Projects' => route('projects.index')]));

        // Filtered listings duplicate the main index; keep them out of search results.
        if ($category) {
            $seo->noIndex();
        }

        return view('pages.projects.index', [
            'seo' => $seo,
            'lead' => $lead,
            'projects' => $projects,
            'categories' => $categories,
            'activeCategory' => $category,
            'totalCount' => array_sum($categories),
        ]);
    }

    public function show(Project $project, PortfolioContent $content, CaseStudyRenderer $renderer, Schema $schema): View
    {
        abort_unless($project->isPublic(), 404);

        $project->load('images');

        $seo = Seo::make(
            $project->seo_title ?: "{$project->title} — Case study",
            $project->seo_description ?: $project->short_description,
        )
            ->canonical($project->url())
            ->type('article')
            ->image($project->og_image_path ? $project->ogImageUrl() : null, $project->title)
            ->property('article:modified_time', $project->updated_at?->toAtomString())
            ->property('article:section', $project->categoryLabel())
            ->schema(
                $schema->project($project),
                $schema->breadcrumbs([
                    'Home' => route('home'),
                    'Projects' => route('projects.index'),
                    $project->title => $project->url(),
                ]),
            );

        return view('pages.projects.show', [
            'seo' => $seo,
            'project' => $project,
            'caseStudy' => $renderer->render($project),
            'testimonials' => $content->testimonialsForProject($project),
            'related' => $content->relatedTo($project),
            'neighbours' => $content->neighbours($project),
        ]);
    }
}
