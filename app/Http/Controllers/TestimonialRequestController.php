<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestimonialSubmissionRequest;
use App\Mail\TestimonialReceived;
use App\Models\Testimonial;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

/**
 * Private, single-use links for collecting testimonials.
 *
 * The admin creates a request and sends the link to a client. The client's
 * answer is saved as "Needs review" with their consent recorded; nothing is
 * published until the owner approves it in the admin.
 */
class TestimonialRequestController extends Controller
{
    public function show(string $token, Site $site): View|Response
    {
        $testimonial = Testimonial::findOpenRequest($token);

        if ($testimonial === null) {
            return $this->unavailable($site);
        }

        $testimonial->loadMissing('project');

        return view('pages.testimonial-request', [
            'seo' => Seo::make("Share your feedback for {$site->name()}", 'A private form for sharing a testimonial.')->noIndex(),
            'testimonial' => $testimonial,
            'token' => $token,
        ]);
    }

    public function store(TestimonialSubmissionRequest $request, string $token, Site $site): RedirectResponse|Response
    {
        $testimonial = Testimonial::findOpenRequest($token);

        if ($testimonial === null) {
            return $this->unavailable($site);
        }

        $testimonial->fill($request->safe()->only([
            'author_name', 'author_title', 'author_company', 'relationship', 'quote', 'source_url', 'show_name',
        ]));

        // Single use: the link stops working as soon as the answer is saved.
        $testimonial->forceFill([
            'status' => 'received',
            'consent_at' => now(),
            'consent_source' => 'form',
            'submitted_at' => now(),
            'request_token_hash' => null,
            'request_expires_at' => null,
        ])->save();

        $this->notifyOwner($testimonial);

        return to_route('testimonial.thanks')->with('testimonial_name', strtok((string) $testimonial->author_name, ' '));
    }

    public function thanks(Site $site): View
    {
        return view('pages.testimonial-thanks', [
            'seo' => Seo::make('Thank you', "Thank you for sharing feedback with {$site->name()}.")->noIndex(),
        ]);
    }

    private function unavailable(Site $site): Response
    {
        return response()->view('pages.testimonial-thanks', [
            'seo' => Seo::make('Link unavailable', 'This feedback link has expired or was already used.')->noIndex(),
            'unavailable' => true,
        ], 410);
    }

    private function notifyOwner(Testimonial $testimonial): void
    {
        $to = config('portfolio.contact.notify_email');

        if (blank($to) || in_array(config('mail.default'), ['log', 'array'], true)) {
            return;
        }

        try {
            Mail::to($to)->send(new TestimonialReceived($testimonial));
        } catch (Throwable $e) {
            Log::warning('Testimonial notification email failed.', ['testimonial_id' => $testimonial->id, 'error' => $e->getMessage()]);
        }
    }
}
