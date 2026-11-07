<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
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
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_approved' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that the comment has been approved by an admin.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the comment is awaiting approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_approved' => false,
        ]);
    }
}
