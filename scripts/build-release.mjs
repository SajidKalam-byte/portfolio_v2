/**
 * Builds an upload-ready release for shared hosting (BigRock/cPanel).
 *
 *   npm run release              # build assets, install production vendor/, package
 *   npm run release -- --skip-build
 *
 * Output (in ./release):
 *   portfolio_app/     the Laravel application  → upload OUTSIDE public_html
 *   public_html/       a copy of public/        → upload INTO public_html (split layout)
 *   portfolio-release.tar.gz   both folders, for a single upload + "Extract" in cPanel
 *
 * No .env, database, logs, tests, or node_modules are included.
 * See docs/DEPLOYMENT.md for the full procedure.
 */
import { execSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, relative, resolve, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const releaseDir = join(root, 'release');
const appDir = join(releaseDir, 'portfolio_app');
const webDir = join(releaseDir, 'public_html');
const skipBuild = process.argv.includes('--skip-build');

const run = (command, cwd = root) => {
    console.log(`\n> ${command}`);
    execSync(command, { cwd, stdio: 'inherit' });
};

// Paths relative to the project root that must never ship.
const excluded = [
    '.git', '.github', '.idea', '.vscode', '.claude', '.fleet', '.zed',
    'node_modules', 'vendor', 'release', 'tests', 'scripts',
    '.env', '.env.backup', '.env.production', '.phpunit.result.cache', '.phpunit.cache',
    'database/database.sqlite', 'public/hot', 'public/storage',
    'storage/logs', 'storage/framework/cache/data', 'storage/framework/sessions',
    'storage/framework/views', 'storage/framework/testing', 'storage/pail',
    'bootstrap/cache/config.php', 'bootstrap/cache/routes-v7.php', 'bootstrap/cache/events.php',
    'bootstrap/cache/packages.php', 'bootstrap/cache/services.php',
    'phpunit.xml', 'vite.config.js', 'package.json', 'package-lock.json',
    'sajidkalam.png', // source art for the logo/favicon, already baked into public/ — not a web asset itself
];

const isExcluded = (source) => {
    const rel = relative(root, source).split(sep).join('/');
    return excluded.some((path) => rel === path || rel.startsWith(`${path}/`));
};

if (!skipBuild) {
    run('npm run build');
}

if (!existsSync(join(root, 'public/build/manifest.json'))) {
    console.error('public/build/manifest.json is missing — run `npm run build` first.');
    process.exit(1);
}

rmSync(releaseDir, { recursive: true, force: true });
mkdirSync(appDir, { recursive: true });

console.log('\nCopying application files…');
// Copy entry by entry: release/ lives inside the project, so the root can't be copied in one go.
for (const entry of readdirSync(root)) {
    const source = join(root, entry);
    if (!isExcluded(source)) {
        cpSync(source, join(appDir, entry), { recursive: true, filter: (path) => !isExcluded(path) });
    }
}

// Recreate the writable directories Laravel expects (empty, with .gitignore placeholders).
for (const dir of ['storage/logs', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'bootstrap/cache']) {
    mkdirSync(join(appDir, dir), { recursive: true });
    writeFileSync(join(appDir, dir, '.gitignore'), '*\n!.gitignore\n');
}

// Production dependencies only, resolved for the PHP version pinned in composer.json (config.platform).
run('composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist', appDir);

console.log('\nPreparing public_html copy…');
cpSync(join(appDir, 'public'), webDir, { recursive: true });

writeFileSync(join(releaseDir, 'UPLOAD-STEPS.txt'), `Portfolio release — ${new Date().toISOString()}

1. Upload and extract portfolio-release.tar.gz in your cPanel HOME directory
   (the folder that CONTAINS public_html — not inside it).
2. Choose ONE layout:
   A) Domain document root can point to portfolio_app/public  → ignore public_html/ from this release.
   B) Domain must use public_html                              → move the contents of the release's
      public_html/ into your real public_html/ (keep any cPanel PHP handler lines in .htaccess).
      index.php detects ../portfolio_app automatically.
3. Create portfolio_app/.env from portfolio_app/.env.example (APP_KEY, APP_URL, DB_*).
4. Create the database tables: SSH/Terminal → php artisan migrate --force && php artisan db:seed --force
   or import the file from "php artisan portfolio:export-sql --with-admin" via phpMyAdmin.
5. Visit the site, then sign in at /admin and review the Deployment checks.

Full guide: portfolio_app/docs/DEPLOYMENT.md
`);

console.log('\nCreating archive…');
run('tar -czf portfolio-release.tar.gz portfolio_app public_html UPLOAD-STEPS.txt', releaseDir);

const size = readdirSync(releaseDir).includes('portfolio-release.tar.gz') ? 'release/portfolio-release.tar.gz' : '(archive failed)';
console.log(`\nDone → ${size}`);
