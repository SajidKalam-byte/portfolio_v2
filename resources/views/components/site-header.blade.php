@php
    $links = [
        ['label' => 'Work', 'route' => 'projects.index', 'active' => 'projects.*'],
        ['label' => 'Experience', 'route' => 'experience', 'active' => 'experience'],
        ['label' => 'About', 'route' => 'about', 'active' => 'about'],
        ['label' => 'Resume', 'route' => 'resume', 'active' => 'resume'],
    ];
@endphp

<header class="site-header" data-site-header>
    <div class="container-site flex h-16 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg" @if (request()->routeIs('home')) aria-current="page" @endif>
            <img src="{{ asset('logo.png') }}" alt="" aria-hidden="true" width="32" height="32" class="brand-mark">
            <span class="text-[0.9375rem] font-semibold tracking-tight">{{ $site->name() }}</span>
        </a>

        <nav aria-label="Primary" class="hidden md:block">
            <div class="nav-track" data-nav-track>
                <span class="nav-indicator" data-nav-indicator aria-hidden="true"></span>
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="nav-link" @if (request()->routeIs($link['active'])) aria-current="page" @endif>{{ $link['label'] }}</a>
                @endforeach
            </div>
        </nav>

        <div class="flex items-center gap-1.5">
            <button type="button" class="icon-btn theme-toggle" data-theme-toggle aria-label="Switch colour theme" aria-pressed="false">
                <x-icon name="moon" class="icon-moon size-[1.125rem]" />
                <x-icon name="sun" class="icon-sun size-[1.125rem]" />
            </button>
            <a href="{{ route('contact') }}" class="btn btn-primary btn-sm hidden sm:inline-flex" @if (request()->routeIs('contact')) aria-current="page" @endif>
                Contact
            </a>
            <button type="button" class="icon-btn md:hidden" data-menu-button aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu">
                <x-icon name="menu" class="size-5" />
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="mobile-menu md:hidden" hidden>
        <nav aria-label="Mobile" class="container-site pb-6">
            @foreach ([...$links, ['label' => 'Contact', 'route' => 'contact', 'active' => 'contact']] as $link)
                <a href="{{ route($link['route']) }}" @if (request()->routeIs($link['active'])) aria-current="page" @endif>
                    {{ $link['label'] }}
                    <x-icon name="arrow-right" class="size-4 text-ink-subtle" />
                </a>
            @endforeach
        </nav>
    </div>
</header>
