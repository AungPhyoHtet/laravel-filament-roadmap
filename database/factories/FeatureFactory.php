<?php

namespace Database\Factories;

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'status' => FeatureStatus::Proposed,
            'type' => fake()->randomElement(FeatureType::cases()),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'effort_in_days' => fake()->numberBetween(1, 30),
            'priority' => fake()->numberBetween(1, 10),
            'cost' => fake()->randomFloat(2, 0, 50000),
            'target_delivery_date' => fake()->dateTimeBetween('now', '+6 months'),
            'delivered_at' => null,
        ];
    }

    /**
     * Indicate that the feature has the given status.
     */
    public function withStatus(FeatureStatus $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }

    /**
     * Indicate that the feature has been delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FeatureStatus::Delivered,
            'delivered_at' => fake()->dateTimeBetween('-3 months'),
        ]);
    }
}
