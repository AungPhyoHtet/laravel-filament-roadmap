<?php

declare(strict_types=1);

use App\Models\Feature;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets any user view and cast votes', function (): void {
    $user = User::factory()->create();
    $vote = Vote::factory()->create();

    expect($user->can('viewAny', Vote::class))->toBeTrue()
        ->and($user->can('view', $vote))->toBeTrue()
        ->and($user->can('create', Vote::class))->toBeTrue();
});

it('lets the voter withdraw but not edit their vote', function (): void {
    $vote = Vote::factory()->create();

    expect($vote->user->can('delete', $vote))->toBeTrue()
        ->and($vote->user->can('update', $vote))->toBeFalse();
});

it('stops other users from withdrawing a vote', function (): void {
    $otherUser = User::factory()->create();
    $vote = Vote::factory()->create();

    expect($otherUser->can('delete', $vote))->toBeFalse();
});

it('lets admins remove any vote but not edit it', function (): void {
    $admin = User::factory()->admin()->create();
    $vote = Vote::factory()->create();

    expect($admin->can('delete', $vote))->toBeTrue()
        ->and($admin->can('forceDelete', $vote))->toBeTrue()
        ->and($admin->can('update', $vote))->toBeFalse();
});

it('allows only one vote per user per feature', function (): void {
    $user = User::factory()->create();
    $feature = Feature::factory()->create();

    Vote::factory()->for($user)->for($feature)->create();

    expect(fn () => Vote::factory()->for($user)->for($feature)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

it('counts votes on a feature', function (): void {
    $feature = Feature::factory()->has(Vote::factory()->count(3))->create();

    expect($feature->votes()->count())->toBe(3);
});

it('lets only admins bulk delete votes', function (): void {
    expect(User::factory()->admin()->create()->can('deleteAny', Vote::class))->toBeTrue()
        ->and(User::factory()->create()->can('deleteAny', Vote::class))->toBeFalse();
});
