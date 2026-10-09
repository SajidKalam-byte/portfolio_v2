<x-layouts.app :seo="$seo">
    @php $resume = $site->resume; @endphp

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop no-print" aria-hidden="true"></div>
        <div class="container-site pb-12 pt-12 sm:pb-14 sm:pt-20">
            <p class="eyebrow enter" style="--i: 0">Resume</p>
            <h1 class="title enter mt-6" style="--i: 1">{{ $site->name() }}</h1>
            <p class="enter mt-3 text-lg text-ink-muted" style="--i: 1">{{ $site->jobTitle() }}</p>
            <p class="enter mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-ink-subtle" style="--i: 2">
                @if ($site->location())<span>{{ $site->location() }}</span>@endif
                @if ($site->email())<a class="link" href="mailto:{{ $site->email() }}">{{ $site->email() }}</a>@endif
                @foreach ($site->socialLinks() as $social)
                    <a class="link" href="{{ $social['url'] }}" rel="me noopener" target="_blank">{{ $social['label'] }}<span class="sr-only"> (opens in a new tab)</span></a>
                @endforeach
            </p>

            @if (session('notice'))
                <div class="alert alert-info mt-8 max-w-2xl" role="alert">
                    <x-icon name="info" class="size-5" />
                    <p>{{ session('notice') }}</p>
                </div>
            @endif

            <div class="enter no-print mt-9 flex flex-wrap items-center gap-3" style="--i: 3">
                @if ($resume->exists())
                    <a href="{{ route('resume.download') }}" class="btn btn-primary" download>
                        <x-icon name="download" class="btn-icon btn-icon-drop" /> Download PDF
                    </a>
                    <p class="mono-label">
                        PDF · {{ $resume->sizeLabel() }}
                        @if ($resume->updatedAt()) · updated {{ $resume->updatedAt()->format('M Y') }} @endif
                    </p>
                @elseif ($resume->externalUrl())
                    <a href="{{ $resume->externalUrl() }}" class="btn btn-primary" rel="noopener" target="_blank">
                        <x-icon name="file" class="btn-icon" /> Open resume
                        <x-icon name="arrow-up-right" class="btn-icon btn-icon-lift" />
                        <span class="sr-only">(opens in a new tab)</span>
                    </a>
                @else
                    <p class="max-w-xl text-[0.9375rem] muted">
                        A downloadable PDF isn’t published yet — the summary below covers the essentials, and I’m happy to send a copy on request.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-secondary">Request a copy</a>
                @endif
            </div>
        </div>
    </header>

    <div class="container-site rule grid gap-12 pb-24 pt-12 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] lg:gap-16">
        <div class="grid content-start gap-14">
            <section aria-labelledby="summary-title">
                <h2 id="summary-title" class="eyebrow">Summary</h2>
                <p class="mt-5 text-lg leading-relaxed muted">
                    Associate Engineer and full-stack web developer building production healthcare software with Python, Django, Django REST Framework, JavaScript, and relational databases. At Corunit Media Corp I work across a production EMR platform — patient intake, clinical documentation, scheduling, billing, authorization forms, ticketing, and administration — with responsibilities that have grown from frontend development into backend engineering, database design, API development, deployment, and production maintenance.
                </p>
                <p class="mt-4 text-lg leading-relaxed muted">
                    I have also led a B2B customer portal from planning to AWS deployment, and bring a foundation in machine learning and NLP from building SMS-spam and hate-speech classifiers. I care about secure, maintainable, user-centred systems and keep deepening my engineering across backend, cloud, and AI-enabled applications.
                </p>
            </section>

            <section aria-labelledby="experience-title">
                <h2 id="experience-title" class="eyebrow">Experience</h2>
                <ol class="mt-6 grid gap-6">
                    @foreach ($experiences as $experience)
                        <li class="card p-6 sm:p-7">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                <h3 class="text-lg font-semibold tracking-tight">{{ $experience->role }}</h3>
                                <p class="mono-label">{{ $experience->periodLabel() }}</p>
                            </div>
                            <p class="text-ink-muted">{{ $experience->company }}</p>
                            <ul class="check-list mt-5 text-[0.9375rem] muted">
                                @foreach (collect($experience->highlightGroups())->flatMap(fn ($g) => $g['items'])->take(6) as $item)
                                    <li><x-icon name="check" class="size-3.5" /><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ol>
                <a href="{{ route('experience') }}" class="link-accent no-print mt-5 inline-flex items-center gap-1.5 text-sm font-medium">
                    Full experience details <x-icon name="arrow-right" class="size-4" />
                </a>
            </section>

            @if ($featuredProjects->isNotEmpty())
                <section aria-labelledby="projects-title">
                    <h2 id="projects-title" class="eyebrow">Selected projects</h2>
                    <ul class="mt-6 grid gap-4">
                        @foreach ($featuredProjects as $project)
                            <li class="card card-interactive relative p-6">
                                <p class="mono-label">{{ $project->categoryLabel() }} · {{ $project->role }}</p>
                                <h3 class="mt-2 font-semibold tracking-tight">
                                    <a href="{{ $project->url() }}" class="stretched-link">{{ $project->title }}</a>
                                </h3>
                                <p class="mt-2 text-[0.9375rem] muted">{{ $project->short_description }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="grid content-start gap-12">
            <section aria-labelledby="education-title">
                <h2 id="education-title" class="eyebrow">Education</h2>
                <ul class="mt-6 grid gap-5">
                    @foreach ($educations as $education)
                        <li>
                            <h3 class="font-semibold tracking-tight">{{ $education->degree }}</h3>
                            <p class="text-[0.9375rem] muted">{{ $education->institution }}</p>
                            <p class="mt-1 flex flex-wrap gap-x-3 mono-label">
                                @if ($education->periodLabel())<span>{{ $education->periodLabel() }}</span>@endif
                                @if ($education->grade)<span>{{ $education->grade }}</span>@endif
                                @if ($education->status && ! $education->isCompleted())<span>{{ $education->status }}</span>@endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section aria-labelledby="skills-title">
                <h2 id="skills-title" class="eyebrow">Skills</h2>
                <dl class="mt-6 grid gap-5">
                    @foreach ($skillGroups as $group)
                        <div>
                            <dt class="text-sm font-medium">{{ $group['label'] }}</dt>
                            <dd class="mt-1.5 text-[0.9375rem] muted">{{ $group['skills']->pluck('name')->implode(', ') }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </aside>
    </div>

</x-layouts.app>
