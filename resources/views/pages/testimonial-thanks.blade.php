<x-layouts.app :seo="$seo">
    <section class="relative isolate">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site grid min-h-[60vh] content-center py-20">
            @if (! empty($unavailable))
                <p class="eyebrow"><span class="eyebrow-index">410</span><span class="eyebrow-rule" aria-hidden="true"></span>Link unavailable</p>
                <h1 class="title mt-6 max-w-3xl">This feedback link has expired or was already used.</h1>
                <p class="lead mt-6 max-w-xl">
                    Feedback links are private and work once. If you’d still like to share something, just reply to the message you received{{ $site->email() ? ' or email '.$site->email() : '' }}.
                </p>
            @else
                <p class="eyebrow"><span class="eyebrow-index">✓</span><span class="eyebrow-rule" aria-hidden="true"></span>Received</p>
                <h1 class="title mt-6 max-w-3xl" role="status">
                    Thank you{{ session('testimonial_name') ? ', '.session('testimonial_name') : '' }} — that <span class="accent-serif">means a lot.</span>
                </h1>
                <p class="lead mt-6 max-w-xl">
                    Your feedback has been received. I’ll review it before it appears on the site, and you can ask me to change or remove it at any time.
                </p>
            @endif
            <div class="mt-9">
                <a href="{{ route('home') }}" class="btn btn-secondary">Visit the portfolio <x-icon name="arrow-right" class="btn-icon btn-icon-move" /></a>
            </div>
        </div>
    </section>
</x-layouts.app>
