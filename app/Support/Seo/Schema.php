<?php

namespace App\Support\Seo;

use App\Models\Education;
use App\Models\Project;
use App\Support\Settings;
use App\Support\Site;
use Illuminate\Support\Collection;

/**
 * schema.org node builders. Only facts present in the content are emitted —
 * no ratings, reviews, or inferred credentials.
 */
class Schema
{
    public function __construct(
        private Site $site,
        private Settings $settings,
    ) {}

    public function personId(): string
    {
        return url('/').'#person';
    }

    public function websiteId(): string
    {
        return url('/').'#website';
    }

    /**
     * @param  list<string>  $knowsAbout
     * @param  Collection<int, Education>|null  $education  Completed programmes become `alumniOf`.
     * @return array<string, mixed>
     */
    public function person(array $knowsAbout = [], ?Collection $education = null): array
    {
        $employer = config('portfolio.employer');

        return [
            '@type' => 'Person',
            '@id' => $this->personId(),
            'name' => $this->site->name(),
            'url' => url('/'),
            'image' => $this->site->photoUrl(),
            'jobTitle' => $this->site->jobTitle(),
            'description' => $this->site->description(),
            'email' => $this->site->email() ? 'mailto:'.$this->site->email() : null,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => config('portfolio.identity.locality'),
                'addressRegion' => config('portfolio.identity.region'),
                'addressCountry' => config('portfolio.identity.country_code'),
            ],
            'sameAs' => array_column($this->site->socialLinks(), 'url'),
            'knowsAbout' => array_values(array_unique($knowsAbout)),
            'worksFor' => $this->settings->bool('schema_works_for') ? [
                '@type' => 'Organization',
                'name' => $employer['name'],
                'url' => $employer['url'],
            ] : null,
            'alumniOf' => $education
                ?->filter(fn (Education $e) => $e->isCompleted())
                ->map(fn (Education $e) => [
                    '@type' => 'CollegeOrUniversity',
                    'name' => $e->institution,
                ])
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $this->websiteId(),
            'url' => url('/'),
            'name' => $this->site->name(),
            'description' => $this->site->description(),
            'inLanguage' => 'en',
            'publisher' => ['@id' => $this->personId()],
        ];
    }

    /**
     * @param  array<string, mixed>  $person
     * @return array<string, mixed>
     */
    public function profilePage(array $person, string $url): array
    {
        return [
            '@type' => 'ProfilePage',
            '@id' => $url.'#profile',
            'url' => $url,
            'name' => 'About '.$this->site->name(),
            'isPartOf' => ['@id' => $this->websiteId()],
            'mainEntity' => $person,
        ];
    }

    /**
     * @param  array<string, string>  $items  label => url
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->keys()->map(fn ($label, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $label,
                'item' => $items[$label],
            ])->all(),
        ];
    }

    /**
     * A case study is described as a CreativeWork authored by the site owner.
     * SoftwareApplication is avoided: it implies an installable/offered product.
     *
     * @return array<string, mixed>
     */
    public function project(Project $project): array
    {
        return [
            '@type' => 'CreativeWork',
            '@id' => $project->url().'#work',
            'name' => $project->title,
            'headline' => $project->title,
            'description' => $project->seo_description ?: $project->short_description,
            'url' => $project->url(),
            'image' => $project->ogImageUrl(),
            'genre' => $project->categoryLabel(),
            'keywords' => implode(', ', $project->techList()),
            'author' => ['@id' => $this->personId()],
            'isPartOf' => ['@id' => $this->websiteId()],
            'dateModified' => $project->updated_at?->toAtomString(),
        ];
    }
}
