<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Leave between 0 and 5 comments from random existing users on every feature.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        Feature::all()->each(function (Feature $feature) use ($users): void {
            Comment::factory()
                ->count(fake()->numberBetween(0, 5))
                ->for($feature)
                ->state(fn (): array => ['user_id' => $users->random()->id])
                ->create();
        });
    }
}
