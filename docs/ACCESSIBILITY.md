# Accessibility checklist

Target: WCAG 2.2 AA. ✅ = implemented and checked during development; ☐ = recheck after content or design changes.

## Structure & navigation
- ✅ Landmarks: `header`, `nav` (labelled Primary / Mobile / Footer / Breadcrumb / Pagination), `main`, `footer`, `aside`.
- ✅ One `<h1>` per page (enforced by a test); headings nest in order.
- ✅ "Skip to content" link, visible on focus.
- ✅ `aria-current="page"` on active nav links and filter chips; `aria-current="true"` on the active table-of-contents entry.
- ✅ Mobile menu: `aria-expanded` / `aria-controls`, first link focused on open, `Escape` closes and returns focus to the button. Without JavaScript the menu is shown inline.
- ✅ Case studies offer a table of contents (sidebar on desktop, collapsible on mobile).

## Keyboard & focus
- ✅ Everything interactive is a native `<a>`, `<button>`, `<summary>`, or form control.
- ✅ Visible `:focus-visible` outline (2px accent, 3px offset) everywhere; inputs use a ring.
- ✅ Card links use one "stretched" link per card, so each card is a single tab stop.
- ✅ Architecture layers use `<details>`/`<summary>` (Enter/Space to toggle).

## Colour & contrast
- ✅ Text tokens meet AA on their backgrounds in both themes (see `docs/DESIGN.md`).
- ✅ Status is never colour-only: badges carry text ("Published", "New", "Current").
- ☐ Recheck contrast if you change tokens (e.g. with the browser devtools contrast checker).

## Motion
- ✅ `prefers-reduced-motion: reduce` removes non-essential animation, scroll reveals, pointer effects, and the theme transition.
- ✅ No autoplaying video, no flashing content; the hero pulse is slow and low-contrast.

## Images & media
- ✅ Every cover and gallery image has alt text (gallery alt is required in the admin).
- ✅ Decorative SVG icons are `aria-hidden`; the hero diagram has `role="img"` with `<title>` and `<desc>`.
- ✅ Width/height set on images to prevent layout shift.

## Forms
- ✅ Every input has a `<label>`; optional fields are marked "(optional)".
- ✅ Errors are linked with `aria-describedby`, flagged with `aria-invalid`, and summarised in a `role="alert"` box.
- ✅ Inline validation appears after leaving a field (not while typing); on submit, focus moves to the first invalid field.
- ✅ Success/failure messages use `role="alert"`; copy-to-clipboard results are announced via a polite live region.
- ✅ Correct `autocomplete` and `type` attributes (`name`, `email`, `username`, `current-password`).
- ✅ 16px inputs (no iOS zoom), 44px minimum button height.

## Known limitations
- Automated testing (axe/Lighthouse) and a screen-reader pass (NVDA/VoiceOver) have **not** been run yet — do this on the deployed site.
- The hero architecture diagram's text is small on narrow phones; its full content is available to assistive tech via `<desc>`.
- Filter chips on the home page dim non-matching skill groups rather than hiding them (content stays reachable; the change is announced).
