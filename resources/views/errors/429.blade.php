<x-layouts.app :seo="\App\Support\Seo\Seo::make('Too many requests', 'Please wait a moment before trying again.')->noIndex()">
    <section class="relative isolate">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site grid min-h-[60vh] content-center py-20">
            <p class="eyebrow"><span class="eyebrow-index">429</span><span class="eyebrow-rule" aria-hidden="true"></span>Slow down</p>
            <h1 class="title mt-6 max-w-3xl">Too many requests.</h1>
            <p class="lead mt-6 max-w-xl">
                You’ve made several requests in a short time. Please wait a minute and try again.
                @if ($site->email())
                    You can also email <a class="link-accent" href="mailto:{{ $site->email() }}">{{ $site->email() }}</a>.
                @endif
            </p>
            <div class="mt-9">
                <a href="{{ route('home') }}" class="btn btn-secondary">Back to home</a>
            </div>
        </div>
    </section>
</x-layouts.app>
