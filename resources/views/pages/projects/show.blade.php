<x-layouts.app :seo="$seo">
    @php
        /** @var \App\Models\Project $project */
        $toc = array_merge(
            [['id' => 'overview', 'text' => 'Overview']],
            ! empty($project->responsibilities) ? [['id' => 'contributions', 'text' => 'My contributions']] : [],
            $caseStudy['toc'],
            $project->images->isNotEmpty() ? [['id' => 'gallery', 'text' => 'Gallery']] : [],
            $testimonials->isNotEmpty() ? [['id' => 'feedback', 'text' => 'Feedback']] : [],
        );
    @endphp

    <div class="read-progress" data-reading-progress aria-hidden="true"></div>

    {{-- Header ---------------------------------------------------------------- --}}
    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="glow-orb animate-breathe -right-40 -top-40 -z-10 h-[30rem] w-[30rem]" aria-hidden="true"></div>

        <div class="container-site pb-10 pt-10 sm:pt-16">
            <nav aria-label="Breadcrumb" class="enter" style="--i: 0">
                <ol class="flex flex-wrap items-center gap-2 font-mono text-xs text-ink-subtle">
                    <li><a href="{{ route('projects.index') }}" class="link hover:text-ink">Projects</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('projects.index', ['category' => $project->category]) }}" class="link hover:text-ink">{{ $project->categoryLabel() }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-ink-muted">{{ \Illuminate\Support\Str::limit($project->title, 40) }}</li>
                </ol>
            </nav>

            <h1 class="title enter mt-7 max-w-4xl" style="--i: 1">{{ $project->title }}</h1>
            <p class="lead enter mt-6 max-w-3xl" style="--i: 2">{{ $project->short_description }}</p>

            <div class="enter mt-8 flex flex-wrap items-center gap-3" style="--i: 3">
                @if ($project->hasExternalLinks())
                    @if ($project->project_url)
                        <a href="{{ $project->project_url }}" class="btn btn-primary" rel="noopener" target="_blank">
                            {{ $project->project_url_label ?: 'Visit project' }}
                            <x-icon name="arrow-up-right" class="btn-icon btn-icon-lift" />
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    @endif
                    @if ($project->github_url)
                        <a href="{{ $project->github_url }}" class="btn btn-secondary" rel="noopener" target="_blank">
                            <x-icon name="github" class="btn-icon" /> Source on GitHub
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    @endif
                @endif
                @if ($project->is_confidential)
                    <span class="badge"><x-icon name="lock" class="size-3" /> Confidential — details shared on request</span>
                @endif
            </div>
        </div>
    </header>

    {{-- Cover + facts ------------------------------------------------------ --}}
    <div class="container-site">
        @if ($project->coverUrl())
            <figure class="card enter overflow-hidden" style="--i: 3">
                <div class="media-frame aspect-[16/10] sm:aspect-[16/9]">
                    <img src="{{ $project->coverUrl() }}" alt="{{ $project->coverAlt() }}" width="1600" height="1000" fetchpriority="high" decoding="async">
                </div>
                @if (in_array($project->category, ['healthcare', 'business-platform', 'client-work'], true) || $project->is_confidential)
                    <figcaption class="border-t border-line px-5 py-3 text-xs text-ink-subtle">
                        Illustrative artwork. No real screenshots, client data, or patient information are shown.
                    </figcaption>
                @endif
            </figure>
        @endif

        <dl class="card mt-6 grid gap-px overflow-hidden bg-line sm:grid-cols-2 lg:grid-cols-4">
            @foreach (array_filter([
                'My role' => $project->role,
                'Type' => $project->typeLabel().($project->client ? ' · '.$project->client : ''),
                'Timeline' => $project->period,
                'Status' => $project->status,
            ]) as $label => $value)
                <div class="bg-surface p-5">
                    <dt class="mono-label uppercase tracking-[0.1em]">{{ $label }}</dt>
                    <dd class="mt-2 font-medium leading-snug">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($project->techList())
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-baseline sm:gap-5">
                <p class="mono-label shrink-0 uppercase tracking-[0.1em]">Stack</p>
                <x-tech-tags :items="$project->techList()" label="Technology stack" />
            </div>
        @endif

        @if (! empty($project->metrics))
            <section class="mt-10" aria-labelledby="results-title">
                <h2 id="results-title" class="sr-only">Reported results</h2>
                <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($project->metrics as $metric)
                        <li class="card metric">
                            <p class="metric-value">{{ $metric['value'] ?? '' }}</p>
                            <p class="mt-3 font-medium">{{ $metric['label'] ?? '' }}</p>
                            @if (! empty($metric['note']))
                                <p class="mt-1.5 text-sm text-ink-subtle">{{ $metric['note'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    {{-- Body ------------------------------------------------------------------- --}}
    <div class="container-site section-tight grid gap-10 lg:grid-cols-[13.5rem_minmax(0,1fr)] lg:gap-16">
        <aside class="hidden lg:block">
            <nav class="sticky top-28" aria-label="On this page" data-toc>
                <p class="mono-label uppercase tracking-[0.1em]">On this page</p>
                <ol class="mt-4">
                    @foreach ($toc as $entry)
                        <li><a class="toc-link" href="#{{ $entry['id'] }}">{{ $entry['text'] }}</a></li>
                    @endforeach
                </ol>
            </nav>
        </aside>

        <div class="min-w-0">
            <details class="card mb-10 lg:hidden">
                <summary class="flex cursor-pointer items-center justify-between px-5 py-4 font-medium">
                    On this page <x-icon name="chevron-down" class="size-4 text-ink-subtle" />
                </summary>
                <ol class="border-t border-line px-5 py-3">
                    @foreach ($toc as $entry)
                        <li><a class="block py-1.5 text-[0.9375rem] text-ink-muted hover:text-ink" href="#{{ $entry['id'] }}">{{ $entry['text'] }}</a></li>
                    @endforeach
                </ol>
            </details>

            <article class="prose-case" data-reading-article>
                {!! \App\Support\CaseStudyRenderer::numberSections(view('projects.partials.article-body', ['project' => $project, 'caseStudy' => $caseStudy, 'testimonials' => $testimonials])->render()) !!}
            </article>

            @if ($project->is_confidential || $project->category === 'healthcare')
                <aside class="alert alert-info mt-14 max-w-[44rem]" aria-label="Confidentiality">
                    <x-icon name="lock" class="size-4" />
                    <p>
                        This write-up describes my role and the engineering approach at a high level. It contains no patient data, credentials, proprietary code, or confidential business details, and makes no compliance or certification claims.
                    </p>
                </aside>
            @endif
        </div>
    </div>

    {{-- Next / previous ------------------------------------------------------ --}}
    @if ($neighbours['previous'] || $neighbours['next'])
        <nav class="container-site" aria-label="More case studies">
            <div class="grid gap-4 border-t border-line pt-10 sm:grid-cols-2">
                @if ($neighbours['previous'])
                    <a href="{{ $neighbours['previous']->url() }}" class="card card-interactive group p-6">
                        <span class="mono-label inline-flex items-center gap-2"><x-icon name="arrow-left" class="size-3.5 transition-transform group-hover:-translate-x-0.5" /> Previous</span>
                        <span class="mt-3 block font-semibold tracking-tight">{{ $neighbours['previous']->title }}</span>
                    </a>
                @else
                    <span class="hidden sm:block"></span>
                @endif
                @if ($neighbours['next'])
                    <a href="{{ $neighbours['next']->url() }}" class="card card-interactive group p-6 text-right">
                        <span class="mono-label inline-flex items-center gap-2">Next <x-icon name="arrow-right" class="size-3.5 transition-transform group-hover:translate-x-0.5" /></span>
                        <span class="mt-3 block font-semibold tracking-tight">{{ $neighbours['next']->title }}</span>
                    </a>
                @endif
            </div>
        </nav>
    @endif

    {{-- Related ---------------------------------------------------------------- --}}
    @if ($related->isNotEmpty())
        <section class="section-tight" aria-labelledby="related-title">
            <div class="container-site">
                <h2 id="related-title" class="eyebrow">Related projects</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-project-card :project="$item" data-reveal style="--reveal-delay: {{ $loop->index }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-cta-band title="Working on something similar?">
        I’m happy to talk through the problem, the trade-offs, and how I’d approach it.
    </x-cta-band>

</x-layouts.app>
