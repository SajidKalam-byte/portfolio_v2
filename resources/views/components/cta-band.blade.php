@props(['title' => 'Let’s build something useful.'])

<section {{ $attributes->class('section-tight') }} aria-labelledby="cta-title">
    <div class="container-site">
        <div class="card relative isolate overflow-hidden px-6 py-14 sm:px-12 sm:py-20" data-reveal>
            <div class="grid-backdrop" aria-hidden="true"></div>
            <div class="glow-orb animate-breathe -right-24 -top-24 -z-10 h-[26rem] w-[26rem]" aria-hidden="true"></div>

            <div class="relative max-w-2xl">
                <p class="eyebrow"><span class="eyebrow-index">→</span><span class="eyebrow-rule" aria-hidden="true"></span>Get in touch</p>
                <h2 id="cta-title" class="title mt-5">{{ $title }}</h2>
                <p class="lead mt-5">
                    {{ $slot->isNotEmpty() ? $slot : 'Hiring for a backend or full-stack role, planning a project, or looking for a collaborator? Tell me what you’re working on.' }}
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        Start a conversation <x-icon name="arrow-right" class="btn-icon btn-icon-move" />
                    </a>
                    @if ($site->email())
                        <a href="mailto:{{ $site->email() }}" class="btn btn-secondary">
                            <x-icon name="mail" class="btn-icon" /> {{ $site->email() }}
                        </a>
                        <x-copy-button :value="$site->email()" label="Email address" class="btn-ghost border-transparent bg-transparent" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
