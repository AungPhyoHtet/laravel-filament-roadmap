<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Feature;
use App\Models\User;
use App\Models\Vote;
use Database\Seeders\CommentSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\VoteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds users, features, comments and votes', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(User::count())->toBe(11)
        ->and(Feature::count())->toBe(10)
        ->and(Comment::query()->whereNotIn('user_id', User::query()->select('id'))->exists())->toBeFalse()
        ->and(Vote::query()->whereNotIn('feature_id', Feature::query()->select('id'))->exists())->toBeFalse();
});

it('leaves at most five comments per feature', function (): void {
    User::factory(3)->create();
    Feature::factory(5)->create();

    $this->seed(CommentSeeder::class);

    Feature::withCount('comments')->get()->each(
        fn (Feature $feature) => expect($feature->comments_count)->toBeLessThanOrEqual(5)
    );
});

it('can run the vote seeder again without duplicate votes', function (): void {
    User::factory(5)->create();
    Feature::factory(5)->create();

    $this->seed(VoteSeeder::class);
    $this->seed(VoteSeeder::class);

    expect(Vote::count())->toBeLessThanOrEqual(25);
});

it('does nothing when there are no users', function (): void {
    Feature::factory(3)->create();

    $this->seed([CommentSeeder::class, VoteSeeder::class]);

    expect(Comment::count())->toBe(0)
        ->and(Vote::count())->toBe(0);
});
