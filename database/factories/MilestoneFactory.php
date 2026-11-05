<?php

namespace Database\Factories;

use App\Models\Feature;
use App\Models\Milestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feature_id' => Feature::factory(),
            'name' => fake()->sentence(3),
            'due_date' => fake()->dateTimeBetween('now', '+6 months'),
            'is_completed' => false,
        ];
    }

    /**
     * Indicate that the milestone has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_completed' => true,
        ]);
    }
}
