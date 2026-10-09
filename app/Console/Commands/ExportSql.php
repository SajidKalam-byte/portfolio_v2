<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PDO;

/**
 * Builds a MySQL import file for hosts without SSH access.
 *
 * The schema is compiled from the migrations (nothing is executed), and the
 * content rows come from your local database — so seed locally, review the
 * site, then import the file with phpMyAdmin into the empty production database.
 */
class ExportSql extends Command
{
    protected $signature = 'portfolio:export-sql
                            {path=storage/app/portfolio-mysql.sql : Where to write the .sql file}
                            {--with-admin : Prompt for an administrator account to include}';

    protected $description = 'Export schema + portfolio content as a MySQL .sql file for phpMyAdmin import';

    /** Content tables, in foreign-key order. */
    private const TABLES = ['skills', 'experiences', 'educations', 'projects', 'project_images', 'testimonials', 'settings'];

    public function handle(): int
    {
        $sql = [
            '-- Portfolio database export — generated '.now()->toDateTimeString(),
            '-- Import into an EMPTY MySQL 5.7.8+/MariaDB 10.3+ database (phpMyAdmin → Import).',
            'SET NAMES utf8mb4;',
            'SET FOREIGN_KEY_CHECKS = 0;',
            '',
            ...$this->schema(),
            '',
            ...$this->migrationRows(),
            '',
            ...$this->contentRows(),
        ];

        if ($this->option('with-admin')) {
            $admin = $this->adminRow();

            if ($admin === null) {
                return self::FAILURE;
            }

            $sql[] = '';
            $sql[] = $admin;
        }

        $sql[] = '';
        $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';

        $path = (string) $this->argument('path');
        $isAbsolute = str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
        $path = $isAbsolute ? $path : base_path($path);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode("\n", $sql)."\n");

        $this->info("Wrote {$path}");
        $this->warn('The file may contain a password hash. Import it, then delete it — never upload it to public_html.');

        return self::SUCCESS;
    }

    /**
     * Compile every migration's up() to MySQL DDL without touching a server.
     *
     * @return list<string>
     */
    private function schema(): array
    {
        $config = ['driver' => 'mysql', 'name' => 'mysql-export', 'database' => 'portfolio', 'prefix' => '',
            'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB'];

        // A stand-in PDO satisfies version checks; pretend() never executes anything.
        $connection = new MySqlConnection(new PDO('sqlite::memory:'), 'portfolio', '', $config);
        $default = config('database.default');

        DB::extend('mysql-export', fn () => $connection);
        config(['database.default' => 'mysql-export', 'database.connections.mysql-export' => $config]);

        try {
            $queries = $connection->pretend(function () {
                foreach ($this->migrationFiles() as $file) {
                    (require $file)->up();
                }
            });
        } finally {
            config(['database.default' => $default]);
            DB::purge('mysql-export');
        }

        return array_map(fn (array $q) => $q['query'].';', $queries);
    }

    /**
     * `migrations` is Laravel's own bookkeeping table — created by the framework
     * at runtime, not by a migration file — so schema() never emits a CREATE for
     * it. Without this, the INSERT below fails with "table doesn't exist" on an
     * empty database, which (depending on the importer) can abort everything
     * after it, including the real content and the admin user.
     *
     * @return list<string>
     */
    private function migrationRows(): array
    {
        $rows = array_map(
            fn (string $file) => '('.$this->quote(pathinfo($file, PATHINFO_FILENAME)).', 1)',
            $this->migrationFiles(),
        );

        return [
            'CREATE TABLE `migrations` (',
            '  `id` int unsigned NOT NULL AUTO_INCREMENT,',
            '  `migration` varchar(255) NOT NULL,',
            '  `batch` int NOT NULL,',
            '  PRIMARY KEY (`id`)',
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'INSERT INTO `migrations` (`migration`, `batch`) VALUES',
            implode(",\n", $rows).';',
        ];
    }

    /**
     * @return list<string>
     */
    private function contentRows(): array
    {
        $lines = [];

        foreach (self::TABLES as $table) {
            $records = DB::table($table)->orderBy('id')->get();

            foreach ($records as $record) {
                $row = (array) $record;
                $columns = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($row)));
                $values = implode(', ', array_map([$this, 'quote'], array_values($row)));
                $lines[] = "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});";
            }

            $this->line(sprintf('  %-16s %d rows', $table, $records->count()));
        }

        return $lines;
    }

    private function adminRow(): ?string
    {
        $email = strtolower(trim((string) $this->ask('Administrator email')));
        $name = (string) $this->ask('Display name', 'Administrator');
        $password = (string) $this->secret('Password (min. 12 characters, letters and numbers)');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            ['email' => ['required', 'email:rfc'], 'name' => ['required', 'max:120'], 'password' => ['required', Password::min(12)->letters()->numbers()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        $now = now()->toDateTimeString();

        return sprintf(
            'INSERT INTO `users` (`name`, `email`, `email_verified_at`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES (%s, %s, %s, %s, 1, %s, %s);',
            $this->quote($name), $this->quote($email), $this->quote($now), $this->quote(Hash::make($password)), $this->quote($now), $this->quote($now),
        );
    }

    /**
     * @return list<string>
     */
    private function migrationFiles(): array
    {
        $files = glob(database_path('migrations/*.php')) ?: [];
        sort($files);

        return $files;
    }

    private function quote(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? '1' : '0',
            is_int($value), is_float($value) => (string) $value,
            default => "'".strtr((string) $value, [
                '\\' => '\\\\', "'" => "\\'", "\0" => '\\0', "\n" => '\\n', "\r" => '\\r', "\x1a" => '\\Z',
            ])."'",
        };
    }
}
