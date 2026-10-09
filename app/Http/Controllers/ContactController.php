<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Services\PortfolioContent;
use App\Support\Seo\Schema;
use App\Support\Seo\Seo;
use App\Support\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(Schema $schema, Site $site, PortfolioContent $content): View
    {
        $seo = Seo::make(
            'Contact',
            "Get in touch with {$site->name()} about backend or full-stack engineering roles, freelance projects, or collaborations.",
        )
            ->canonical(route('contact'))
            ->schema($schema->breadcrumbs(['Home' => route('home'), 'Contact' => route('contact')]));

        return view('pages.contact', [
            'seo' => $seo,
            'formToken' => Crypt::encryptString((string) now()->timestamp),
            'testimonial' => $content->featuredTestimonials(1)->first(),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $firstName = Str::of($request->validated('name'))->before(' ')->limit(40, '')->toString();
        $success = "Thanks, {$firstName} — your message has been received. I’ll reply to {$request->validated('email')}.";

        // Pretend to succeed so bots get no signal, but store nothing.
        if ($request->looksAutomated()) {
            return to_route('contact')->with('contact_success', $success);
        }

        $key = 'contact-form:'.sha1((string) $request->ip());
        $maxPerHour = config('portfolio.contact.max_per_hour', 5);

        if (RateLimiter::tooManyAttempts($key, $maxPerHour)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

            return back()
                ->withInput()
                ->withErrors(['form' => "You’ve sent several messages in a short time. Please try again in about {$minutes} minute(s), or reach me by email."]);
        }

        RateLimiter::hit($key, 3600);

        $message = ContactMessage::create([
            ...$request->safe()->only(['name', 'email', 'subject', 'message']),
            'status' => 'pending',
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        $this->notifyOwner($message);

        return to_route('contact')->with('contact_success', $success);
    }

    /**
     * Email the site owner when SMTP is configured. The message is already
     * stored, so a mail failure is logged (without message content) and the
     * visitor still sees an accurate "received" confirmation.
     */
    private function notifyOwner(ContactMessage $message): void
    {
        $to = config('portfolio.contact.notify_email');

        if (blank($to) || in_array(config('mail.default'), ['log', 'array'], true)) {
            return;
        }

        try {
            Mail::to($to)->send(new ContactMessageReceived($message));
        } catch (Throwable $e) {
            Log::warning('Contact notification email failed.', [
                'contact_message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
