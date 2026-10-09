<x-layouts.app :seo="\App\Support\Seo\Seo::make('Page expired', 'The form session expired. Please go back and try again.')->noIndex()">
    <section class="relative isolate">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site grid min-h-[60vh] content-center py-20">
            <p class="eyebrow"><span class="eyebrow-index">419</span><span class="eyebrow-rule" aria-hidden="true"></span>Session expired</p>
            <h1 class="title mt-6 max-w-3xl">The form timed out.</h1>
            <p class="lead mt-6 max-w-xl">For your security, forms expire after a while. Nothing was sent — please reload the page and try again.</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('contact') }}" class="btn btn-primary">
                    <x-icon name="arrow-left" class="btn-icon" /> Go back
                </a>
                <a href="{{ route('home') }}" class="btn btn-secondary">Home</a>
            </div>
        </div>
    </section>
</x-layouts.app>
