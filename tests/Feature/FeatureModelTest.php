<?php

declare(strict_types=1);

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use App\Models\Comment;
use App\Models\Feature;
use App\Models\Milestone;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('applies the database defaults to a new feature', function (): void {
    $feature = Feature::create([
        'name' => 'Dark mode',
        'description' => 'Support a dark colour scheme.',
    ])->refresh();

    expect($feature->status)->toBe(FeatureStatus::Proposed)
        ->and($feature->type)->toBe(FeatureType::Feature)
        ->and($feature->effort_in_days)->toBe(0)
        ->and($feature->priority)->toBe(0)
        ->and($feature->cost)->toBe('0.00')
        ->and($feature->target_delivery_date)->toBeNull()
        ->and($feature->delivered_at)->toBeNull();
});

it('casts dates when a feature is delivered', function (): void {
    $feature = Feature::factory()->delivered()->create()->refresh();

    expect($feature->status)->toBe(FeatureStatus::Delivered)
        ->and($feature->delivered_at)->toBeInstanceOf(Carbon::class)
        ->and($feature->target_delivery_date)->toBeInstanceOf(Carbon::class);
});

it('has many milestones that are deleted with the feature', function (): void {
    $feature = Feature::factory()->has(Milestone::factory()->count(3))->create();

    expect($feature->milestones)->toHaveCount(3)
        ->each->toBeInstanceOf(Milestone::class);

    $feature->delete();

    expect(Milestone::count())->toBe(0);
});

it('deletes comments and votes along with the feature', function (): void {
    $feature = Feature::factory()
        ->has(Comment::factory()->count(2))
        ->has(Vote::factory()->count(2))
        ->create();

    $feature->delete();

    expect(Comment::count())->toBe(0)
        ->and(Vote::count())->toBe(0);
});
