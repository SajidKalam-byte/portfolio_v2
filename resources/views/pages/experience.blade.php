<x-layouts.app :seo="$seo">

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site pb-14 pt-12 sm:pb-20 sm:pt-20">
            <p class="eyebrow enter" style="--i: 0">Experience</p>
            <h1 class="title enter mt-6 max-w-4xl text-[clamp(2.5rem,1.5rem+4vw,4.75rem)]" style="--i: 1">
                Building software that people <span class="accent-serif">rely on</span> every day.
            </h1>
            <p class="lead enter mt-7 max-w-2xl" style="--i: 2">
                Production healthcare software, end-to-end client delivery, and applied machine learning — with the responsibilities, technologies, and engineering details behind each role.
            </p>
        </div>
    </header>

    <section class="section-tight rule" aria-label="Roles">
        <div class="container-site">
            @if ($experiences->isEmpty())
                <p class="muted">Experience details are coming soon.</p>
            @else
                <div class="timeline" data-timeline>
                    <span class="timeline-progress" aria-hidden="true"></span>
                    <ol class="timeline-list gap-16 sm:gap-20">
                        @foreach ($experiences as $experience)
                            <li class="timeline-item" data-reveal>
                                <span class="timeline-dot" data-current="{{ $experience->is_current ? 'true' : 'false' }}" aria-hidden="true"></span>

                                <article class="grid gap-8 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:gap-14" aria-labelledby="role-{{ $experience->id }}">
                                    <header class="lg:sticky lg:top-28 lg:self-start">
                                        <p class="mono-label">
                                            <time datetime="{{ $experience->isoRange()['start'] }}">{{ $experience->start_date->format('M Y') }}</time>
                                            —
                                            @if ($experience->is_current)
                                                Present
                                            @else
                                                <time datetime="{{ $experience->isoRange()['end'] }}">{{ $experience->end_date?->format('M Y') }}</time>
                                            @endif
                                        </p>
                                        <h2 id="role-{{ $experience->id }}" class="mt-3 text-2xl font-semibold leading-tight tracking-tight sm:text-3xl">{{ $experience->role }}</h2>
                                        <p class="mt-2 text-lg text-ink-muted">
                                            @if ($experience->company_url)
                                                <a class="link" href="{{ $experience->company_url }}" rel="noopener" target="_blank">{{ $experience->company }}<span class="sr-only"> (opens in a new tab)</span></a>
                                            @else
                                                {{ $experience->company }}
                                            @endif
                                        </p>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @if ($experience->is_current)<span class="badge badge-success">Current</span>@endif
                                            @if ($experience->employment_type)<span class="badge">{{ $experience->employment_type }}</span>@endif
                                            @if ($experience->location)<span class="badge">{{ $experience->location }}</span>@endif
                                        </div>

                                        @if ($relatedProjects[$experience->id]->isNotEmpty())
                                            <div class="mt-8">
                                                <p class="mono-label uppercase tracking-[0.1em]">Case studies</p>
                                                <ul class="mt-3 grid gap-2">
                                                    @foreach ($relatedProjects[$experience->id] as $project)
                                                        <li>
                                                            <a href="{{ $project->url() }}" class="group inline-flex items-center gap-2 font-medium text-accent-ink">
                                                                <span class="link">{{ $project->title }}</span>
                                                                <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-0.5" />
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </header>

                                    <div>
                                        <p class="text-lg leading-relaxed muted">{{ $experience->description }}</p>

                                        @foreach ($experience->highlightGroups() as $group)
                                            <section class="mt-9" @if ($group['title']) aria-label="{{ $group['title'] }}" @endif>
                                                @if ($group['title'])
                                                    <h3 class="eyebrow">{{ $group['title'] }}</h3>
                                                @endif
                                                <ul class="check-list mt-4 text-[0.9375rem] muted">
                                                    @foreach ($group['items'] as $item)
                                                        <li><x-icon name="check" class="size-3.5" /><span>{{ $item }}</span></li>
                                                    @endforeach
                                                </ul>
                                            </section>
                                        @endforeach

                                        @if (! empty($experience->tech_stack))
                                            <div class="mt-9 border-t border-line pt-6">
                                                <h3 class="mono-label uppercase tracking-[0.1em]">Technologies</h3>
                                                <x-tech-tags :items="$experience->tech_stack" class="mt-3" />
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </section>

    @if ($educations->isNotEmpty())
        <section class="section-tight rule" aria-labelledby="education-title">
            <div class="container-site">
                <h2 id="education-title" class="eyebrow">Education</h2>
                <ul class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($educations as $education)
                        <li class="card flex flex-wrap items-start justify-between gap-4 p-6" data-reveal style="--reveal-delay: {{ $loop->index }}">
                            <div>
                                <h3 class="font-semibold tracking-tight">{{ $education->degree }}</h3>
                                <p class="text-[0.9375rem] muted">{{ $education->institution }}</p>
                                <p class="mt-3 flex flex-wrap gap-x-4 mono-label">
                                    @if ($education->periodLabel())<span>{{ $education->periodLabel() }}</span>@endif
                                    @if ($education->grade)<span>{{ $education->grade }}</span>@endif
                                </p>
                            </div>
                            @if ($education->status)
                                <span @class(['badge', 'badge-accent' => ! $education->isCompleted()])>{{ $education->status }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-cta-band title="Have a role or project in mind?" />

</x-layouts.app>
