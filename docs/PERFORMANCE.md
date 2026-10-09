# Performance checklist

Measured locally with `npm run build` (not a Lighthouse score — run Lighthouse/PageSpeed Insights against the deployed site before quoting numbers).

| Asset | Size |
| --- | --- |
| CSS (all pages) | ~55 KB, ~12 KB gzipped |
| JavaScript (all modules) | ~10 KB, ~3.6 KB gzipped |
| Fonts | Geist 29 KB + Geist Mono 23 KB + Instrument Serif 22 KB (Latin; Latin-Ext only if needed) |
| Project cover SVGs | 4–15 KB each |
| Open Graph PNGs | 34–52 KB each (only fetched by social platforms) |

## Implemented
- ✅ Production build: minified, hashed filenames, served with `Cache-Control: immutable` for one year (`.htaccess`).
- ✅ No third-party scripts, fonts, analytics, or CDNs on the page.
- ✅ Fonts self-hosted, subset, `font-display: swap`, variable fonts (one file per family).
- ✅ Vector artwork (SVG) for covers; lazy loading below the fold; `fetchpriority="high"` for the case-study cover; explicit `width`/`height` on images.
- ✅ Animations use `transform`/`opacity`; scroll and pointer handlers are passive and batched with `requestAnimationFrame`.
- ✅ Content queries cached (versioned cache, cleared on edits); rendered Markdown cached per project version; sitemap cached.
- ✅ Indexed columns for published/featured/sort-order queries; home page runs a handful of cached queries.
- ✅ Gzip via `mod_deflate`, browser caching via `mod_expires`.
- ✅ Pagination at 12 projects per page.
- ✅ `php artisan optimize` (or the admin *Optimize* button) caches config, routes, views, and events.
- ✅ No queues, cron, or background processes needed.

## To check after deployment
- ☐ PageSpeed Insights / Lighthouse on mobile for `/`, `/projects`, and one case study.
- ☐ Confirm `Content-Encoding: gzip` and the `Cache-Control` header on `/build/assets/*`.
- ☐ Replace any large uploaded screenshots with WebP at sensible dimensions.
- ☐ If you add a profile photo, provide a 2x-resolution WebP and explicit dimensions.
