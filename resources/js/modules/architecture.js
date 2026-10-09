/**
 * "Expand all / Collapse all" control for architecture layer <details>.
 */
export function initArchitecture() {
    document.querySelectorAll('[data-arch-toggle]').forEach((button) => {
        const stack = document.getElementById(button.getAttribute('aria-controls'));

        if (!stack) {
            return;
        }

        const details = [...stack.querySelectorAll('details')];
        const sync = () => {
            const allOpen = details.every((d) => d.open);
            button.textContent = allOpen ? 'Collapse all' : 'Expand all';
            button.setAttribute('aria-expanded', String(allOpen));
        };

        button.hidden = false;
        sync();

        button.addEventListener('click', () => {
            const open = !details.every((d) => d.open);
            details.forEach((d) => {
                d.open = open;
            });
            sync();
        });

        details.forEach((d) => d.addEventListener('toggle', sync));
    });
}
