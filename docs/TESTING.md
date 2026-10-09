# Testing

```bash
php artisan test              # or: composer test
php artisan test --filter=ContactFormTest
```

Tests use an in-memory SQLite database and don't need a Vite build or care whether `npm run dev` is running (see `tests/TestCase.php`).

## Coverage

| Suite | What it checks |
| --- | --- |
| `PublicPagesTest` | Every public page renders with canonical/OG/Twitter/description tags and exactly one `<h1>`; titles are unique; `Person`/`WebSite`/`ProfilePage`/`CreativeWork`/`BreadcrumbList` JSON-LD is valid; `alumniOf` lists only completed degrees; `worksFor` is off by default; confidential projects hide links; only the configured email is shown; phone hidden by default; filtered listings are `noindex` with a canonical to `/projects` |
| `ProjectVisibilityTest` | Drafts and scheduled projects 404 and stay out of listings/sitemap; pagination and paged canonicals; related projects exclude the current one; Markdown is escaped (`<script>`, `javascript:` links) |
| `SeoTest` | Sitemap is well-formed XML with absolute URLs, published projects only, no admin URLs; `robots.txt` in production vs. elsewhere; `noindex` outside production; JSON-LD can't break out of `<script>`; breadcrumb positions are sequential |
| `ContactFormTest` | Storage, accurate confirmation, validation + old input, honeypot, timing check, forged token, rate limit, CSRF, owner email only when SMTP is configured |
| `ResumeTest` | No broken download link when the PDF is missing; friendly filename; path traversal blocked; external fallback |
| `AdminTest` | Guest redirect, non-admins can't sign in or access, login throttle, every admin screen renders, project create with list/JSON fields, JSON validation, non-image upload rejected, upload stored under `uploads/`, settings change public details, resume path validation, message status/delete |
| `SecurityHeadersTest` | Baseline headers, CSP nonce matches inline scripts, no `unsafe-eval`, headers on error pages |
| `TestimonialTest` | Section hidden until a consented testimonial is published; featured first; hidden names; project testimonials on case study + featured card; escaping; no review schema; request link created once and stored hashed; single-use submission with consent recorded; consent required; expired/unknown links rejected (410); regenerated link invalidates the old one; publishing blocked without consent; admin screens render |
| `CreateAdminCommandTest` | Creates an admin; rejects weak passwords |
| `CaseStudyRendererTest` | Heading ids + dedupe, TOC, placeholder placement, section numbering |

## Manual checks before launch

1. Resize from 320px to 1920px on Home, a case study, Contact, and Experience — no horizontal scrolling.
2. Tab through Home and Contact with the keyboard only.
3. Toggle dark/light; enable "reduce motion" in the OS and reload.
4. Submit the contact form (success, validation errors, and — with SMTP — the notification email).
5. Validate structured data with Google's Rich Results Test and share a case-study URL in LinkedIn's Post Inspector to confirm the Open Graph image.
6. Run Lighthouse on the deployed site.
