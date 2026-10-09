# Design concept

## Idea: "engineering notebook"

The site presents Sajid's work the way a careful engineer documents a system: precise grid lines, monospace labels (`01 — Introduction`), numbered sections, annotated architecture diagrams, and honest footnotes about limitations. A single serif-italic accent word per headline ("*execution.*", "*down* the stack") adds a human, editorial note to an otherwise technical voice.

**Headline:** *Backend thinking. Full-stack execution.* — chosen because it is short, memorable, and matches the actual career arc (frontend → backend, APIs, deployment). Name and role sit inside the same `<h1>` for SEO.

**Hero visual:** a reference-architecture diagram (clients → Nginx → Gunicorn → Django/DRF → PostgreSQL/S3) with a slow pulse travelling through the layers. It is labelled *illustrative* and captioned "not a live system" — no fake logs or live metrics.

## Theme: industrial / neo-brutalist (October 2026)

Solid ink borders, hard offset shadows on primary buttons and cards (`3–6px 3–6px 0 var(--ink)`), a cool grey canvas, a faint film-grain overlay, and Space Grotesk headings. One accent — **burnt orange** — with amber used only in gradients.

## Colour

| Token | Light | Dark | Use |
| --- | --- | --- | --- |
| `--canvas` | `#eaecef` cool grey | `#0b0b0d` near-black | Page background |
| `--surface` | `#f8f9fa` | `#131316` | Cards |
| `--line` / `--line-strong` | `#1a1d20` (solid ink) | `#ededef` | Borders, rules |
| `--ink` | `#1a1d20` | `#ededef` | Primary text, hard shadows |
| `--ink-muted` | `#474e56` (7.1:1) | `#a3a3ad` (≈8:1) | Body copy |
| `--ink-subtle` | `#5f6770` (4.9:1) | `#85858f` (5.4:1) | Labels, captions, hints |
| `--accent` | `#c84b02` | `#e8651e` | Button fills, highlights |
| `--accent-ink` | `#a63f02` (5.3:1) | `#ff8a4d` (8.4:1) | Accent-coloured text/links |

Ratios are against `--canvas`; every text token meets WCAG AA (4.5:1). Button text: white on `#c84b02` (4.7:1) in light mode; near-black on `#e8651e` (5.9:1) in dark mode.

> When the new theme landed, light-mode `--ink-subtle` (`#89939e`, 2.6:1) and `--accent-ink` (`#c84b02`, 4.0:1) failed AA for small text, so they were darkened (and `--ink-muted` with them, to keep muted darker than subtle). Re-check contrast whenever these tokens change.

Tokens are CSS custom properties in `resources/css/app.css`, exposed to Tailwind through `@theme inline`, so `bg-surface`, `text-ink-muted`, `border-line`, etc. switch with the theme automatically. The generated artwork (`scripts/generate-images.mjs`) uses the dark-theme accent `#e8651e`.

## Typography

- **Space Grotesk** (variable) — headings (`--font-display`). **Geist** (variable) — UI and body. **JetBrains Mono** — labels, tags, diagrams. **Instrument Serif Italic** — one accent word per heading and the testimonial quote marks.
- All self-hosted (no Google Fonts request), Latin + Latin Extended subsets only, `font-display: swap`.
- Fluid scale: `--text-display`, `--text-title`, `--text-lead` use `clamp()`.

## Testimonials

Cards with a 2px ink border and a 5px hard shadow, an orange serif quote mark, and an author row (photo or initials avatar, name, role, relationship, linked project). Large size for a single quote, a masonry column layout for several. They appear only when real, consented testimonials are published:

- **Home:** "In their words" section after More projects (featured first, up to six).
- **Featured case-study cards:** a short pull-quote from that project's top testimonial.
- **Case studies:** a numbered "Client feedback" section at the end, listed in the contents.
- **Contact page:** one testimonial beside the form.

## Layout tokens

Container `76rem`, prose `44rem`, gutters 16 / 24 / 32 px, card radius `1.25rem`, control radius `0.75rem`. Breakpoints: `xs` 384, `sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1536.

## Motion

| Effect | Where | Implementation |
| --- | --- | --- |
| Staggered entrance | Hero, page headers | CSS keyframes (`.enter`, `--i` delay) — runs without JS |
| Scroll reveal | Sections, cards | `IntersectionObserver` adds `.is-revealed` |
| Architecture pulse | Hero diagram | SVG stroke/fill keyframes |
| Grid drift, glow breathe | Hero backgrounds | Slow CSS animations |
| Pointer spotlight | Hero, cards | CSS variables from `pointermove` — fine pointers only |
| Sliding nav pill | Desktop nav | Measured with JS, CSS transition |
| Timeline fill | Experience & journey | Scroll-linked `scaleY` |
| Reading progress + scrollspy | Case studies | Scroll listener + `IntersectionObserver` |
| Theme switch | Toggle | View Transitions API circle reveal, instant fallback |

`prefers-reduced-motion: reduce` disables all non-essential motion (animations collapse to their end state; spotlight and view transitions are skipped). If the JS bundle fails to load, a 2.5 s inline guard removes the `.js` class so hidden reveal targets become visible.

Alpine.js was not used: every interaction is a small vanilla module (~3.6 KB gzipped in total), and avoiding Alpine's `eval`-based expressions keeps the Content Security Policy strict (no `unsafe-eval`).
