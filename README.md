# Sajid Kalam — Portfolio

Personal portfolio for **Sajid Kalam**, Associate Engineer and full-stack web developer. Laravel 13 + Blade + Tailwind CSS 4, built to run on BigRock cPanel shared hosting without Node.js on the server.

> **Before publishing:** work through [docs/CONFIRM-BEFORE-PUBLISHING.md](docs/CONFIRM-BEFORE-PUBLISHING.md). A few facts (public email, MCA dates, some project roles) are deliberately conservative until you confirm them.

## What's inside

| Area | Details |
| --- | --- |
| Pages | Home, About, Experience, Projects (filterable, paginated), case studies, Contact, Resume, styled 404/419/429/500/503 |
| Content | MySQL via Eloquent; seeded with your real data; editable in a protected admin |
| Testimonials | Collected via private single-use request links (or added manually), published only with recorded consent; shown on Home, case studies, featured cards, and Contact |
| Case studies | Markdown with generated table of contents, numbered sections, expandable architecture diagrams, verified-metrics cards, galleries |
| SEO | Per-page titles/descriptions/canonicals, Open Graph + Twitter cards, per-project OG images, JSON-LD (`Person`, `WebSite`, `ProfilePage`, `CreativeWork`, `BreadcrumbList`), dynamic `/sitemap.xml` and `/robots.txt` |
| Design | Dark/light themes, self-hosted Geist / Geist Mono / Instrument Serif, design tokens in CSS, restrained motion with `prefers-reduced-motion` support |
| Security | CSRF, nonce-based CSP, security headers, rate limits, honeypot + timing checks, escaped Markdown, admin behind auth + gate, no default admin account |
| Hosting | Works with the web root at `public/` *or* a split `public_html/` layout — `index.php` detects which |

## Requirements

- PHP **8.3+** with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- Composer 2
- Node 20+ (local builds only — never needed on the server)
- MySQL 5.7.8+ / MariaDB 10.3+ in production (SQLite works for local development)

Laravel 13 needs PHP 8.3. BigRock offers PHP 8.2 and 8.3 — select **8.3** in cPanel → MultiPHP Manager. If your account is stuck on 8.2, see "PHP 8.2 fallback" in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Local development

```bash
composer install
npm install
cp .env.example .env            # then set APP_ENV=local, APP_DEBUG=true, APP_URL=http://127.0.0.1:8000
php artisan key:generate
```

For a quick local database, use SQLite: set `DB_CONNECTION=sqlite` and remove the other `DB_*` lines, then:

```bash
php artisan migrate --seed
php artisan portfolio:admin      # create your administrator account
composer run dev                 # Laravel on :8000 + Vite with hot reload
```

Open http://127.0.0.1:8000 — the admin lives at `/admin`.

### Useful commands

| Command | Purpose |
| --- | --- |
| `composer run dev` | Laravel dev server + Vite |
| `npm run build` | Production CSS/JS into `public/build` |
| `npm run images` | Regenerate cover art, Open Graph images, and favicons |
| `npm run release` | Build an upload-ready archive in `release/` |
| `php artisan test` | Run the test suite |
| `php artisan db:seed` | Re-apply seed content (upserts; overwrites seeded projects) |
| `php artisan portfolio:admin` | Create an admin, or reset an admin's password |
| `php artisan portfolio:export-sql --with-admin` | Export schema + content as a `.sql` file for phpMyAdmin |
| `./vendor/bin/pint` | Format PHP code |

## Project structure

```
app/
  Console/Commands/     portfolio:admin, portfolio:export-sql
  Http/Controllers/     Public pages + Admin/ (CRUD, auth, settings, maintenance)
  Http/Middleware/      SecurityHeaders (CSP nonce), NoIndex
  Http/Requests/        Form requests (contact form, admin forms)
  Models/               Project, ProjectImage, Skill, Experience, Education, ContactMessage, Setting, User
  Services/             PortfolioContent — cached read queries for public pages
  Support/              Settings, Site, ResumeFile, ContentCache, CaseStudyRenderer, PublicUploads, Seo/*
config/portfolio.php    Identity, defaults, categories, security & SEO switches
database/
  migrations/           Schema
  seeders/              ProfileSeeder, ProjectSeeder, content/*.md (case-study Markdown)
resources/
  css/app.css           Design tokens + components (Tailwind 4, CSS-first config)
  css/fonts.css         Self-hosted font faces
  js/app.js, modules/   Progressive enhancement (theme, nav, reveal, filters, forms, …)
  views/                Blade: components/, pages/, projects/partials/, admin/, errors/
public/                 index.php, .htaccess, images/, favicons, build/ (generated)
scripts/                generate-images.mjs, build-release.mjs
docs/                   Deployment, content, design, security, accessibility, performance
tests/                  Feature + unit tests (PHPUnit)
```

## Updating content

Everything public is editable at `/admin` (projects, gallery images, skills, experience, education, settings, contact messages). Details, Markdown conventions, and image guidelines: [docs/CONTENT.md](docs/CONTENT.md).

## Documentation

- [Deployment to BigRock / cPanel](docs/DEPLOYMENT.md)
- [Updating content & images](docs/CONTENT.md)
- [Design system & motion](docs/DESIGN.md)
- [Security](docs/SECURITY.md)
- [Accessibility checklist](docs/ACCESSIBILITY.md)
- [Performance checklist](docs/PERFORMANCE.md)
- [Testing](docs/TESTING.md)
- [Confirm before publishing](docs/CONFIRM-BEFORE-PUBLISHING.md)

## Licence

Code: proprietary to Sajid Kalam. Fonts: SIL Open Font License (Geist, Geist Mono, Instrument Serif). Icons adapted from Lucide (ISC).
