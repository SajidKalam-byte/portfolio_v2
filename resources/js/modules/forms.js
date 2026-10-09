/**
 * Enhances [data-enhanced-form] forms:
 * - inline validation messages after a field is first left (not while typing),
 * - character counters for [data-maxlength-counter],
 * - a busy state on submit to prevent double submission.
 *
 * Server-side validation remains the source of truth.
 */
function messageFor(field) {
    const { validity } = field;
    const label = field.dataset.label || 'This field';

    if (validity.valueMissing) {
        return `${label} is required.`;
    }
    if (validity.typeMismatch && field.type === 'email') {
        return 'Please enter a valid email address.';
    }
    if (validity.tooShort) {
        return `${label} needs at least ${field.minLength} characters (currently ${field.value.trim().length}).`;
    }
    if (validity.tooLong) {
        return `${label} can be at most ${field.maxLength} characters.`;
    }

    return field.validationMessage;
}

function errorElementFor(field) {
    const id = `${field.id}-client-error`;
    let el = document.getElementById(id);

    if (!el) {
        el = document.createElement('p');
        el.id = id;
        el.className = 'field-error';
        el.hidden = true;
        field.closest('.field')?.append(el);
    }

    return el;
}

function showValidity(field) {
    const error = errorElementFor(field);
    const serverError = document.getElementById(`${field.id}-error`);
    const describedBy = new Set((field.getAttribute('aria-describedby') || '').split(' ').filter(Boolean));

    if (field.checkValidity()) {
        error.hidden = true;
        describedBy.delete(error.id);
        if (!serverError || serverError.hidden) {
            field.removeAttribute('aria-invalid');
        }
    } else {
        error.textContent = messageFor(field);
        error.hidden = false;
        describedBy.add(error.id);
        field.setAttribute('aria-invalid', 'true');
        // A fresh client message supersedes a stale server message.
        if (serverError) {
            serverError.hidden = true;
        }
    }

    field.setAttribute('aria-describedby', [...describedBy].join(' '));
}

function initCounter(field) {
    const counter = document.getElementById(field.dataset.maxlengthCounter);

    if (!counter) {
        return;
    }

    const update = () => {
        const length = field.value.length;
        counter.textContent = `${length.toLocaleString()} / ${field.maxLength.toLocaleString()}`;
        counter.dataset.near = String(length > field.maxLength * 0.9);
    };

    update();
    field.addEventListener('input', update);
}

export function initForms() {
    document.querySelectorAll('form[data-enhanced-form]').forEach((form) => {
        const fields = [...form.querySelectorAll('input:not([type=hidden]):not([tabindex="-1"]), textarea, select')];

        fields.forEach((field) => {
            let touched = false;

            field.addEventListener('blur', () => {
                if (field.value !== '' || touched) {
                    touched = true;
                    showValidity(field);
                }
            });

            field.addEventListener('input', () => {
                if (touched) {
                    showValidity(field);
                }
            });

            if (field.dataset.maxlengthCounter) {
                initCounter(field);
            }
        });

        form.addEventListener('submit', (event) => {
            const invalid = fields.filter((field) => !field.checkValidity());

            if (invalid.length > 0) {
                event.preventDefault();
                invalid.forEach(showValidity);
                invalid[0].focus();
                return;
            }

            const submit = form.querySelector('[data-submit]');

            if (submit) {
                if (submit.dataset.busy === 'true') {
                    event.preventDefault();
                    return;
                }

                submit.dataset.busy = 'true';
                submit.setAttribute('aria-disabled', 'true');
                submit.querySelector('[data-submit-idle]')?.setAttribute('hidden', '');
                submit.querySelector('[data-submit-busy]')?.removeAttribute('hidden');
            }
        });

        // Browser validation bubbles are replaced by the inline messages above.
        form.setAttribute('novalidate', '');
    });
}
