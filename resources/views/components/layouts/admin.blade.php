@props(['title' => 'Admin', 'heading' => null])

@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
        ['label' => 'Projects', 'route' => 'admin.projects.index', 'active' => 'admin.projects.*', 'icon' => 'layers'],
        ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'active' => 'admin.testimonials.*', 'icon' => 'quote'],
        ['label' => 'Skills', 'route' => 'admin.skills.index', 'active' => 'admin.skills.*', 'icon' => 'wrench'],
        ['label' => 'Experience', 'route' => 'admin.experiences.index', 'active' => 'admin.experiences.*', 'icon' => 'briefcase'],
        ['label' => 'Education', 'route' => 'admin.educations.index', 'active' => 'admin.educations.*', 'icon' => 'graduation'],
        ['label' => 'Messages', 'route' => 'admin.messages.index', 'active' => 'admin.messages.*', 'icon' => 'inbox'],
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'active' => 'admin.settings.*', 'icon' => 'settings'],
    ];
    $unread = \App\Models\ContactMessage::where('status', 'pending')->count();
    $toReview = \App\Models\Testimonial::where('status', 'received')->count();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Admin · {{ $site->name() }}</title>
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            var d = document.documentElement, t = null;
            d.classList.add('js');
            try { t = localStorage.getItem('theme'); } catch (e) {}
            if (t !== 'light' && t !== 'dark') { t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
            d.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a href="#main" class="skip-link">Skip to content</a>

    <div class="flex min-h-dvh flex-col lg:flex-row">
        <aside class="border-b border-line bg-canvas-soft lg:sticky lg:top-0 lg:h-dvh lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between gap-3 px-4 py-4 lg:px-5 lg:py-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg">
                    <img src="{{ asset('logo.png') }}" alt="" aria-hidden="true" width="32" height="32" class="brand-mark">
                    <span class="font-semibold tracking-tight">Admin</span>
                </a>
                <button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="Switch colour theme" aria-pressed="false">
                    <x-icon name="moon" class="icon-moon size-[1.125rem]" />
                    <x-icon name="sun" class="icon-sun size-[1.125rem]" />
                </button>
            </div>
            <nav aria-label="Admin" class="admin-nav flex gap-1 overflow-x-auto px-3 pb-3 lg:grid lg:overflow-visible lg:px-3">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs($item['active'])) aria-current="page" @endif class="shrink-0">
                        <span class="flex items-center gap-2.5"><x-icon :name="$item['icon']" class="size-4" /> {{ $item['label'] }}</span>
                        @if ($item['route'] === 'admin.messages.index' && $unread > 0)
                            <span class="badge badge-accent">{{ $unread }}<span class="sr-only"> unread</span></span>
                        @elseif ($item['route'] === 'admin.testimonials.index' && $toReview > 0)
                            <span class="badge badge-accent">{{ $toReview }}<span class="sr-only"> to review</span></span>
                        @endif
                    </a>
                @endforeach
            </nav>
            <div class="hidden gap-1 border-t border-line px-3 py-4 lg:grid">
                <div class="admin-nav">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener">
                        <span class="flex items-center gap-2.5"><x-icon name="external" class="size-4" /> View site</span>
                    </a>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" class="admin-nav">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-[0.625rem] px-3 py-2 text-[0.9375rem] text-ink-muted hover:bg-surface-2 hover:text-ink">
                        <x-icon name="logout" class="size-4" /> Sign out
                    </button>
                </form>
            </div>
        </aside>

        <main id="main" tabindex="-1" class="min-w-0 flex-1 outline-none">
            <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading ?? $title }}</h1>
                    @isset($actions)
                        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>

                @if (session('status'))
                    <div class="alert alert-success mt-6" role="status">
                        <x-icon name="check-circle" class="size-5" /><p>{{ session('status') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mt-6" role="alert">
                        <x-icon name="alert" class="size-5" /><p>{{ session('error') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger mt-6" role="alert">
                        <x-icon name="alert" class="size-5" />
                        <div>
                            <p class="font-medium">Please fix the following:</p>
                            <ul class="mt-1 list-disc pl-5 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="mt-8">
                    {{ $slot }}
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="mt-12 lg:hidden">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><x-icon name="logout" class="btn-icon" /> Sign out</button>
                </form>
            </div>
        </main>
    </div>

    <div id="live-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
