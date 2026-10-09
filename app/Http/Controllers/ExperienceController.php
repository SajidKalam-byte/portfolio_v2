<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Services\PortfolioContent;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    public function __invoke(PortfolioContent $content, Schema $schema, Site $site): View
    {
        $experiences = $content->experiences();

        $seo = Seo::make(
            'Experience',
            "Professional experience of {$site->name()}: production healthcare software at Corunit Media Corp — Django, REST APIs, role-based access, deployment — and NLP model development as a data science intern.",
        )
            ->canonical(route('experience'))
            ->schema($schema->breadcrumbs(['Home' => route('home'), 'Experience' => route('experience')]));

        return view('pages.experience', [
            'seo' => $seo,
            'experiences' => $experiences,
            'relatedProjects' => $experiences->mapWithKeys(fn (Experience $e) => [
                $e->id => $content->projectsBySlugs($e->related_projects ?? []),
            ]),
            'educations' => $content->educations(),
        ]);
    }
}
