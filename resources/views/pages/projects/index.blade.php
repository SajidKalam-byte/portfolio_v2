<x-layouts.app :seo="$seo">

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site pb-12 pt-12 sm:pb-16 sm:pt-20">
            <p class="eyebrow enter" style="--i: 0">
                Projects
                <span class="eyebrow-rule" aria-hidden="true"></span>
                <span>{{ str_pad((string) $totalCount, 2, '0', STR_PAD_LEFT) }} case studies</span>
            </p>
            <h1 class="title enter mt-6 max-w-4xl text-[clamp(2.5rem,1.5rem+4vw,4.75rem)]" style="--i: 1">
                Problems, decisions, and <span class="accent-serif">the systems</span> that came out of them.
            </h1>
            <p class="lead enter mt-7 max-w-2xl" style="--i: 2">
                Each project is written up like an engineering review: the problem, my role, the architecture, and the trade-offs — including limits and what I’d do differently.
            </p>
        </div>
    </header>

    <section class="rule pb-24 pt-10 sm:pt-12" aria-label="Project list">
        <div class="container-site">
            @if (count($categories) > 1)
                <nav aria-label="Filter projects by category" class="-mx-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0">
                    <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                        <li>
                            <a href="{{ route('projects.index') }}" class="chip" @if (! $activeCategory) aria-current="page" @endif>
                                All <span class="chip-count">{{ $totalCount }}</span>
                            </a>
                        </li>
                        @foreach ($categories as $key => $count)
                            <li>
                                <a href="{{ route('projects.index', ['category' => $key]) }}" class="chip" @if ($activeCategory === $key) aria-current="page" @endif>
                                    {{ config("portfolio.project_categories.{$key}") }} <span class="chip-count">{{ $count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

            @if ($lead)
                <div class="mt-10">
                    <x-feature-project :project="$lead" data-reveal />
                </div>
            @endif

            @if ($projects->isEmpty() && ! $lead)
                <div class="card mt-10 p-10 text-center">
                    <p class="muted">No projects in this category yet.</p>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary mt-6">Show all projects</a>
                </div>
            @else
                <h2 class="sr-only">
                    {{ $activeCategory ? config("portfolio.project_categories.{$activeCategory}").' projects' : 'All projects' }}
                </h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-project-card :project="$project" :eager="$loop->index < 3 && ! $lead" data-reveal style="--reveal-delay: {{ $loop->index % 3 }}" />
                    @endforeach
                </div>

                {{ $projects->links('pagination.portfolio') }}
            @endif
        </div>
    </section>

    <x-cta-band title="Have a problem worth solving?">
        If one of these projects looks close to what you need — or you’d like to hear about the private work I can’t publish — let’s talk.
    </x-cta-band>

</x-layouts.app>
