# Deploying to BigRock (cPanel shared hosting)

This guide assumes a BigRock Linux shared-hosting plan with cPanel, PHP, and MySQL. Nothing here needs Node.js, a queue worker, or a cron job on the server.

> Verify each assumption against **your** account — plans differ in PHP versions, SSH access, and whether you can change a domain's document root.

---

## 0. Compatibility check

| Requirement | This project | What to check in cPanel |
| --- | --- | --- |
| PHP | **8.3+** (Laravel 13) | *MultiPHP Manager* → select PHP 8.3 for the domain |
| PHP extensions | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `bcmath` | *Select PHP Version → Extensions* (if shown). `gd` is **not** required. |
| Database | MySQL 5.7.8+ or MariaDB 10.3+ (JSON columns) | *phpMyAdmin* shows the server version on its home page |
| Storage | ~45 MB app + your uploads | *Disk Usage* |
| SSL | Needed for `SESSION_SECURE_COOKIE=true` | *SSL/TLS Status* → run AutoSSL, or install a certificate |

**Why Laravel 13:** Laravel 11 stopped receiving security fixes in March 2026 and Laravel 12's end in February 2027. Laravel 13 is supported until March 2028 and runs on PHP 8.3–8.5.

### PHP 8.2 fallback

If your account can only run PHP 8.2, switch to Laravel 12 (security fixes until 24 Feb 2027) — the application code avoids 13-only APIs:

1. In `composer.json`: `"php": "^8.2"`, `"laravel/framework": "^12.0"`, `"laravel/tinker": "^2.10"`, and `"config.platform.php": "8.2.0"`.
2. `composer update`, then `php artisan test`.
3. Plan the upgrade back to 13 before February 2027.

---

## 1. Build the release (on your computer)

```bash
npm ci
npm run release
```

This compiles assets, installs **production-only** Composer dependencies resolved for PHP 8.3 (`config.platform` in `composer.json`), and writes:

```
release/
  portfolio_app/            ← the Laravel app (upload OUTSIDE public_html)
  public_html/              ← a copy of public/ for the split layout
  portfolio-release.tar.gz  ← both, for one upload
  UPLOAD-STEPS.txt
```

No `.env`, database file, logs, tests, or `node_modules` are included.

---

## 2. Choose a directory layout

### Layout A — recommended: point the domain at `portfolio_app/public`

Possible for **addon domains and subdomains** (cPanel → *Domains* → *Manage* → *Document Root*). Some plans also allow it for the primary domain.

```
/home/cpuser/
  portfolio_app/          ← application
    public/               ← document root
  public_html/            ← untouched
```

Only `public/` is web-accessible. This is the cleanest and safest setup.

### Layout B — primary domain fixed to `public_html` (split layout)

```
/home/cpuser/
  portfolio_app/          ← application (NOT web-accessible)
  public_html/            ← contents of release/public_html
    index.php             ← detects ../portfolio_app automatically
    .htaccess
    build/ images/ files/ uploads/ …
```

`public/index.php` looks for `../portfolio_app/bootstrap/app.php`. When found, it loads the app from there and tells Laravel that `public_html` is the public directory, so uploads, the resume PDF, and the Vite manifest resolve correctly. **No file edits needed.**

If you use a different folder name than `portfolio_app`, edit the two paths near the top of `public_html/index.php`.

