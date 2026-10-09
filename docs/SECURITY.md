# Security considerations

## Implemented

**Transport & headers** (`app/Http/Middleware/SecurityHeaders.php`, `public/.htaccess`)
- Content Security Policy with a per-request nonce: `script-src 'self' 'nonce-…'`, `object-src 'none'`, `frame-ancestors 'self'`, `form-action 'self'`, `base-uri 'self'`. No `unsafe-eval`; inline styles allowed for CSS custom properties.
- `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: SAMEORIGIN`, restrictive `Permissions-Policy`, `Cross-Origin-Opener-Policy: same-origin`.
- `.htaccess` blocks dotfiles (except `.well-known`), `composer.*`, `package*.json`, `*.log`, `*.sql`, `*.sqlite`, `*.md` in the web root. HSTS and HTTPS redirect are provided commented-out, to enable once SSL works.
- Secure session cookie (`SESSION_SECURE_COOKIE=true`) in the production example.

**Contact form** (`ContactController`, `ContactRequest`)
- CSRF token + Laravel 13's origin-aware request-forgery protection.
- Server-side validation with length limits; control characters stripped from single-line fields.
- Honeypot field and an encrypted "form rendered at" timestamp; bots get a fake success and nothing is stored.
- Per-IP limits: 5 messages/hour (configurable) plus a 10/minute route throttle.
- IPs stored only as an HMAC (`ip_hash`), never raw. Message content is not logged; mail failures log only the message ID and error.
- Messages are only visible in the admin and rendered escaped.
- The visitor confirmation says the message was *received*, never that an email was sent.

**Admin**
- No registration route and no seeded/default account. Accounts are created with `php artisan portfolio:admin` (min. 12 characters, letters + numbers) or the export command.
- `auth` middleware + `access-admin` gate (`users.is_admin`, not mass-assignable). Login only succeeds for admin accounts; the same generic error is shown for any failure.
- Login throttled to 5/minute per email+IP and 30/hour per IP. Session regenerated on login, invalidated on logout.
- `X-Robots-Tag: noindex` and `Cache-Control: no-store` on every admin response; admin URLs never appear in the sitemap. Path configurable (`PORTFOLIO_ADMIN_PATH`) and the whole area can be disabled (`PORTFOLIO_ADMIN_ENABLED=false`).
- Uploads: images limited to JPG/PNG/WebP (validated by content, no SVG uploads), 2 MB, 4000 px; file extension derived from the detected MIME type; random suffix in filenames; deletions restricted to the uploads directory. Resume: PDF only, 5 MB, path validated to stay inside the public folder.

**Testimonials**
- Only published testimonials with recorded consent are shown; publishing is blocked server-side without it. Consent source (the person, via the form, or you, manually) and date are stored.
- Request links are 48 random characters, single-use, expire after 30 days, and are stored only as SHA-256 hashes. Link pages send `noindex`, are throttled (30 views / 6 submissions per minute), and are never in the sitemap.
- The feedback form collects no email address. Quotes are rendered escaped; external links use `rel="noopener nofollow"`. Photos are admin-only uploads (JPG/PNG/WebP, 1 MB) and are hidden when the person chooses not to be named.

**Output safety**
- Blade escapes all output. Case-study Markdown runs with `html_input: escape` and `allow_unsafe_links: false`.
- JSON-LD uses `JSON_HEX_TAG | JSON_HEX_AMP`, so content can't close the `<script>` element.
- Laravel 13's `cache.serializable_classes = false` is kept: cached content is stored as plain arrays and rehydrated, so no PHP objects are ever unserialized from cache.

**Operational**
- `APP_DEBUG=false` and `LOG_LEVEL=warning` in the production example; the dashboard flags debug mode, non-HTTPS `APP_URL`, and non-production `APP_ENV`.
- Non-production environments serve `noindex` headers and `Disallow: /`.

## Your responsibilities

- Keep `.env` and `APP_KEY` out of the web root and out of version control.
- Use a unique, long admin password; consider a password manager.
- Delete `portfolio-mysql.sql` exports after importing (they can contain a password hash).
- Keep PHP at a supported version in cPanel and update dependencies (`composer update`, `npm update`) periodically, re-running tests.
- Back up the database (cPanel → *Backup*) before large changes.

## Known limitations

- No two-factor authentication for the admin (single-owner site; add Laravel Fortify if needed).
- No CAPTCHA — honeypot, timing, and rate limits are usually enough for a personal site; add Cloudflare Turnstile if spam appears.
- Rate limiting uses the client IP; behind Cloudflare, enable `trustProxies` in `bootstrap/app.php`.
- Healthcare content is described in terms of controls implemented, without compliance claims — keep it that way unless you have written authorization and evidence.
