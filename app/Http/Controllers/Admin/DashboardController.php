<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use App\Support\ResumeFile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ResumeFile $resume): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'projects' => Project::count(),
                'published' => Project::published()->count(),
                'skills' => Skill::count(),
                'experiences' => Experience::count(),
                'educations' => Education::count(),
                'unread' => ContactMessage::where('status', 'pending')->count(),
                'testimonials' => Testimonial::published()->count(),
                'testimonialsToReview' => Testimonial::where('status', 'received')->count(),
            ],
            'recentMessages' => ContactMessage::latest()->limit(5)->get(),
            'checks' => $this->healthChecks($resume),
        ]);
    }

    /**
     * Deployment sanity checks worth surfacing on shared hosting.
     *
     * @return list<array{label: string, ok: bool, hint: string}>
     */
    private function healthChecks(ResumeFile $resume): array
    {
        $appUrl = (string) config('app.url');

        return [
            [
                'label' => 'Running in production mode',
                'ok' => app()->isProduction(),
                'hint' => 'Set APP_ENV=production so search engines may index the site.',
            ],
            [
                'label' => 'Debug mode is off',
                'ok' => ! config('app.debug'),
                'hint' => 'Set APP_DEBUG=false — debug pages expose configuration.',
            ],
            [
                'label' => 'APP_URL uses HTTPS',
                'ok' => str_starts_with($appUrl, 'https://'),
                'hint' => 'Enable SSL in cPanel, then set APP_URL=https://your-domain.',
            ],
            [
                'label' => 'Resume PDF is available',
                'ok' => $resume->available(),
                'hint' => 'Upload a PDF in Settings, or set PORTFOLIO_RESUME_URL.',
            ],
            [
                'label' => 'Contact notifications can be emailed',
                'ok' => filled(config('portfolio.contact.notify_email'))
                    && ! in_array(config('mail.default'), ['log', 'array'], true),
                'hint' => 'Messages are always stored here. To also get emails, configure MAIL_* and PORTFOLIO_NOTIFY_EMAIL.',
            ],
            [
                'label' => 'Configuration is cached',
                'ok' => app()->configurationIsCached(),
                'hint' => 'Optional: use “Optimize” below after changing .env for slightly faster responses.',
            ],
        ];
    }
}
