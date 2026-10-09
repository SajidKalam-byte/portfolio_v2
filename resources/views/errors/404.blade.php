<x-layouts.app :seo="\App\Support\Seo\Seo::make('Page not found', 'The page you were looking for doesn’t exist or has moved.')->noIndex()">
    <section class="hero relative isolate">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site grid min-h-[60vh] content-center py-20">
            <p class="eyebrow enter" style="--i: 0"><span class="eyebrow-index">404</span><span class="eyebrow-rule" aria-hidden="true"></span>Not found</p>
            <h1 class="title enter mt-6 max-w-3xl" style="--i: 1">This page took a <span class="accent-serif">wrong turn.</span></h1>
            <p class="lead enter mt-6 max-w-xl" style="--i: 2">The page you were looking for doesn’t exist, or it has moved. These might help:</p>
            <div class="enter mt-9 flex flex-wrap gap-3" style="--i: 3">
                <a href="{{ route('home') }}" class="btn btn-primary"><x-icon name="home" class="btn-icon" /> Home</a>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Projects</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost">Contact</a>
            </div>
        </div>
    </section>
</x-layouts.app>
