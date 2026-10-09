<x-layouts.app :seo="$seo">
    @php
        // Sections are conditional (e.g. testimonials appear once one is published), so number only those that render.
        $sectionNumbers = collect([
            'intro' => true,
            'skills' => $skillGroups->isNotEmpty(),
            'featured' => (bool) $leadProject,
            'more' => $otherProjects->isNotEmpty(),
            'testimonials' => $testimonials->isNotEmpty(),
            'experience' => $experiences->isNotEmpty(),
            'education' => $educations->isNotEmpty(),
        ])->filter()->keys()->mapWithKeys(fn ($key, $i) => [$key => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)]);
    @endphp

    {{-- Hero ------------------------------------------------------------ --}}
    <section class="hero" data-hero aria-labelledby="hero-title">
        <div class="grid-backdrop animate-drift" aria-hidden="true"></div>
        <div class="glow-orb animate-breathe -right-[12%] -top-48 -z-10 h-[40rem] w-[40rem]" aria-hidden="true"></div>
        <div class="hero-spotlight" aria-hidden="true"></div>

        <div class="container-site grid items-center gap-14 pb-20 pt-10 sm:pt-16 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.85fr)] lg:gap-12 lg:pb-28 lg:pt-20">
            <div>
                @if ($site->statusText())
                    <p class="status-pill enter" style="--i: 0">
                        <span class="status-dot" aria-hidden="true"></span>
                        <span>
                            {{ $site->statusText() }}
                            @if ($site->openToWork() && $site->openToWorkText())
                                <span class="text-ink-subtle" aria-hidden="true"> · </span><span class="sr-only">. </span><span class="text-accent-ink">{{ $site->openToWorkText() }}</span>
                            @endif
                        </span>
                    </p>
                @endif

                <h1 id="hero-title" class="mt-8">
                    <span class="enter block font-mono text-sm tracking-wide text-ink-muted" style="--i: 1">{{ $site->name() }} — {{ $site->shortTitle() }}</span>
                    <span class="display enter mt-5 block" style="--i: 2">Backend thinking.<br> Full-stack <span class="accent-serif">execution.</span></span>
                </h1>

                <p class="lead enter mt-8 max-w-[37rem]" style="--i: 3">
                    I build and run production web systems with Python, Django, and REST APIs — from clinical workflows in a healthcare EMR platform to a B2B customer portal I took from requirements to AWS deployment.
                </p>

                <div class="enter mt-10 flex flex-wrap items-center gap-3" style="--i: 4">
                    <a href="{{ route('projects.index') }}" class="btn btn-primary">
                        View projects <x-icon name="arrow-right" class="btn-icon btn-icon-move" />
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-secondary">Contact me</a>
                    @if ($site->resume->downloadUrl())
                        <a href="{{ $site->resume->downloadUrl() }}" class="btn btn-ghost" @if ($site->resume->exists()) download @else rel="noopener" target="_blank" @endif>
                            Download resume <x-icon name="download" class="btn-icon btn-icon-drop" />
                        </a>
                    @else
                        <a href="{{ route('resume') }}" class="btn btn-ghost">
                            Resume <x-icon name="arrow-right" class="btn-icon btn-icon-move" />
                        </a>
                    @endif
                </div>

                <ul class="stack-line enter mt-14" style="--i: 5" aria-label="Core stack">
                    <li>Python</li>
                    <li>Django</li>
                    <li>DRF</li>
                    <li>PostgreSQL</li>
                    <li>JavaScript</li>
                    <li>AWS</li>
                </ul>
            </div>

            <x-hero-terminal class="enter" style="--i: 3" />
        </div>
    </section>

    {{-- Introduction ------------------------------------------------ --}}
    <section class="section rule" aria-labelledby="intro-title">
        <div class="container-site">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)] lg:gap-16">
                <div data-reveal>
                    <x-section-heading :index="$sectionNumbers['intro']" eyebrow="Introduction" title-id="intro-title">
                        <x-slot:title>I follow problems all the way <span class="accent-serif">down</span> the stack.</x-slot:title>
                    </x-section-heading>
                </div>

                <div class="space-y-5 text-lg leading-relaxed muted lg:pt-12" data-reveal style="--reveal-delay: 1">
                    <p>
                        I started on the frontend and kept following the problems downward — into data models, APIs, access control, and the servers that keep everything running. Today my work spans backend engineering, database design, API development, deployment, and production maintenance.
                    </p>
                    <p>
                        At Corunit Media Corp I work on a production Electronic Medical Record platform: patient intake, clinical documentation, scheduling, billing, and the administrative tooling around them. I’ve also led a B2B customer portal end to end, and I bring a grounding in machine learning and NLP from a data science internship.
                    </p>
                    <a href="{{ route('about') }}" class="link-accent inline-flex items-center gap-1.5 text-base font-medium">
                        More about how I work <x-icon name="arrow-right" class="size-4" />
                    </a>
                </div>
            </div>

            <ul class="mt-16 grid gap-4 md:grid-cols-3" aria-label="Working principles">
                @foreach ([
                    ['icon' => 'workflow', 'title' => 'Understand the workflow first', 'body' => 'Software for clinics and businesses only works when it matches how people actually work. I start with the workflow, then shape the data model around it.'],
                    ['icon' => 'server', 'title' => 'Own it through production', 'body' => 'Shipping is the midpoint. I stay with features through deployment, debugging, and client-reported issues until they hold up in real use.'],
                    ['icon' => 'shield', 'title' => 'Treat data with care', 'body' => 'Role-based access, audit trails, and careful handling of sensitive records are defaults in how I design — not something bolted on later.'],
                ] as $principle)
                    <li class="card p-6 sm:p-7" data-reveal style="--reveal-delay: {{ $loop->index }}">
                        <span class="inline-grid size-10 place-items-center rounded-xl border border-line bg-surface-2 text-accent-ink">
                            <x-icon :name="$principle['icon']" class="size-5" />
                        </span>
                        <h3 class="mt-5 text-lg font-semibold tracking-tight">{{ $principle['title'] }}</h3>
                        <p class="mt-2 text-[0.9375rem] muted">{{ $principle['body'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Skills ------------------------------------------------------ --}}
    @if ($skillGroups->isNotEmpty())
        <section id="skills" class="section rule" aria-labelledby="skills-title">
            <div class="container-site">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div data-reveal>
                        <x-section-heading :index="$sectionNumbers['skills']" eyebrow="Technical skills" title-id="skills-title">
                            <x-slot:title>Tools I reach for</x-slot:title>
                            No proficiency bars or percentages — the case studies show how each tool was actually used.
                        </x-section-heading>
                    </div>

                    <div class="flex flex-wrap gap-2 lg:max-w-md lg:justify-end" data-filter-group="skills-grid" role="group" aria-label="Highlight a skill category" hidden>
                        <button type="button" class="chip" data-filter="all" aria-pressed="true">All</button>
                        @foreach ($skillGroups as $group)
                            <button type="button" class="chip" data-filter="{{ $group['key'] }}" aria-pressed="false">{{ $group['label'] }}</button>
                        @endforeach
                    </div>
                </div>

                <div id="skills-grid" class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($skillGroups as $group)
                        <article class="card skill-card" data-filter-item="{{ $group['key'] }}" data-reveal style="--reveal-delay: {{ $loop->index % 3 }}">
                            <div class="flex items-baseline justify-between gap-4">
                                <h3 class="text-lg font-semibold tracking-tight">{{ $group['label'] }}</h3>
                                <span class="mono-label">{{ str_pad((string) $group['skills']->count(), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <p class="mt-1.5 text-sm text-ink-subtle">{{ $group['description'] }}</p>
                            <ul class="mt-5 flex flex-wrap gap-1.5" aria-label="{{ $group['label'] }}">
                                @foreach ($group['skills'] as $skill)
                                    <li class="tag" @if ($skill->description) title="{{ $skill->description }}" @endif>{{ $skill->name }}</li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach

                    <article class="card skill-card border-dashed bg-transparent" data-filter-item="direction" data-reveal style="--reveal-delay: 2">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="compass" class="size-5 text-accent-ink" />
                            <h3 class="text-lg font-semibold tracking-tight">Where I’m heading</h3>
                        </div>
                        <p class="mt-1.5 text-sm text-ink-subtle">Areas I’m actively deepening.</p>
                        <ul class="mt-5 grid gap-2 text-[0.9375rem] muted">
                            <li>Backend & platform engineering</li>
                            <li>Cloud engineering on AWS</li>
                            <li>AI-enabled applications</li>
                            <li>Full-stack product engineering</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>
    @endif

    {{-- Featured case studies --------------------------------------- --}}
    @if ($leadProject)
        <section class="section rule" aria-labelledby="featured-title">
            <div class="container-site">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between" data-reveal>
                    <x-section-heading :index="$sectionNumbers['featured']" eyebrow="Featured work" title-id="featured-title">
                        <x-slot:title>Production software, <span class="accent-serif">start to finish</span></x-slot:title>
                    </x-section-heading>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary shrink-0">
                        All projects <x-icon name="arrow-right" class="btn-icon btn-icon-move" />
                    </a>
                </div>

                <div class="mt-12 grid gap-6">
                    <x-feature-project :project="$leadProject" :testimonial="$projectQuotes->get($leadProject->id)" data-reveal />
                    @foreach ($featuredProjects as $project)
                        <x-feature-project :project="$project" :testimonial="$projectQuotes->get($project->id)" :reverse="$loop->odd" label="Case study" data-reveal />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- More projects --------------------------------------------- --}}
    @if ($otherProjects->isNotEmpty())
        <section class="section rule" aria-labelledby="more-work-title">
            <div class="container-site">
                <div data-reveal>
                    <x-section-heading :index="$sectionNumbers['more']" eyebrow="More projects" title-id="more-work-title">
                        <x-slot:title>Machine learning, web apps, and experiments</x-slot:title>
                        Internship NLP models, personal builds, academic work, and a hackathon concept — each written up with what it does and where its limits are.
                    </x-section-heading>
                </div>

                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($otherProjects as $project)
                        <x-project-card :project="$project" data-reveal style="--reveal-delay: {{ $loop->index % 3 }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Testimonials (only real, published ones; hidden until the first is approved) -- --}}
    @if ($testimonials->isNotEmpty())
        <section class="section rule" aria-labelledby="testimonials-title">
            <div class="container-site">
                @if ($testimonials->count() === 1)
                    <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
                        <div data-reveal>
                            <x-section-heading :index="$sectionNumbers['testimonials']" eyebrow="Testimonials" title-id="testimonials-title">
                                <x-slot:title>In their <span class="accent-serif">words</span></x-slot:title>
                                What it’s like to work with me, from the people I’ve built with.
                            </x-section-heading>
                        </div>
                        <x-testimonial-card :testimonial="$testimonials->first()" size="large" data-reveal style="--reveal-delay: 1" />
                    </div>
                @else
                    <div data-reveal>
                        <x-section-heading :index="$sectionNumbers['testimonials']" eyebrow="Testimonials" title-id="testimonials-title">
                            <x-slot:title>In their <span class="accent-serif">words</span></x-slot:title>
                            What it’s like to work with me, from the people I’ve built with.
                        </x-section-heading>
                    </div>
                    <div class="testimonial-columns mt-12" data-count="{{ $testimonials->count() }}">
                        @foreach ($testimonials as $testimonial)
                            <x-testimonial-card :testimonial="$testimonial" data-reveal style="--reveal-delay: {{ $loop->index % 3 }}" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Experience ---------------------------------------------------- --}}
    @if ($experiences->isNotEmpty())
        <section class="section rule" aria-labelledby="experience-title">
            <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] lg:gap-16">
                <div class="lg:sticky lg:top-28 lg:self-start" data-reveal>
                    <x-section-heading :index="$sectionNumbers['experience']" eyebrow="Experience" title-id="experience-title">
                        <x-slot:title>Where I’ve been building</x-slot:title>
                    </x-section-heading>
                    <a href="{{ route('experience') }}" class="btn btn-secondary mt-8">
                        Full experience <x-icon name="arrow-right" class="btn-icon btn-icon-move" />
                    </a>
                </div>

                <div class="timeline" data-timeline>
                    <span class="timeline-progress" aria-hidden="true"></span>
                    <ol class="timeline-list">
                    @foreach ($experiences as $experience)
                        <li class="timeline-item" data-reveal>
                            <span class="timeline-dot" data-current="{{ $experience->is_current ? 'true' : 'false' }}" aria-hidden="true"></span>
                            <p class="mono-label">
                                <time datetime="{{ $experience->isoRange()['start'] }}">{{ $experience->start_date->format('M Y') }}</time>
                                —
                                @if ($experience->is_current)
                                    Present
                                @else
                                    <time datetime="{{ $experience->isoRange()['end'] }}">{{ $experience->end_date?->format('M Y') }}</time>
                                @endif
                            </p>
                            <h3 class="mt-2 text-xl font-semibold tracking-tight">{{ $experience->role }}</h3>
                            <p class="mt-1 text-ink-muted">{{ $experience->company }}</p>
                            <p class="mt-4 max-w-2xl text-[0.9375rem] muted">{{ $experience->description }}</p>
                            <x-tech-tags :items="$experience->tech_stack ?? []" :limit="6" class="mt-5" />
                        </li>
                    @endforeach
                    </ol>
                </div>
            </div>
        </section>
    @endif

    {{-- Education & learning ------------------------------------------ --}}
    @if ($educations->isNotEmpty())
        <section class="section rule" aria-labelledby="education-title">
            <div class="container-site">
                <div data-reveal>
                    <x-section-heading :index="$sectionNumbers['education']" eyebrow="Education & learning" title-id="education-title">
                        <x-slot:title>Formal study, <span class="accent-serif">continuous</span> practice</x-slot:title>
                    </x-section-heading>
                </div>

                <div class="mt-12 grid gap-4 md:grid-cols-3">
                    @foreach ($educations as $education)
                        <article class="card flex flex-col p-6 sm:p-7" data-reveal style="--reveal-delay: {{ $loop->index }}">
                            <div class="flex items-center justify-between gap-3">
                                <x-icon name="graduation" class="size-5 text-accent-ink" />
                                @if ($education->status)
                                    <span @class(['badge', 'badge-accent' => ! $education->isCompleted()])>{{ $education->status }}</span>
                                @endif
                            </div>
                            <h3 class="mt-5 text-lg font-semibold tracking-tight">{{ $education->degree }}</h3>
                            @if ($education->field_of_study)
                                <p class="text-ink-muted">{{ $education->field_of_study }}</p>
                            @endif
                            <p class="mt-1 text-[0.9375rem] muted">{{ $education->institution }}</p>
                            <div class="mt-auto flex flex-wrap gap-x-4 gap-y-1 pt-5 mono-label">
                                @if ($education->periodLabel())<span>{{ $education->periodLabel() }}</span>@endif
                                @if ($education->grade)<span>{{ $education->grade }}</span>@endif
                            </div>
                        </article>
                    @endforeach

                    @if ($site->githubUrl())
                        <article class="card flex flex-col border-dashed bg-transparent p-6 sm:p-7" data-reveal style="--reveal-delay: 2">
                            <x-icon name="github" class="size-5 text-accent-ink" />
                            <h3 class="mt-5 text-lg font-semibold tracking-tight">Learning in the open</h3>
                            <p class="mt-1 text-[0.9375rem] muted">Practice problems, course work, and small experiments live on GitHub alongside my projects.</p>
                            <a href="{{ $site->githubUrl() }}" class="link-accent mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-medium" rel="me noopener" target="_blank">
                                View GitHub profile <x-icon name="arrow-up-right" class="size-4" /><span class="sr-only">(opens in a new tab)</span>
                            </a>
                        </article>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <x-cta-band />

</x-layouts.app>
