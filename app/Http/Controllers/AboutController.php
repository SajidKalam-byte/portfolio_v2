<?php

namespace App\Http\Controllers;

use App\Services\PortfolioContent;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(PortfolioContent $content, Schema $schema, Site $site): View
    {
        $person = $schema->person($content->skillNames(), $content->educations());

        $seo = Seo::make(
            "About {$site->name()}",
            "The story, working principles, and technical focus of {$site->name()} — a software engineer who moved from frontend work into backend engineering, APIs, deployment, and applied machine learning.",
        )
            ->canonical(route('about'))
            ->type('profile')
            ->schema(
                $schema->profilePage($person, route('about')),
                $schema->breadcrumbs(['Home' => route('home'), 'About' => route('about')]),
            );

        return view('pages.about', [
            'seo' => $seo,
            'skillGroups' => $content->skillGroups(),
            'experiences' => $content->experiences(),
            'educations' => $content->educations(),
        ]);
    }
}
