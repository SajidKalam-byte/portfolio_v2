import { announce } from './motion';

/**
 * <button data-copy="text to copy" data-copy-label="Email address">
 *   <span data-copy-text>Copy</span>
 * </button>
 */
export function initClipboard() {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.hidden = false;

        const label = button.querySelector('[data-copy-text]');
        const original = label?.textContent;

        button.addEventListener('click', async () => {
            const value = button.dataset.copy;
            const what = button.dataset.copyLabel || 'Text';

            try {
                await navigator.clipboard.writeText(value);
            } catch {
                // Clipboard API unavailable (e.g. insecure origin): fall back to selection.
                const input = document.createElement('textarea');
                input.value = value;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.append(input);
                input.select();
                const ok = document.execCommand('copy');
                input.remove();

                if (!ok) {
                    announce(`Couldn't copy. ${what}: ${value}`);
                    return;
                }
            }

            button.dataset.copied = 'true';
            if (label) {
                label.textContent = 'Copied';
            }
            announce(`${what} copied to clipboard`);

            clearTimeout(button._copyTimer);
            button._copyTimer = setTimeout(() => {
                button.dataset.copied = 'false';
                if (label) {
                    label.textContent = original;
                }
            }, 2000);
        });
    });
}
