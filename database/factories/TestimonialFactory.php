<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Test data only. Real testimonials are never seeded.
 *
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_name' => $this->faker->name(),
            'author_title' => 'Engineering Manager',
            'author_company' => $this->faker->company(),
            'relationship' => 'client',
            'show_name' => true,
            'quote' => $this->faker->paragraph(3),
            'status' => 'published',
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Testimonial $testimonial) {
            if ($testimonial->status !== 'requested' && $testimonial->consent_at === null) {
                $testimonial->forceFill(['consent_at' => now(), 'consent_source' => 'manual', 'submitted_at' => now()]);
            }
        });
    }

    public function withoutConsent(): static
    {
        return $this->afterMaking(fn (Testimonial $t) => $t->forceFill(['consent_at' => null, 'consent_source' => null]));
    }

    public function received(): static
    {
        return $this->state(['status' => 'received']);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }
}
