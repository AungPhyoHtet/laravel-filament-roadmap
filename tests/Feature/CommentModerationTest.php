<?php

declare(strict_types=1);

use App\Filament\Resources\Comments\Pages\EditComment;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Models\Comment;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('makes approved comments public and pending ones private to the author and admins', function (): void {
    $pending = Comment::factory()->pending()->create();
    $approved = Comment::factory()->approved()->create();

    expect(Gate::forUser(null)->allows('view', $approved))->toBeTrue()
        ->and(Gate::forUser(null)->allows('view', $pending))->toBeFalse()
        ->and(User::factory()->create()->can('view', $pending))->toBeFalse()
        ->and($pending->user->can('view', $pending))->toBeTrue()
        ->and(User::factory()->admin()->create()->can('view', $pending))->toBeTrue();
});

it('lets only admins approve comments', function (): void {
    $comment = Comment::factory()->pending()->create();

    expect($comment->user->can('approve', $comment))->toBeFalse()
        ->and($comment->user->can('approveAny', Comment::class))->toBeFalse()
        ->and(User::factory()->admin()->create()->can('approve', $comment))->toBeTrue();
});

it('hides other users\' pending comments from non-admins in the panel', function (): void {
    $user = User::factory()->create();
    $ownPending = Comment::factory()->pending()->for($user)->create();
    $othersPending = Comment::factory()->pending()->create();
    $approved = Comment::factory()->approved()->create();

    $this->actingAs($user);

    Livewire::test(ListComments::class)
        ->assertCanSeeTableRecords([$ownPending, $approved])
        ->assertCanNotSeeTableRecords([$othersPending])
        ->assertActionHidden(TestAction::make('approve')->table($ownPending));
});

it('lets admins approve and unapprove comments from the panel', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $comment = Comment::factory()->pending()->create();

    Livewire::test(ListComments::class)
        ->filterTable('is_approved', false)
        ->assertCanSeeTableRecords([$comment])
        ->callAction(TestAction::make('approve')->table($comment));

    expect($comment->refresh()->is_approved)->toBeTrue();

    Livewire::test(ListComments::class)
        ->callAction(TestAction::make('reject')->table($comment));

    expect($comment->refresh()->is_approved)->toBeFalse();
});

it('lets admins bulk approve comments', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $comments = Comment::factory()->pending()->count(2)->create();

    Livewire::test(ListComments::class)
        ->selectTableRecords($comments)
        ->callAction(TestAction::make('approve')->table()->bulk())
        ->assertNotified('2 comments approved');

    expect(Comment::query()->where('is_approved', false)->exists())->toBeFalse();
});

it('lets admins bulk unapprove comments', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $comments = Comment::factory()->approved()->count(3)->create();

    Livewire::test(ListComments::class)
        ->selectTableRecords($comments)
        ->callAction(TestAction::make('reject')->table()->bulk())
        ->assertNotified('3 comments unapproved');

    expect(Comment::query()->where('is_approved', true)->exists())->toBeFalse();
});

it('sends an approved comment back for approval when its author edits it', function (): void {
    $comment = Comment::factory()->approved()->create();
    $this->actingAs($comment->user);

    Livewire::test(EditComment::class, ['record' => $comment->getRouteKey()])
        ->fillForm(['body' => 'Edited after approval.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($comment->refresh())
        ->body->toBe('Edited after approval.')
        ->is_approved->toBeFalse();
});

it('keeps approval when an admin edits a comment', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $comment = Comment::factory()->approved()->create();

    Livewire::test(EditComment::class, ['record' => $comment->getRouteKey()])
        ->fillForm(['body' => 'Fixed a typo.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($comment->refresh()->is_approved)->toBeTrue();
});
