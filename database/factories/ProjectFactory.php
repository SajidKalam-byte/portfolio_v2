<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title($this->faker->unique()->words(3, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => $this->faker->sentence(14),
            'description' => $this->faker->paragraph(),
            'category' => 'web-app',
            'project_type' => 'personal',
            'role' => 'Developer',
            'tech_stack' => ['Python', 'Django'],
            'case_study_content' => "## The problem\n\n".$this->faker->paragraph()."\n\n## Approach\n\n".$this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_featured' => false,
            'is_published' => true,
            'published_at' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function scheduled(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->addWeek()]);
    }
}
