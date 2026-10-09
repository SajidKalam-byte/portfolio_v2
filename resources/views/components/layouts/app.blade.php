@props(['seo' => null, 'bodyClass' => ''])

@php
    $seo ??= \App\Support\Seo\Seo::make($site->name(), $site->description());
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    {{-- Runs before first paint: applies the saved theme, defaulting to light, and flags JS support. --}}
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function () {
            var d = document.documentElement, t = null;
            d.classList.add('js');
            try { t = localStorage.getItem('theme'); } catch (e) {}
            if (t !== 'light' && t !== 'dark') {
                t = 'light';
            }
            d.setAttribute('data-theme', t);
            // If the app bundle fails to load, show everything rather than leave reveal targets hidden.
            setTimeout(function () { if (!window.__appReady) { d.classList.remove('js'); } }, 2500);
        })();
    </script>

    <x-seo :seo="$seo" />

    <meta name="theme-color" content="#eaecef" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b0b0d" media="(prefers-color-scheme: dark)">
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="{{ $bodyClass }}">
    <a href="#main" class="skip-link">Skip to content</a>

    <x-site-header />

    <main id="main" tabindex="-1" class="outline-none">
        {{ $slot }}
    </main>

    <x-site-footer />

    <div id="live-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
