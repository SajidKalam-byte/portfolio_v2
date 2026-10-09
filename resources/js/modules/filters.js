import { announce } from './motion';

/**
 * Client-side category filter.
 *
 * <div data-filter-group>
 *   <button data-filter="all" aria-pressed="true">…</button>
 *   <button data-filter="backend" aria-pressed="false">…</button>
 * </div>
 * <article data-filter-item="backend">…</article>
 *
 * Non-matching items are dimmed rather than removed, so the layout doesn't jump
 * and all content stays reachable.
 */
export function initFilters() {
    document.querySelectorAll('[data-filter-group]').forEach((group) => {
        const scope = document.getElementById(group.dataset.filterGroup) ?? document;
        const buttons = [...group.querySelectorAll('[data-filter]')];
        const items = [...scope.querySelectorAll('[data-filter-item]')];

        group.hidden = false;

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const value = button.dataset.filter;

                buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));

                items.forEach((item) => {
                    const match = value === 'all' || item.dataset.filterItem.split(' ').includes(value);
                    item.dataset.dimmed = String(!match);
                });

                announce(value === 'all' ? 'Showing all categories' : `Highlighting ${button.textContent.trim()}`);

                if (value !== 'all') {
                    items.find((item) => item.dataset.dimmed === 'false')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        });
    });
}
