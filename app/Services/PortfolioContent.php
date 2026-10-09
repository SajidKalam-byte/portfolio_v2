<?php

namespace App\Services;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Support\ContentCache;
use Illuminate\Support\Collection;

/**
 * Cached read-side queries for public pages.
 */
class PortfolioContent
{
    /** @return Collection<int, Project> */
    public function featuredProjects(): Collection
    {
        return ContentCache::rememberModels('projects:featured', Project::class, fn () => Project::published()
            ->where('is_featured', true)
            ->ordered()
            ->get());
    }

    /**
     * Published projects that aren't featured, for "selected work" grids.
     *
     * @return Collection<int, Project>
     */
    public function otherProjects(int $limit = 6): Collection
    {
        return ContentCache::rememberModels("projects:other:{$limit}", Project::class, fn () => Project::published()
            ->where('is_featured', false)
            ->ordered()
            ->limit($limit)
            ->get());
    }

    /** @return Collection<int, Project> */
    public function allProjects(): Collection
    {
        return ContentCache::rememberModels('projects:all', Project::class, fn () => Project::published()->ordered()->get());
    }

    /**
     * @return array<string, int> category key => published project count, in configured order.
     */
    public function projectCategoryCounts(): array
    {
        $counts = ContentCache::remember('projects:category-counts', fn () => Project::published()
            ->selectRaw('category, COUNT(*) as aggregate')
            ->groupBy('category')
            ->pluck('aggregate', 'category')
            ->map(fn ($count) => (int) $count)
            ->all());

        return collect(config('portfolio.project_categories'))
            ->keys()
            ->filter(fn ($key) => isset($counts[$key]))
            ->mapWithKeys(fn ($key) => [$key => $counts[$key]])
            ->all();
    }

    /**
     * Visible skills grouped by category, in configured category order.
     *
     * @return Collection<string, array{key: string, label: string, description: string, skills: Collection<int, Skill>}>
     */
    public function skillGroups(): Collection
    {
        $skills = ContentCache::rememberModels('skills:visible', Skill::class, fn () => Skill::visible()->ordered()->get());

        return collect(config('portfolio.skill_categories'))
            ->map(fn (array $meta, string $key) => [
                'key' => $key,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'skills' => $skills->where('category', $key)->values(),
            ])
            ->filter(fn (array $group) => $group['skills']->isNotEmpty());
    }

    /** @return list<string> */
    public function skillNames(int $limit = 20): array
    {
        return $this->skillGroups()
            ->flatMap(fn (array $group) => $group['skills']->pluck('name'))
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }

    /** @return Collection<int, Experience> */
    public function experiences(): Collection
    {
        return ContentCache::rememberModels('experiences', Experience::class, fn () => Experience::ordered()->get());
    }

    /** @return Collection<int, Education> */
    public function educations(): Collection
    {
        return ContentCache::rememberModels('educations', Education::class, fn () => Education::ordered()->get());
    }

    /**
     * Published projects keyed by slug, for linking from experience entries.
     *
     * @param  list<string>  $slugs
     * @return Collection<int, Project>
     */
    public function projectsBySlugs(array $slugs): Collection
    {
        if ($slugs === []) {
            return collect();
        }

        return $this->allProjects()
            ->whereIn('slug', $slugs)
            ->sortBy(fn (Project $p) => array_search($p->slug, $slugs, true))
            ->values();
    }

    /**
     * Up to $limit related projects: same category first, then featured, then the rest.
     *
     * @return Collection<int, Project>
     */
    public function relatedTo(Project $project, int $limit = 3): Collection
    {
        return $this->allProjects()
            ->reject(fn (Project $p) => $p->is($project))
            ->sortBy(fn (Project $p) => [
                $p->category === $project->category ? 0 : 1,
                $p->is_featured ? 0 : 1,
                $p->sort_order,
            ])
            ->take($limit)
            ->values();
    }

    /**
     * Published testimonials (featured first), each with its project relation
     * set from the cached project list — only published projects are linked.
     *
     * @return Collection<int, Testimonial>
     */
    public function testimonials(): Collection
    {
        $testimonials = ContentCache::rememberModels('testimonials:published', Testimonial::class, fn () => Testimonial::published()
            ->ordered()
            ->get());

        $projects = $this->allProjects()->keyBy('id');

        return $testimonials->each(fn (Testimonial $testimonial) => $testimonial->setRelation(
            'project',
            $testimonial->project_id ? $projects->get($testimonial->project_id) : null,
        ));
    }

    /** @return Collection<int, Testimonial> */
    public function featuredTestimonials(int $limit = 6): Collection
    {
        return $this->testimonials()->take($limit)->values();
    }

    /** @return Collection<int, Testimonial> */
    public function testimonialsForProject(Project $project): Collection
    {
        return $this->testimonials()->where('project_id', $project->id)->values();
    }

    /**
     * The top testimonial for each project, keyed by project id (for featured cards).
     *
     * @return Collection<int, Testimonial>
     */
    public function testimonialHighlights(): Collection
    {
        return $this->testimonials()
            ->filter(fn (Testimonial $testimonial) => $testimonial->project !== null)
            ->unique('project_id')
            ->keyBy('project_id');
    }

    /**
     * Previous and next published projects in display order.
     *
     * @return array{previous: ?Project, next: ?Project}
     */
    public function neighbours(Project $project): array
    {
        $all = $this->allProjects()->values();
        $index = $all->search(fn (Project $p) => $p->is($project));

        if ($index === false) {
            return ['previous' => null, 'next' => null];
        }

        return [
            'previous' => $all->get($index - 1),
            'next' => $all->get($index + 1),
        ];
    }
}
