<?php

namespace App\Http\Controllers;

use App\Services\PortfolioContent;
use App\Support\ResumeFile;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResumeController extends Controller
{
    public function show(PortfolioContent $content, Schema $schema, Site $site): View
    {
        $seo = Seo::make(
            'Resume',
            "Resume of {$site->name()}: Associate Engineer and full-stack web developer — Python, Django, Django REST Framework, JavaScript, PostgreSQL, and AWS.",
        )
            ->canonical(route('resume'))
            ->schema($schema->breadcrumbs(['Home' => route('home'), 'Resume' => route('resume')]));

        return view('pages.resume', [
            'seo' => $seo,
            'experiences' => $content->experiences(),
            'educations' => $content->educations(),
            'skillGroups' => $content->skillGroups(),
            'featuredProjects' => $content->featuredProjects(),
        ]);
    }

    public function download(ResumeFile $resume): BinaryFileResponse|RedirectResponse
    {
        $path = $resume->absolutePath();

        if ($path === null) {
            if ($external = $resume->externalUrl()) {
                return redirect()->away($external);
            }

            return to_route('resume')->with('notice', 'The PDF resume isn’t available for download right now. The summary below covers the essentials, and I’m happy to send a copy on request.');
        }

        return response()->download($path, $resume->downloadName(), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
