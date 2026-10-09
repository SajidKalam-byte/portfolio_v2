/*
 * Progressive enhancement only: every page is fully usable without this file.
 * Each module looks for its own data-* hooks and does nothing if they're absent.
 */
import { initTheme } from './modules/theme';
import { initNav } from './modules/nav';
import { initReveal } from './modules/reveal';
import { initSpotlight } from './modules/spotlight';
import { initFilters } from './modules/filters';
import { initClipboard } from './modules/clipboard';
import { initReadingProgress } from './modules/reading';
import { initTimeline } from './modules/timeline';
import { initForms } from './modules/forms';
import { initArchitecture } from './modules/architecture';
import { initConfirm } from './modules/confirm';
import { initTerminal } from './modules/terminal';

window.__appReady = true;

const modules = [
    initTheme,
    initNav,
    initReveal,
    initSpotlight,
    initFilters,
    initClipboard,
    initReadingProgress,
    initTimeline,
    initForms,
    initArchitecture,
    initConfirm,
    initTerminal,
];

for (const init of modules) {
    try {
        init();
    } catch (error) {
        // One failing enhancement must never break the rest of the page.
        console.error(error);
    }
}
