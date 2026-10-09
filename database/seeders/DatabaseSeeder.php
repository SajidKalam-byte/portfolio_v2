<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the portfolio content. Safe to re-run: every seeder upserts.
     *
     * No administrator is seeded — create one with `php artisan portfolio:admin`.
     */
    public function run(): void
    {
        $this->call([
            ProfileSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
