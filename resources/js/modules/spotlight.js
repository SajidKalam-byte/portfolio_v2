import { hasFinePointer, prefersReducedMotion, rafThrottle } from './motion';

/**
 * Pointer-following glow for [data-spotlight] elements and the hero. Desktop
 * pointers only; disabled for touch and reduced motion.
 */
export function initSpotlight() {
    if (!hasFinePointer() || prefersReducedMotion()) {
        return;
    }

    document.querySelectorAll('[data-spotlight]').forEach((el) => {
        const move = rafThrottle((event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            el.style.setProperty('--my', `${event.clientY - rect.top}px`);
        });

        el.addEventListener('pointermove', move, { passive: true });
    });

    const hero = document.querySelector('[data-hero]');

    if (hero) {
        const move = rafThrottle((event) => {
            const rect = hero.getBoundingClientRect();
            hero.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            hero.style.setProperty('--my', `${event.clientY - rect.top}px`);
            hero.dataset.pointer = 'true';
        });

        hero.addEventListener('pointermove', move, { passive: true });
        hero.addEventListener('pointerleave', () => {
            hero.dataset.pointer = 'false';
        });
    }
}
