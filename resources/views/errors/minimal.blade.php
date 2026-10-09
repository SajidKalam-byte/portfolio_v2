{{--
    Self-contained error page for 500/503: no database, no Vite assets, no components,
    so it still renders when those are the thing that failed.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ config('portfolio.identity.name', 'Portfolio') }}</title>
    <style>
        :root { color-scheme: light dark; --bg: #f6f5f1; --ink: #141416; --muted: #4f4f57; --line: #e4e1da; --accent: #c2410c; }
        @media (prefers-color-scheme: dark) { :root { --bg: #0b0b0d; --ink: #ededef; --muted: #a3a3ad; --line: #232328; --accent: #ff8763; } }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; background: var(--bg); color: var(--ink);
               font: 16px/1.6 ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        main { max-width: 34rem; }
        .code { font: 500 .75rem/1 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); }
        h1 { margin: 1rem 0 0; font-size: clamp(2rem, 1.4rem + 2.5vw, 3rem); line-height: 1.05; letter-spacing: -.035em; }
        p { color: var(--muted); }
        a { display: inline-block; margin-top: 1rem; padding: .6rem 1.1rem; border: 1px solid var(--line); border-radius: 999px; color: var(--ink); text-decoration: none; }
        a:hover { border-color: var(--muted); }
        a:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
    </style>
</head>
<body>
    <main>
        <p class="code">@yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="/">Back to the home page</a>
    </main>
</body>
</html>