With SSH you can alternatively replace `public_html` with a symlink: `mv public_html public_html.bak && ln -s portfolio_app/public public_html` (some hosts don't allow this).

### Never do this

Don't put the whole Laravel project *inside* `public_html` and rely on a rewrite to `/public`. A single misconfiguration would expose `.env` (database password, `APP_KEY`), source code, and logs.

---

## 3. Upload

1. cPanel → *File Manager* → your **home directory** (the one that contains `public_html`).
2. Upload `portfolio-release.tar.gz`, right-click → *Extract*.
3. Layout B only: move everything from the extracted `public_html/` into your real `public_html/`.
   - If cPanel already wrote a `.htaccess` there with a block like `# php -- BEGIN cPanel-generated handler`, **copy that block into the new `.htaccess`**. It selects the PHP version; losing it can silently switch PHP.
4. Delete the uploaded archive.

For later updates, upload only what changed: usually `portfolio_app/app`, `resources/views`, `public_html/build` (after `npm run build`), and `vendor` if dependencies changed.

---

## 4. Database

cPanel → *MySQL® Databases*:

1. Create a database (e.g. `cpuser_portfolio`).
2. Create a user with a long random password.
3. *Add User To Database* → **ALL PRIVILEGES**.

Then create the tables and content — pick one:

**With SSH / cPanel Terminal**

```bash
cd ~/portfolio_app
php artisan migrate --force
php artisan db:seed --force
php artisan portfolio:admin
```

(If `php` points to an older version, use the full path, e.g. `/opt/cpanel/ea-php83/root/usr/bin/php`.)

**Without SSH (phpMyAdmin)**

On your computer, after `php artisan migrate:fresh --seed` against your local database and reviewing the site:

```bash
php artisan portfolio:export-sql --with-admin
```

This writes `storage/app/portfolio-mysql.sql` containing the schema (compiled from the migrations), your content, and an administrator account (password hashed with bcrypt). In cPanel → *phpMyAdmin* → select the empty database → *Import* → choose the file. **Delete the local file afterwards.**

> The export's SQL is generated for MySQL but was not tested against a live MySQL server during development. If an import fails, the error message names the statement; the SSH route above is the fallback.

---

## 5. Configure `.env`

In `portfolio_app/`, copy `.env.example` to `.env` (File Manager → *Copy*, then *Edit*):

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example      # exact final domain, https, no trailing slash
APP_KEY=                                   # see below

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=cpuser_portfolio
DB_USERNAME=cpuser_portfolio
DB_PASSWORD=…

SESSION_SECURE_COOKIE=true                 # requires working HTTPS
PORTFOLIO_PUBLIC_EMAIL=…                   # the one address to publish
```

**`APP_KEY`:** run `php artisan key:generate --show` locally and paste the output. Keep it secret and stable — changing it invalidates sessions and encrypted form tokens.

**Mail (optional):** without SMTP, contact messages are still saved and visible in the admin. To get email notifications, create a mailbox in cPanel → *Email Accounts*, use the SMTP settings from *Connect Devices*, and set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT=465`, `MAIL_SCHEME=smtps`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `PORTFOLIO_NOTIFY_EMAIL`.

---

## 6. Permissions

Directories `755`, files `644`. Laravel must be able to write:

```
portfolio_app/storage/            (recursively)
portfolio_app/bootstrap/cache/
public_html/uploads/  (or portfolio_app/public/uploads/ in layout A)
public_html/files/
```

On most cPanel servers PHP runs as your user, so `755` is enough. Never use `777`.

---

## 7. Go live

1. Visit the site. If you see a 500 page, check `portfolio_app/storage/logs/laravel-*.log`.
2. Sign in at `/admin` (or your `PORTFOLIO_ADMIN_PATH`) and review **Deployment checks** on the dashboard.
3. Click **Optimize** on the dashboard (caches config, routes, and views). After any later `.env` change, click **Clear all caches**.
4. Upload your resume PDF in *Settings*.
5. Check `https://your-domain/robots.txt` shows `Allow: /` and a `Sitemap:` line with your domain. (It shows `Disallow: /` whenever `APP_ENV` isn't `production` — by design, to keep staging copies out of search.)
6. Submit `https://your-domain/sitemap.xml` in Google Search Console.
7. Optional hardening in `public/.htaccess`: uncomment the HTTPS redirect, the www/apex redirect, and HSTS once HTTPS works everywhere.

With SSH, step 3 is `php artisan optimize`.

---

## Shared-hosting limitations and how this project handles them

| Limitation | Handling |
| --- | --- |
| No Node.js | Assets are compiled locally; `public/build` is uploaded |
| No queue worker | `QUEUE_CONNECTION=sync`; contact emails are sent in-request and failures are logged, never shown as success |
| No cron | Nothing is scheduled |
| No `storage:link` | Uploads are written directly into the public directory |
| No SSH | `portfolio:export-sql` + phpMyAdmin for the database; admin buttons for cache maintenance |
| PHP version drift | `.htaccess` warns to keep cPanel's PHP handler block |
| Upload limits | Admin limits: images 2 MB, resume 5 MB (`config/portfolio.php`); PHP's `upload_max_filesize` must be at least that (*MultiPHP INI Editor*) |
| Behind Cloudflare/proxy | Uncomment `trustProxies` in `bootstrap/app.php` so rate limits see real client IPs |

## Troubleshooting

| Symptom | Likely cause |
| --- | --- |
| 500 on every page, empty log | Wrong PHP version, or `storage/` not writable |
| Unstyled pages | `public_html/build/` missing — upload it after `npm run build` |
| "419 Page expired" on the contact form | `APP_URL` domain differs from the one you're browsing (www vs apex), or `SESSION_SECURE_COOKIE=true` without HTTPS |
| Old content after editing `.env` | Click **Clear all caches** in the admin |
| Links point to the wrong domain | `APP_URL` is wrong, then clear caches |
