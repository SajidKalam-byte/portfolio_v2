# Updating content

All public content lives in the database and can be edited at `/admin`. Seeders provide the initial content; the admin is the day-to-day tool.

## Where things live

| Content | Admin screen | Stored in |
| --- | --- | --- |
| Projects & case studies | Projects | `projects`, `project_images` |
| Skills (grouped) | Skills | `skills`; category list in `config/portfolio.php` |
| Roles | Experience | `experiences` |
| Degrees | Education | `educations` |
| Email, phone, status line, profiles, resume | Settings | `settings` (falls back to `.env` / `config/portfolio.php`) |
| Contact-form submissions | Messages | `contact_messages` |
| Testimonials (overall and per project) | Testimonials | `testimonials` |
| Hero headline, intro, principles, About copy | — | Blade views in `resources/views/pages/` |

Admin edits clear the content cache immediately. If you edit rows directly in phpMyAdmin, click **Clear content cache** on the dashboard (or wait up to `PORTFOLIO_CACHE_TTL` seconds).

> `php artisan db:seed` re-applies the seed files and **overwrites** seeded projects/roles with the versions in `database/seeders/`. Once you edit in the admin, treat the admin as the source of truth, or edit the seeders instead — not both.

## Collecting testimonials

Nothing appears publicly until you approve a testimonial *and* permission is recorded. Until the first one is published, the testimonials section simply isn't shown — the site never displays placeholder quotes.

### Option A — send a request link (recommended)

1. **Admin → Testimonials → Request a testimonial.** Optionally enter the person's name and the project it's about.
2. Copy the generated link (and the suggested message) and send it however you normally talk to them — email, WhatsApp, LinkedIn.
3. They fill in a short form: name, role, company, how you worked together, the testimonial, an optional LinkedIn link, whether to show their name, and a **required** permission checkbox.
4. It arrives in the admin as **Needs review** (and by email if SMTP is configured). Fix typos only, then set **Status → Published**.

Links are private, work **once**, and expire after 30 days. Only a fingerprint (SHA-256) of each link is stored, so a link can't be shown again — use **New link** to issue a fresh one (the old one stops working).

### Option B — add one yourself

If someone already wrote you a recommendation (email, LinkedIn), use **Add one manually**, paste their exact words, and tick *"I have this person's permission to publish"*. Publishing is blocked until that box is ticked. Adding the LinkedIn recommendation URL as the *Link to the original* lets visitors verify it.

### Where they appear

| Setting | Effect |
| --- | --- |
| Published | Visible on the site |
| Featured | Shown first in the home page "In their words" section (up to six testimonials appear there) |
| Project | Also shown in that case study's "Client feedback" section, and as a pull-quote on its featured card |
| Show their name (off) | Only role and company are shown, and no photo |
| Sort order | Lower numbers first within the same featured/non-featured group |

Testimonials are deliberately **not** marked up as schema.org reviews — reviews of yourself on your own site aren't eligible for review rich results and could be seen as misleading.

## Writing a case study

Each project has structured fields plus a Markdown body.

| Field | Shown as |
| --- | --- |
| Short description | Cards, the case-study lead, meta description |
| Overview | "01 Overview" section (blank line = new paragraph) |
| My contributions | "My contributions" checklist (one per line) |
| At a glance | Feature grid (one per line) |
| Content (Markdown) | The rest of the case study |
| Architecture (JSON) | Expandable layer diagram |
| Verified metrics (JSON) | Metric cards under the header — **verified figures only** |

### Markdown conventions

- `## Heading` creates a numbered section and a table-of-contents entry. `###` creates a sub-heading.
- Put `[[architecture]]` or `[[highlights]]` on its own line to place those blocks. Blocks with data but no placeholder are appended at the end.
- `> Quote` renders as a callout — useful for caveats.
- Tables, lists, `code`, and links work (GitHub-flavoured Markdown).
- Raw HTML is escaped and `javascript:` links are dropped, so pasted content can't inject scripts.

### Architecture JSON

```json
{
  "heading": "Architecture overview",
  "intro": "One sentence of context.",
  "layers": [
    { "layer": "Application", "title": "Django + DRF", "description": "What this layer does.", "items": ["Django", "RBAC"] }
  ]
}
```

### Metrics JSON

```json
[{ "value": "98%", "label": "Reported classification accuracy", "note": "On the project's evaluation data." }]
```

### Confidential work

Tick **Confidential / NDA** to hide project and GitHub links and show a confidentiality badge. Never upload screenshots containing patient, client, or credential data.

## Images

| Image | Size | Location |
| --- | --- | --- |
| Project cover | 16:10, e.g. 1600×1000; JPG/PNG/WebP ≤ 2 MB (or SVG via path) | Upload in the project form, or `public/images/projects/` |
| Open Graph image | 1200×630 PNG/JPG | `public/images/og/{slug}.png` |
| Gallery screenshots | Any; WebP/JPG preferred | Upload in the project's *Gallery* section (alt text required) |
| Resume | PDF ≤ 5 MB | *Settings → Resume* |
| Profile photo (optional) | Square JPG/WebP | `public/images/`, then set `PORTFOLIO_PHOTO` |

### Replacing the placeholder artwork

The covers and Open Graph images are generated abstract illustrations — no screenshots or real data. To replace one:

- **With a real, publishable screenshot:** upload it as the project's cover in the admin. The generated OG image stays until you replace `public/images/og/{slug}.png` (1200×630) and set the path in the project form.
- **Tweak the generated art:** edit `scripts/generate-images.mjs` and run `npm run images` (requires the dev dependency `sharp`). Commit/upload the regenerated files in `public/images/` and `public/favicon*`.

Tips: export WebP at quality ~80, keep covers under ~300 KB, and write alt text that describes what matters ("Schedule view with weekly appointments"), not "screenshot".

## SEO settings

- Site-wide identity and description: `config/portfolio.php` → `identity`.
- Per-project: *SEO title*, *SEO description*, *Open Graph image path* in the project form.
- Page titles/descriptions for static pages: in each controller (`app/Http/Controllers/*Controller.php`).
- `worksFor` in structured data: *Settings → Include current employer* (off until confirmed).
- `APP_URL` controls every absolute URL (canonical, sitemap, Open Graph).

## Adding a skill category or project category

Edit `skill_categories` or `project_categories` in `config/portfolio.php` — the admin dropdowns and public filters pick them up automatically.
