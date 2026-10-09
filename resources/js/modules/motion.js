const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

export const prefersReducedMotion = () => reducedMotion.matches;

export const hasFinePointer = () => finePointer.matches;

/**
 * Coalesce high-frequency events (scroll, pointermove) into one callback per frame.
 */
export function rafThrottle(callback) {
    let frame = null;
    let lastArgs = [];

    return (...args) => {
        lastArgs = args;

        if (frame !== null) {
            return;
        }

        frame = requestAnimationFrame(() => {
            frame = null;
            callback(...lastArgs);
        });
    };
}

/**
 * Announce a message to screen readers through the global polite live region.
 */
export function announce(message) {
    const region = document.getElementById('live-region');

    if (!region) {
        return;
    }

    region.textContent = '';
    // A new text node after a tick makes repeated identical messages audible.
    setTimeout(() => {
        region.textContent = message;
    }, 50);
}
