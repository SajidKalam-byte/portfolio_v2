import { rafThrottle } from './motion';

/**
 * Case-study reading progress bar + table-of-contents scrollspy.
 */
export function initReadingProgress() {
    const article = document.querySelector('[data-reading-article]');
    const bar = document.querySelector('[data-reading-progress]');

    if (!article) {
        return;
    }

    if (bar) {
        const update = rafThrottle(() => {
            const rect = article.getBoundingClientRect();
            const total = rect.height - window.innerHeight * 0.6;
            const progress = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
            bar.style.setProperty('--progress', progress.toFixed(4));
            bar.setAttribute('aria-valuenow', String(Math.round(progress * 100)));
        });

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    }

    const links = [...document.querySelectorAll('[data-toc] a[href^="#"]')];
    const sections = links
        .map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1))))
        .filter(Boolean);

    if (sections.length === 0 || !('IntersectionObserver' in window)) {
        return;
    }

    const setActive = (id) => {
        links.forEach((link) => {
            const current = link.hash.slice(1) === id;
            link.setAttribute('aria-current', current ? 'true' : 'false');
        });
    };

    const visible = new Map();
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => visible.set(entry.target.id, entry.isIntersecting));
            // The first heading currently in the top band wins; otherwise keep the last one passed.
            const firstVisible = sections.find((section) => visible.get(section.id));
            if (firstVisible) {
                setActive(firstVisible.id);
            }
        },
        { rootMargin: '-15% 0px -70% 0px' },
    );

    sections.forEach((section) => observer.observe(section));
}
