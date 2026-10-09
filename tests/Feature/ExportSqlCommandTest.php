<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportSqlCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_mysql_schema_and_content(): void
    {
        $this->seed(DatabaseSeeder::class);
        $path = storage_path('framework/testing/export.sql');

        $this->artisan('portfolio:export-sql', ['path' => $path])->assertSuccessful();

        $sql = File::get($path);
        File::delete($path);

        $this->assertStringContainsString('create table `projects`', $sql);
        $this->assertStringContainsString('`tech_stack` json null', $sql);
        $this->assertStringContainsString('INSERT INTO `migrations`', $sql);
        $this->assertSame(9, substr_count($sql, 'INSERT INTO `projects`'));
        $this->assertStringNotContainsString('INSERT INTO `users`', $sql, 'No account is exported unless requested.');

        // `migrations` is Laravel's own bookkeeping table, created at runtime
        // rather than by a migration file — schema() never emits its CREATE on
        // its own. Importing INSERT INTO migrations without a preceding CREATE
        // fails with "table doesn't exist" on an empty database, which can
        // abort everything after it depending on the importer.
        $this->assertStringContainsString('CREATE TABLE `migrations`', $sql);
        $this->assertLessThan(
            strpos($sql, 'INSERT INTO `migrations`'),
            strpos($sql, 'CREATE TABLE `migrations`'),
            'CREATE TABLE `migrations` must appear before INSERT INTO `migrations`.',
        );
    }
}
