<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;

class VoteSeeder extends Seeder
{
    /**
     * Have a random group of existing users vote once on each feature they have not voted on yet.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        Feature::all()->each(function (Feature $feature) use ($users): void {
            $votedUserIds = $feature->votes()->pluck('user_id');

            $users->whereNotIn('id', $votedUserIds)
                ->shuffle()
                ->take(fake()->numberBetween(0, $users->count()))
                ->each(fn (User $user) => Vote::factory()->for($feature)->for($user)->create());
        });
    }
}
