import { prefersReducedMotion } from './motion';

const HELP_TEXT = 'Commands: help · whoami · about · skills · projects · experience · resume · contact · clear';

const RESPONSES = {
    whoami: { text: 'sajid-kalam — backend & full-stack engineer', variant: 'muted' },
    skills: { text: 'Python · Django · DRF · PostgreSQL · AWS · Nginx · Gunicorn · React Native', variant: 'info' },
    ls: { text: HELP_TEXT, variant: 'info' },
};

const NAV_COMMANDS = ['about', 'projects', 'experience', 'resume', 'contact'];

// Scrolls just far enough that `row`'s bottom edge lines up with the bottom
// of the visible box — not to scrollHeight, which (since every row already
// reserves its layout space, just invisible) is the end of ALL rows, most of
// which haven't happened yet. Scrolling there jumps straight past everything
// still mid-typing to the empty tail end.
function scrollRowIntoView(body, row) {
    if (!row) {
        return;
    }

    const target = Math.max(0, row.offsetTop + row.offsetHeight - body.clientHeight);
    body.scrollTo({ top: target, behavior: 'smooth' });
}

function appendRow(inputRow, build) {
    const row = document.createElement('div');
    row.className = 'terminal-row';
    build(row);
    inputRow.before(row);
    return row;
}

function appendOutput(inputRow, text, variant = 'muted') {
    return appendRow(inputRow, (row) => {
        const span = document.createElement('span');
        span.className = `terminal-output terminal-output--${variant}`;
        span.textContent = text;
        row.append(span);
    });
}

function appendEcho(inputRow, text) {
    return appendRow(inputRow, (row) => {
        const prompt = document.createElement('span');
        prompt.className = 'terminal-prompt';
        prompt.setAttribute('aria-hidden', 'true');
        prompt.textContent = '$';

        const echo = document.createElement('span');
        echo.className = 'terminal-echo';
        echo.textContent = text;

        row.append(prompt, document.createTextNode(' '), echo);
    });
}

/**
 * The live `$ _` prompt at the end of the terminal: a small set of real
 * commands with real (if curated) responses — including actually navigating
 * for things like "projects" — so typing into it isn't a dead end.
 */
function initInteractiveInput(body) {
    const input = body.querySelector('[data-terminal-input]');
    const inputRow = body.querySelector('[data-terminal-input-row]');

    if (!input || !inputRow) {
        return;
    }

    let links = {};
    try {
        links = JSON.parse(body.dataset.links || '{}');
    } catch {
        links = {};
    }

    body.addEventListener('click', (event) => {
        if (!event.target.closest('a, button, input')) {
            input.focus();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        const raw = input.value.trim();
        input.value = '';

        if (!raw) {
            return;
        }

        const command = raw.toLowerCase();
        appendEcho(inputRow, raw);

        if (command === 'clear') {
            [...body.querySelectorAll('.terminal-row')].forEach((row) => {
                if (row !== inputRow) {
                    row.remove();
                }
            });
        } else if (command.startsWith('sudo ') || command === 'sudo') {
            appendOutput(inputRow, "Permission denied: you're not root here.", 'muted');
        } else if (command === 'help') {
            appendOutput(inputRow, HELP_TEXT, 'info');
        } else if (RESPONSES[command]) {
            appendOutput(inputRow, RESPONSES[command].text, RESPONSES[command].variant);
        } else if (NAV_COMMANDS.includes(command) && links[command]) {
            appendOutput(inputRow, `Opening ${links[command]} …`, 'success');
            setTimeout(() => {
                window.location.href = links[command];
            }, 400);
        } else {
            appendOutput(inputRow, `command not found: ${raw} — type "help" for available commands`, 'muted');
        }

        scrollRowIntoView(body, inputRow);
    });
}

/**
 * Keeps the hero terminal scrolled to whichever line just finished as the
 * scripted intro plays, like `tail -f`.
 */
function initAutoScroll(body) {
    if (prefersReducedMotion()) {
        return;
    }

    const reveal = body.closest('[data-reveal]');

    if (!reveal) {
        return;
    }

    const rows = [...body.querySelectorAll('.terminal-cmd, .terminal-output')];

    const run = () => {
        rows.forEach((el) => {
            const style = getComputedStyle(el);
            const delay = parseFloat(style.getPropertyValue('--delay')) || 0;
            const duration = parseFloat(style.getPropertyValue('--duration')) || 0;
            const row = el.closest('.terminal-row');

            setTimeout(() => scrollRowIntoView(body, row), delay + duration);
        });
    };

    if (reveal.classList.contains('is-revealed')) {
        run();
        return;
    }

    const observer = new MutationObserver(() => {
        if (reveal.classList.contains('is-revealed')) {
            observer.disconnect();
            run();
        }
    });
    observer.observe(reveal, { attributes: true, attributeFilter: ['class'] });
}

export function initTerminal() {
    document.querySelectorAll('[data-terminal]').forEach((body) => {
        // Chrome remembers this element's scroll offset across a plain reload,
        // so a visitor can land here already scrolled past content that hasn't
        // actually typed yet. Force a clean start every time.
        body.scrollTop = 0;

        initAutoScroll(body);
        initInteractiveInput(body);
    });
}
