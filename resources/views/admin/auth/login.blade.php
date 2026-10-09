<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · {{ $site->name() }}</title>
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
    <main id="main" class="relative isolate grid min-h-dvh place-items-center px-4 py-16">
        <div class="grid-backdrop" aria-hidden="true"></div>

        <div class="card w-full max-w-sm p-7 sm:p-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-lg">
                <img src="{{ asset('logo.png') }}" alt="" aria-hidden="true" width="32" height="32" class="brand-mark">
                <span class="font-semibold tracking-tight">{{ $site->name() }}</span>
            </a>
            <h1 class="mt-7 text-2xl font-semibold tracking-tight">Admin sign in</h1>
            <p class="mt-1.5 text-sm muted">Content management for this portfolio.</p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-7 grid gap-5" data-enhanced-form>
                @csrf
                <x-form.input name="email" type="email" label="Email" autocomplete="username" required autofocus />
                <x-form.input name="password" type="password" label="Password" autocomplete="current-password" required />
                <x-form.checkbox name="remember" label="Keep me signed in on this device" />
                <button type="submit" class="btn btn-primary w-full" data-submit>
                    <span data-submit-idle>Sign in</span>
                    <span class="inline-flex items-center gap-2" data-submit-busy hidden><x-icon name="loader" class="btn-icon spinner" /> Signing in…</span>
                </button>
            </form>
        </div>
    </main>
</body>
</html>
