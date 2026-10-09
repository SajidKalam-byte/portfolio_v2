{{--
    Hero terminal. Deliberately labelled as interactive: a representative dev
    session for the kind of stack Sajid builds, not a live/real terminal.

    Timing is computed once, server-side, so the whole typing sequence is driven
    by plain CSS (terminal.js only auto-scrolls the box as new lines arrive).
--}}
@php
    $charMs = 30;
    $linePauseMs = 200;
    $outputMs = 260;

    $lines = [
        ['type' => 'command', 'text' => 'whoami'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'sajid-kalam — backend & full-stack engineer'],

        ['type' => 'command', 'text' => 'git status'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'On branch main · nothing to commit'],

        ['type' => 'command', 'text' => 'git pull origin main'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'Already up to date.'],

        ['type' => 'command', 'text' => 'pip install -r requirements.txt'],
        ['type' => 'output', 'variant' => 'success', 'text' => '✓ Installed 42 packages'],

        ['type' => 'command', 'text' => 'python manage.py migrate'],
        ['type' => 'output', 'variant' => 'success', 'text' => '✓ Applied 24 migrations'],

        ['type' => 'command', 'text' => 'python manage.py test'],
        ['type' => 'output', 'variant' => 'success', 'text' => '✓ 86 passed in 4.2s'],

        ['type' => 'command', 'text' => 'ruff check .'],
        ['type' => 'output', 'variant' => 'success', 'text' => '✓ No issues found'],

        ['type' => 'command', 'text' => 'black . --check'],
        ['type' => 'output', 'variant' => 'success', 'text' => '✓ All files formatted'],

        ['type' => 'command', 'text' => 'cat stack.txt'],
        ['type' => 'output', 'variant' => 'info', 'text' => 'Python · Django · DRF · PostgreSQL · AWS'],

        ['type' => 'command', 'text' => 'psql -c "\dt"'],
        ['type' => 'output', 'variant' => 'info', 'text' => '24 tables'],

        ['type' => 'command', 'text' => 'aws s3 ls s3://portfolio-media/'],
        ['type' => 'output', 'variant' => 'info', 'text' => '1,204 objects'],

        ['type' => 'command', 'text' => 'curl -I /api/patients/42/'],
        ['type' => 'output', 'variant' => 'info', 'text' => 'HTTP/1.1 200 OK'],

        ['type' => 'command', 'text' => 'python manage.py runserver'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'Starting development server at :8000'],

        ['type' => 'command', 'text' => 'git add .'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'staged 6 files'],

        ['type' => 'command', 'text' => 'git commit -m "fix: patient intake validation"'],
        ['type' => 'output', 'variant' => 'muted', 'text' => '[main 7a9f21c] fix: patient intake validation'],

        ['type' => 'command', 'text' => 'git push origin main'],
        ['type' => 'output', 'variant' => 'muted', 'text' => 'main -> main'],

        ['type' => 'command', 'text' => 'gunicorn config.wsgi:application'],
        ['type' => 'output', 'variant' => 'success', 'text' => 'Listening on :8000 — ready'],

        ['type' => 'command', 'text' => 'echo "shipped."'],
        ['type' => 'output', 'variant' => 'success', 'text' => 'shipped.'],
    ];

    $cursor = 0;
    foreach ($lines as $i => $line) {
        $chars = mb_strlen($line['text']);
        $duration = $line['type'] === 'command' ? $chars * $charMs : $outputMs;
        $lines[$i]['chars'] = $chars;
        $lines[$i]['duration'] = $duration;
        $lines[$i]['delay'] = $cursor;
        $cursor += $duration + $linePauseMs;
    }
@endphp

<figure {{ $attributes->class('schematic card') }}>
    <div class="schematic-head">
        <span class="flex items-center gap-2 font-mono text-xs text-ink-muted">
            <span class="terminal-dots" aria-hidden="true"><span></span><span></span><span></span></span>
            sajid@portfolio
        </span>
        <span class="badge">interactive</span>
    </div>

    @php
        $links = json_encode([
            'projects' => route('projects.index'),
            'experience' => route('experience'),
            'about' => route('about'),
            'resume' => route('resume'),
            'contact' => route('contact'),
        ]);
    @endphp
    <div data-reveal>
        <div class="terminal-body" data-terminal data-links="{{ $links }}" aria-label="interactive terminal session: pulling code, installing dependencies, running tests and migrations, checking the stack, and deploying the app. type help to see available commands."
>@foreach ($lines as $line)
<div class="terminal-row" style="--row-delay: {{ $line['delay'] }}ms">@if ($line['type'] === 'command')<span class="terminal-prompt" aria-hidden="true">$</span> <span class="terminal-cmd" style="--chars: {{ $line['chars'] }}; --delay: {{ $line['delay'] }}ms; --duration: {{ $line['duration'] }}ms">{{ $line['text'] }}</span>@else<span class="terminal-output terminal-output--{{ $line['variant'] }}" style="--delay: {{ $line['delay'] }}ms">{{ $line['text'] }}</span>@endif</div>
@endforeach
<div class="terminal-row terminal-row--input" data-terminal-input-row><span class="terminal-prompt" aria-hidden="true">$</span> <label class="sr-only" for="hero-terminal-input">Type a terminal command</label><input id="hero-terminal-input" class="terminal-input" type="text" placeholder="try &quot;help&quot;" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" data-terminal-input></div>
</div>
    </div>

    <figcaption class="border-t border-line px-5 py-3.5 text-xs leading-relaxed text-ink-subtle">
        type <code>help</code> to try it. Not a live system shell.
    </figcaption>
</figure>
