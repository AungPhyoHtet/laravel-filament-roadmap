<?php

declare(strict_types=1);

use App\Filament\Resources\Features\Pages\EditFeature;
use App\Filament\Resources\Features\RelationManagers\CommentsRelationManager;
use App\Models\Comment;
use App\Models\Feature;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('lists only the comments for the feature', function (): void {
    $feature = Feature::factory()->has(Comment::factory()->approved()->count(2))->create();
    $otherComment = Comment::factory()->approved()->create();

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $feature,
        'pageClass' => EditFeature::class,
    ])
        ->assertCanSeeTableRecords($feature->comments)
        ->assertCanNotSeeTableRecords([$otherComment]);
});

it('creates a comment on the feature as the current user', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $feature,
        'pageClass' => EditFeature::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'body' => 'Would love this in the mobile app too.',
        ])
        ->assertHasNoFormErrors();

    $comment = $feature->comments()->sole();

    expect($comment->body)->toBe('Would love this in the mobile app too.')
        ->and($comment->user->is($this->user))->toBeTrue()
        ->and($comment->is_approved)->toBeFalse();
});

it('ignores a chosen author when a non-admin comments', function (): void {
    $feature = Feature::factory()->create();
    $someoneElse = User::factory()->create();

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $feature,
        'pageClass' => EditFeature::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'user_id' => $someoneElse->id,
            'body' => 'Posting as someone else.',
        ]);

    expect($feature->comments()->sole()->user->is($this->user))->toBeTrue();
});

it('lets an admin comment on behalf of another user', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $feature = Feature::factory()->create();
    $author = User::factory()->create();

    Livewire::test(CommentsRelationManager::class, [
        'ownerRecord' => $feature,
        'pageClass' => EditFeature::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'user_id' => $author->id,
            'body' => 'Feedback from the support call.',
        ])
        ->assertHasNoFormErrors();

    expect($feature->comments()->sole()->user->is($author))->toBeTrue();
});
