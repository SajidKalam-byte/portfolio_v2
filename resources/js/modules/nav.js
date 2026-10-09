import { rafThrottle } from './motion';

function initScrolledHeader(header) {
    const update = rafThrottle(() => {
        header.dataset.scrolled = String(window.scrollY > 8);
    });

    update();
    window.addEventListener('scroll', update, { passive: true });
}

/**
 * A pill that slides behind the hovered or focused link and settles back on the
 * current page's link.
 */
function initIndicator(track) {
    const indicator = track.querySelector('[data-nav-indicator]');
    const links = [...track.querySelectorAll('.nav-link')];

    if (!indicator || links.length === 0) {
        return;
    }

    const active = () => links.find((link) => link.getAttribute('aria-current') === 'page');

    const moveTo = (link) => {
        if (!link) {
            indicator.style.opacity = '0';
            return;
        }

        indicator.style.width = `${link.offsetWidth}px`;
        indicator.style.transform = `translateX(${link.offsetLeft}px)`;
        indicator.style.opacity = '1';
    };

    // Place without animating on first paint.
    indicator.style.transition = 'none';
    moveTo(active());
    requestAnimationFrame(() => indicator.style.removeProperty('transition'));

    links.forEach((link) => {
        link.addEventListener('pointerenter', () => moveTo(link));
        link.addEventListener('focus', () => moveTo(link));
    });

    track.addEventListener('pointerleave', () => moveTo(active()));
    track.addEventListener('focusout', (event) => {
        if (!track.contains(event.relatedTarget)) {
            moveTo(active());
        }
    });

    window.addEventListener('resize', rafThrottle(() => moveTo(active())));
    document.fonts?.ready.then(() => moveTo(active()));
}

function initMobileMenu(header) {
    const button = header.querySelector('[data-menu-button]');
    const menu = document.getElementById(button?.getAttribute('aria-controls') ?? '');

    if (!button || !menu) {
        return;
    }

    const setOpen = (open, { restoreFocus = false } = {}) => {
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        header.dataset.menuOpen = String(open);
        menu.hidden = !open;

        if (open) {
            menu.querySelector('a')?.focus();
        } else if (restoreFocus) {
            button.focus();
        }
    };

    button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
            setOpen(false, { restoreFocus: true });
        }
    });

    // Close when resizing up to the desktop layout.
    window.matchMedia('(min-width: 48rem)').addEventListener('change', (event) => {
        if (event.matches) {
            setOpen(false);
        }
    });
}

export function initNav() {
    const header = document.querySelector('[data-site-header]');

    if (!header) {
        return;
    }

    initScrolledHeader(header);
    initMobileMenu(header);
    header.querySelectorAll('[data-nav-track]').forEach(initIndicator);
}
