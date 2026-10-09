<x-layouts.app :seo="$seo">

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="glow-orb animate-breathe -right-32 -top-40 -z-10 h-[30rem] w-[30rem]" aria-hidden="true"></div>
        <div class="container-site pb-12 pt-12 sm:pb-16 sm:pt-20">
            <p class="eyebrow enter" style="--i: 0">Contact</p>
            <h1 class="title enter mt-6 max-w-3xl text-[clamp(2.5rem,1.5rem+4vw,4.75rem)]" style="--i: 1">
                Let’s build something <span class="accent-serif">useful.</span>
            </h1>
            <p class="lead enter mt-7 max-w-2xl" style="--i: 2">
                Have a role, a project, or an idea you’d like to talk through? Send a message — a few lines of context is plenty.
            </p>
        </div>
    </header>

    <section class="rule pb-24 pt-12" aria-label="Contact options">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)] lg:gap-14">

            {{-- Form ---------------------------------------------------------- --}}
            <div class="card p-6 sm:p-8 lg:p-10">
                <h2 class="text-xl font-semibold tracking-tight">Send a message</h2>
                <p class="mt-1.5 text-[0.9375rem] muted">All fields are required unless marked optional.</p>

                @if (session('contact_success'))
                    <div class="alert alert-success mt-6" role="alert">
                        <x-icon name="check-circle" class="size-5" />
                        <p>{{ session('contact_success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mt-6" role="alert">
                        <x-icon name="alert" class="size-5" />
                        <div>
                            @if ($errors->has('form'))
                                <p>{{ $errors->first('form') }}</p>
                            @else
                                <p class="font-medium">Please check the {{ \Illuminate\Support\Str::plural('field', $errors->count()) }} below.</p>
                            @endif
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="mt-8 grid gap-6" data-enhanced-form>
                    @csrf
                    <input type="hidden" name="{{ \App\Http\Requests\ContactRequest::TIMESTAMP }}" value="{{ $formToken }}">

                    {{-- Honeypot: invisible to people, tempting to bots. --}}
                    <div class="hp-field" aria-hidden="true">
                        <label for="{{ \App\Http\Requests\ContactRequest::HONEYPOT }}">Leave this field empty</label>
                        <input type="text" id="{{ \App\Http\Requests\ContactRequest::HONEYPOT }}" name="{{ \App\Http\Requests\ContactRequest::HONEYPOT }}" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-form.input name="name" label="Your name" autocomplete="name" required minlength="2" maxlength="120" />
                        <x-form.input name="email" type="email" label="Email address" autocomplete="email" required maxlength="190" inputmode="email" />
                    </div>

                    <x-form.input name="subject" label="Subject" :optional="true" maxlength="160"
                                  placeholder="e.g. Backend engineer role, API project, collaboration" />

                    <x-form.input name="message" label="Message" :textarea="true" :counter="true" rows="7" required minlength="20" maxlength="5000"
                                  hint="What are you working on, and how could I help?" />

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="max-w-sm text-sm text-ink-subtle">
                            Your message is stored only so I can reply. It isn’t shared or added to any mailing list.
                        </p>
                        <button type="submit" class="btn btn-primary shrink-0" data-submit>
                            <span class="inline-flex items-center gap-2" data-submit-idle>Send message <x-icon name="send" class="btn-icon" /></span>
                            <span class="inline-flex items-center gap-2" data-submit-busy hidden><x-icon name="loader" class="btn-icon spinner" /> Sending…</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Channels & guidance ------------------------------------------- --}}
            <aside class="grid content-start gap-6">
                <div class="card p-6 sm:p-7">
                    <h2 class="eyebrow">Direct</h2>
                    <ul class="mt-5 grid gap-4">
                        @if ($site->email())
                            <li class="flex flex-wrap items-center justify-between gap-3">
                                <a href="mailto:{{ $site->email() }}" class="group flex min-w-0 items-center gap-3">
                                    <span class="inline-grid size-10 shrink-0 place-items-center rounded-xl border border-line bg-surface-2 text-accent-ink"><x-icon name="mail" class="size-[1.125rem]" /></span>
                                    <span class="min-w-0">
                                        <span class="block text-sm text-ink-subtle">Email</span>
                                        <span class="link block truncate font-medium">{{ $site->email() }}</span>
                                    </span>
                                </a>
                                <x-copy-button :value="$site->email()" label="Email address" />
                            </li>
                        @endif
                        @if ($site->phone())
                            <li>
                                <a href="{{ $site->phoneHref() }}" class="group flex items-center gap-3">
                                    <span class="inline-grid size-10 shrink-0 place-items-center rounded-xl border border-line bg-surface-2 text-accent-ink"><x-icon name="phone" class="size-[1.125rem]" /></span>
                                    <span>
                                        <span class="block text-sm text-ink-subtle">Phone</span>
                                        <span class="link font-medium">{{ $site->phone() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif
                        @foreach ($site->socialLinks() as $social)
                            <li>
                                <a href="{{ $social['url'] }}" class="group flex items-center gap-3" rel="me noopener" target="_blank">
                                    <span class="inline-grid size-10 shrink-0 place-items-center rounded-xl border border-line bg-surface-2 text-accent-ink"><x-icon :name="$social['icon']" class="size-[1.125rem]" /></span>
                                    <span>
                                        <span class="block text-sm text-ink-subtle">{{ $social['label'] }}</span>
                                        <span class="link font-medium">{{ $social['handle'] }}</span>
                                        <span class="sr-only">(opens in a new tab)</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                        @if ($site->location())
                            <li class="flex items-center gap-3">
                                <span class="inline-grid size-10 shrink-0 place-items-center rounded-xl border border-line bg-surface-2 text-accent-ink"><x-icon name="clock" class="size-[1.125rem]" /></span>
                                <span>
                                    <span class="block text-sm text-ink-subtle">Based in</span>
                                    <span class="font-medium">{{ $site->location() }}</span>
                                    <span class="block text-sm text-ink-subtle">{{ $site->timezoneLabel() }}</span>
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="card p-6 sm:p-7">
                    <h2 class="eyebrow">Helpful context</h2>
                    <dl class="mt-5 grid gap-5 text-[0.9375rem]">
                        <div>
                            <dt class="font-medium">Hiring</dt>
                            <dd class="mt-1 muted">The team, the stack, and what the role involves day to day.</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Projects & freelance</dt>
                            <dd class="mt-1 muted">The problem, what already exists, and a rough timeline.</dd>
                        </div>
                        <div>
                            <dt class="font-medium">Collaboration</dt>
                            <dd class="mt-1 muted">What you’re building and where you’d like another engineer’s help.</dd>
                        </div>
                    </dl>
                </div>

                @if ($testimonial)
                    <section aria-label="Testimonial">
                        <x-testimonial-card :testimonial="$testimonial" size="small" />
                    </section>
                @endif
            </aside>
        </div>
    </section>

</x-layouts.app>
