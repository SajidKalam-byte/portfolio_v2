import { prefersReducedMotion } from './motion';

const STORAGE_KEY = 'theme';
const THEME_COLORS = { light: '#eaecef', dark: '#0b0b0d' };

function store(theme) {
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Private mode or blocked storage: the choice just won't persist.
    }
}

function apply(theme) {
    const root = document.documentElement;
    root.dataset.theme = theme;

    document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => {
        meta.setAttribute('content', THEME_COLORS[theme]);
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const next = theme === 'dark' ? 'light' : 'dark';
        button.setAttribute('aria-label', `Switch to ${next} theme`);
        button.setAttribute('aria-pressed', String(theme === 'dark'));
    });
}

/**
 * Circular reveal from the toggle using the View Transitions API, with an
 * instant switch where unsupported or when reduced motion is requested.
 */
function switchTheme(next, origin) {
    if (!document.startViewTransition || prefersReducedMotion()) {
        apply(next);
        return;
    }

    const { x, y } = origin;
    const radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    const transition = document.startViewTransition(() => apply(next));

    transition.ready
        .then(() => {
            document.documentElement.animate(
                { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
                { duration: 520, easing: 'cubic-bezier(0.16, 1, 0.3, 1)', pseudoElement: '::view-transition-new(root)' },
            );
        })
        .catch(() => {});
}

export function initTheme() {
    const current = () => document.documentElement.dataset.theme || 'light';

    apply(current());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const next = current() === 'dark' ? 'light' : 'dark';
            const rect = button.getBoundingClientRect();
            // Keyboard activation reports (0, 0): start from the button centre instead.
            const origin = event.clientX || event.clientY
                ? { x: event.clientX, y: event.clientY }
                : { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };

            store(next);
            switchTheme(next, origin);
        });
    });
}
