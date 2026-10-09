<footer class="site-footer mt-auto border-t border-line bg-canvas-soft">
    <div class="container-site grid gap-12 py-14 sm:py-16 md:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)]">
        <div class="max-w-sm">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-lg">
                <img src="{{ asset('logo.png') }}" alt="" aria-hidden="true" width="32" height="32" class="brand-mark">
                <span class="font-semibold tracking-tight">{{ $site->name() }}</span>
            </a>
            <p class="mt-5 text-sm muted">
                Software engineer building reliable web systems with Python, Django, and the cloud — from backend architecture to production.
            </p>
            @if ($site->location())
                <p class="mt-5 flex items-center gap-2 text-sm text-ink-subtle">
                    <x-icon name="map-pin" class="size-4" />
                    <span>{{ $site->location() }} · {{ $site->timezoneLabel() }}</span>
                </p>
            @endif
        </div>

        <nav aria-labelledby="footer-sitemap">
            <h2 id="footer-sitemap" class="mono-label uppercase tracking-[0.12em]">Pages</h2>
            <ul class="mt-4 grid gap-2.5 text-sm">
                <li><a class="link text-ink-muted hover:text-ink" href="{{ route('projects.index') }}">Projects</a></li>
                <li><a class="link text-ink-muted hover:text-ink" href="{{ route('experience') }}">Experience</a></li>
                <li><a class="link text-ink-muted hover:text-ink" href="{{ route('about') }}">About</a></li>
                <li><a class="link text-ink-muted hover:text-ink" href="{{ route('resume') }}">Resume</a></li>
                <li><a class="link text-ink-muted hover:text-ink" href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </nav>

        <div>
            <h2 class="mono-label uppercase tracking-[0.12em]">Elsewhere</h2>
            <ul class="mt-4 grid gap-2.5 text-sm">
                @foreach ($site->socialLinks() as $social)
                    <li>
                        <a class="group inline-flex items-center gap-2.5 text-ink-muted hover:text-ink" href="{{ $social['url'] }}" rel="me noopener" target="_blank">
                            <x-icon :name="$social['icon']" class="size-4" />
                            <span class="link">{{ $social['label'] }}</span>
                            <span class="sr-only">(opens in a new tab)</span>
                        </a>
                    </li>
                @endforeach
                @if ($site->email())
                    <li>
                        <a class="group inline-flex items-center gap-2.5 text-ink-muted hover:text-ink" href="mailto:{{ $site->email() }}">
                            <x-icon name="mail" class="size-4" />
                            <span class="link">Email</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="container-site flex flex-col gap-3 border-t border-line py-6 text-sm text-ink-subtle sm:flex-row sm:items-center sm:justify-between">
        <p>© {{ now()->year }} {{ $site->name() }}.</p>
        <a href="#main" class="inline-flex items-center gap-1.5 hover:text-ink">
            Back to top <x-icon name="arrow-up" class="size-3.5" />
        </a>
    </div>
</footer>
