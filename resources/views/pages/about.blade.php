<x-layouts.app :seo="$seo">

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site pb-16 pt-12 sm:pb-20 sm:pt-20">
            <p class="eyebrow enter" style="--i: 0">About</p>
            <h1 class="display enter mt-6 max-w-5xl text-[clamp(2.5rem,1.4rem+4.4vw,5rem)]" style="--i: 1">
                I follow problems all the way <span class="accent-serif">down</span> the stack.
            </h1>
            <p class="lead enter mt-8 max-w-2xl" style="--i: 2">
                I’m {{ $site->name() }}, a software engineer in {{ config('portfolio.identity.locality') }}, India. I build and maintain production web systems — mostly with Python and Django — and care about software that is secure, maintainable, and genuinely useful to the people who depend on it.
            </p>
        </div>
    </header>

    {{-- Story ---------------------------------------------------------------- --}}
    <section class="section-tight rule" aria-labelledby="story-title">
        <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
            <div data-reveal>
                <x-section-heading index="01" eyebrow="The short version" title-id="story-title">
                    <x-slot:title>From interfaces to infrastructure</x-slot:title>
                </x-section-heading>

                @if ($site->photoUrl())
                    <img src="{{ $site->photoUrl() }}" alt="{{ $site->name() }}" width="960" height="1280" class="portrait mt-8">
                @endif
            </div>
            <div class="space-y-5 text-lg leading-relaxed muted" data-reveal style="--reveal-delay: 1">
                <p>
                    I’m an Associate Engineer at Corunit Media Corp, where I work on a production Electronic Medical Record (EMR) platform — patient intake, clinical documentation including SOAP notes, appointment scheduling, billing and superbills, authorization forms, ticketing, and administration.
                </p>
                <p>
                    I began with frontend work. As I took on more of the product, my responsibilities grew into backend engineering, database design, REST API development with Django REST Framework, authentication and role-based access control, audit logging, deployment, and the day-to-day work of keeping production software healthy: investigating client-reported bugs and shipping fixes.
                </p>
                <p>
                    I’ve also owned a project end to end — planning, designing, building, and deploying a B2B customer portal on AWS, while acting as the client’s technical point of contact. Before that, a data science internship gave me hands-on experience with NLP: SMS spam classification and hate-speech detection with SVM, Logistic Regression, TF-IDF, and SMOTE.
                </p>
                <p>
                    I hold a Master of Computer Applications from Siliguri Institute of Technology, building on a BCA from the University of North Bengal.
                </p>
            </div>
        </div>
    </section>

    {{-- Technical focus ------------------------------------------------------ --}}
    <section class="section-tight rule" aria-labelledby="focus-title">
        <div class="container-site">
            <div data-reveal>
                <x-section-heading index="02" eyebrow="Technical focus" title-id="focus-title">
                    <x-slot:title>What I spend my time on</x-slot:title>
                </x-section-heading>
            </div>

            <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'server', 'title' => 'Backend engineering', 'body' => 'Django applications, REST APIs with Django REST Framework, business logic, and relational data modelling.'],
                    ['icon' => 'shield', 'title' => 'Secure data handling', 'body' => 'Authentication, role-based access control, and audit logging for systems that hold sensitive records.'],
                    ['icon' => 'wrench', 'title' => 'Production operations', 'body' => 'Deployment on AWS with Nginx and Gunicorn, environment configuration, and production debugging.'],
                    ['icon' => 'layers', 'title' => 'Applied ML & NLP', 'body' => 'Supervised text classification — feature extraction, class imbalance, and honest evaluation.'],
                ] as $focus)
                    <li class="card p-6" data-reveal style="--reveal-delay: {{ $loop->index }}">
                        <x-icon :name="$focus['icon']" class="size-5 text-accent-ink" />
                        <h3 class="mt-4 font-semibold tracking-tight">{{ $focus['title'] }}</h3>
                        <p class="mt-2 text-[0.9375rem] muted">{{ $focus['body'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Principles ----------------------------------------------------------- --}}
    <section class="section-tight rule" aria-labelledby="principles-title">
        <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
            <div data-reveal>
                <x-section-heading index="03" eyebrow="Working principles" title-id="principles-title">
                    <x-slot:title>How I like to <span class="accent-serif">work</span></x-slot:title>
                </x-section-heading>
            </div>
            <ol class="grid gap-0">
                @foreach ([
                    ['title' => 'Understand the workflow before the code.', 'body' => 'Clinic staff, customers, and administrators each see the same data differently. Mapping who does what comes before designing models and endpoints.'],
                    ['title' => 'Enforce rules on the server.', 'body' => 'Permissions and validation belong in the backend, so they hold no matter which screen or API a request comes from.'],
                    ['title' => 'Make changes traceable.', 'body' => 'Audit trails, careful migrations, and reversible changes make production systems easier to trust and easier to debug.'],
                    ['title' => 'Stay with it after launch.', 'body' => 'Deployment, watching how features behave in real use, and fixing client-reported issues are part of the job, not an afterthought.'],
                    ['title' => 'Keep learning in depth.', 'body' => 'I’d rather understand one layer properly than skim ten — and then move to the next layer.'],
                ] as $principle)
                    <li class="grid grid-cols-[3rem_1fr] gap-4 border-t border-line py-6 first:border-t-0 first:pt-0 sm:py-7" data-reveal>
                        <span class="font-mono text-sm text-accent-ink">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="text-lg font-semibold tracking-tight">{{ $principle['title'] }}</h3>
                            <p class="mt-1.5 muted">{{ $principle['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Journey -------------------------------------------------------------- --}}
    <section class="section-tight rule" aria-labelledby="journey-title">
        <div class="container-site grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:gap-16">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal>
                <x-section-heading index="04" eyebrow="Learning journey" title-id="journey-title">
                    <x-slot:title>How I got here</x-slot:title>
                </x-section-heading>
            </div>

            <div class="timeline" data-timeline>
                <span class="timeline-progress" aria-hidden="true"></span>
                <ol class="timeline-list">
                    @foreach ([
                        ['when' => '2021 — 2024', 'title' => 'Bachelor of Computer Applications', 'body' => 'University of North Bengal. Graduated with a CGPA of 9.31/10.', 'current' => false],
                        ['when' => 'Apr — May 2024', 'title' => 'Data Science Intern, Personifwy', 'body' => 'Built NLP classifiers for SMS spam (SVM + TF-IDF, tuned with GridSearchCV) and hate speech (Logistic Regression with SMOTE for class imbalance).', 'current' => false],
                        ['when' => '2024', 'title' => 'Smart India Hackathon', 'body' => 'Worked on a concept for AI-assisted complaint management for Rail Madad (problem statement 1711).', 'current' => false],
                        ['when' => 'Mar 2025', 'title' => 'Associate Engineer, Corunit Media Corp', 'body' => 'Joined the team behind a production EMR platform. Scope grew from frontend work into backend engineering, APIs, access control, deployment, and production support.', 'current' => true],
                        ['when' => '2024 — 2026', 'title' => 'Master of Computer Applications', 'body' => 'Completed an MCA at Siliguri Institute of Technology, deepening backend, cloud, and AI engineering skills.', 'current' => false],
                    ] as $step)
                        <li class="timeline-item" data-reveal>
                            <span class="timeline-dot" data-current="{{ $step['current'] ? 'true' : 'false' }}" aria-hidden="true"></span>
                            <p class="mono-label">{{ $step['when'] }}</p>
                            <h3 class="mt-2 text-lg font-semibold tracking-tight">{{ $step['title'] }}</h3>
                            <p class="mt-2 max-w-xl muted">{{ $step['body'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Education + skills summary -------------------------------------------- --}}
    <section class="section-tight rule" aria-labelledby="education-title">
        <div class="container-site grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div data-reveal>
                <h2 id="education-title" class="eyebrow">Education</h2>
                <ul class="mt-6 grid gap-4">
                    @foreach ($educations as $education)
                        <li class="card p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold tracking-tight">{{ $education->degree }}</h3>
                                    <p class="text-[0.9375rem] muted">{{ $education->institution }}</p>
                                </div>
                                @if ($education->status)
                                    <span @class(['badge', 'badge-accent' => ! $education->isCompleted()])>{{ $education->status }}</span>
                                @endif
                            </div>
                            <p class="mt-4 flex flex-wrap gap-x-4 mono-label">
                                @if ($education->periodLabel())<span>{{ $education->periodLabel() }}</span>@endif
                                @if ($education->grade)<span>{{ $education->grade }}</span>@endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div data-reveal style="--reveal-delay: 1">
                <h2 class="eyebrow">Toolbox</h2>
                <dl class="mt-6 grid gap-5">
                    @foreach ($skillGroups as $group)
                        <div>
                            <dt class="text-sm font-medium">{{ $group['label'] }}</dt>
                            <dd class="mt-2"><x-tech-tags :items="$group['skills']->pluck('name')->all()" :label="$group['label']" /></dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    <x-cta-band />

</x-layouts.app>
