<x-layouts.app :seo="$seo">
    @php
        /** @var \App\Models\Testimonial $testimonial */
        $project = $testimonial->project;
    @endphp

    <header class="relative isolate overflow-clip">
        <div class="grid-backdrop" aria-hidden="true"></div>
        <div class="container-site pb-10 pt-12 sm:pt-16">
            <p class="eyebrow">Private feedback link</p>
            <h1 class="title mt-6 max-w-3xl">
                @if ($testimonial->author_name)
                    Hi {{ strtok($testimonial->author_name, ' ') }} — thanks for taking <span class="accent-serif">a moment.</span>
                @else
                    Thanks for taking <span class="accent-serif">a moment.</span>
                @endif
            </h1>
            <p class="lead mt-6 max-w-2xl">
                I’d really value a few honest sentences about working together{{ $project ? ' on '.$project->title : '' }}. It takes about two minutes, and nothing is published until I’ve reviewed it — with your permission below.
            </p>
        </div>
    </header>

    <section class="rule pb-24 pt-10" aria-label="Feedback form">
        <div class="container-site grid gap-10 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] lg:gap-14">
            <div class="card p-6 sm:p-8 lg:p-10">
                @if ($errors->any())
                    <div class="alert alert-danger mb-6" role="alert">
                        <x-icon name="alert" class="size-5" />
                        <p class="font-medium">Please check the {{ \Illuminate\Support\Str::plural('field', $errors->count()) }} below.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('testimonial.submit', $token) }}" class="grid gap-6" data-enhanced-form>
                    @csrf

                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-form.input name="author_name" label="Your name" :value="$testimonial->author_name" autocomplete="name" required minlength="2" maxlength="120" />
                        <x-form.select name="relationship" label="How we worked together" :value="$testimonial->relationship ?? 'client'"
                                       :options="\App\Models\Testimonial::RELATIONSHIPS" />
                        <x-form.input name="author_title" label="Your role" :optional="true" autocomplete="organization-title" maxlength="120" placeholder="e.g. Founder, CTO, Project manager" />
                        <x-form.input name="author_company" label="Company" :optional="true" autocomplete="organization" maxlength="120" />
                    </div>

                    <x-form.input name="quote" label="Your testimonial" :textarea="true" :counter="true" rows="7" required minlength="30" maxlength="1200"
                                  hint="What was the problem, how did I help, and what was it like working together? Plain, specific words work best." />

                    <x-form.input name="source_url" type="url" label="LinkedIn profile or recommendation link" :optional="true" maxlength="255"
                                  placeholder="https://www.linkedin.com/in/…" hint="Lets visitors see the testimonial is genuine." />

                    <div class="grid gap-4 border-t border-line pt-6">
                        <x-form.checkbox name="show_name" label="Show my name with the testimonial" :checked="true"
                                         hint="Untick to show only your role and company." />
                        <x-form.checkbox name="consent" label="I agree that this testimonial may be published on {{ $site->name() }}’s portfolio website" :checked="false"
                                         hint="You can ask for it to be changed or removed at any time." />
                        @error('consent')
                            <p class="field-error"><x-icon name="alert" class="mt-0.5 size-4" /><span>{{ $message }}</span></p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary" data-submit>
                            <span class="inline-flex items-center gap-2" data-submit-idle>Send feedback <x-icon name="send" class="btn-icon" /></span>
                            <span class="inline-flex items-center gap-2" data-submit-busy hidden><x-icon name="loader" class="btn-icon spinner" /> Sending…</span>
                        </button>
                    </div>
                </form>
            </div>

            <aside class="grid content-start gap-6">
                <div class="card p-6 sm:p-7">
                    <h2 class="eyebrow">A few prompts, if useful</h2>
                    <ul class="check-list mt-5 text-[0.9375rem] muted">
                        <li><x-icon name="check" class="size-3.5" /><span>What did you need when we started?</span></li>
                        <li><x-icon name="check" class="size-3.5" /><span>What did I build, fix, or take ownership of?</span></li>
                        <li><x-icon name="check" class="size-3.5" /><span>How was communication and reliability?</span></li>
                        <li><x-icon name="check" class="size-3.5" /><span>What changed for you afterwards?</span></li>
                    </ul>
                </div>
                <div class="card p-6 sm:p-7">
                    <h2 class="eyebrow">Your privacy</h2>
                    <p class="mt-4 text-[0.9375rem] muted">
                        This link is private and works once. Your answer is reviewed before anything appears on the site, and your email address is never collected or shown.
                    </p>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
