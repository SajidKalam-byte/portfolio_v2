import { prefersReducedMotion, rafThrottle } from './motion';

/**
 * Fills the timeline line as the reader scrolls through it.
 */
export function initTimeline() {
    const timelines = document.querySelectorAll('[data-timeline]');

    timelines.forEach((timeline) => {
        const line = timeline.querySelector('.timeline-progress');

        if (!line) {
            return;
        }

        if (prefersReducedMotion()) {
            line.style.setProperty('--progress', '1');
            return;
        }

        const update = rafThrottle(() => {
            const rect = timeline.getBoundingClientRect();
            const anchor = window.innerHeight * 0.65;
            const progress = Math.min(1, Math.max(0, (anchor - rect.top) / rect.height));
            line.style.setProperty('--progress', progress.toFixed(4));
        });

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
    });
}
