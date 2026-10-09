<?php

namespace App\Http\Controllers;

use App\Services\PortfolioContent;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PortfolioContent $content, Schema $schema, Site $site): View
    {
        $featured = $content->featuredProjects();

        $seo = Seo::make(
            "{$site->name()} — Software Engineer · Python, Django & Full-Stack",
            $site->description(),
        )
            ->canonical(route('home'))
            ->type('profile')
            ->schema(
                $schema->website(),
                $schema->person($content->skillNames(), $content->educations()),
            );

        return view('pages.home', [
            'seo' => $seo,
            'leadProject' => $featured->first(),
            'featuredProjects' => $featured->slice(1)->values(),
            'otherProjects' => $content->otherProjects(6),
            'skillGroups' => $content->skillGroups(),
            'experiences' => $content->experiences(),
            'educations' => $content->educations(),
            'testimonials' => $content->featuredTestimonials(6),
            'projectQuotes' => $content->testimonialHighlights(),
        ]);
    }
}
