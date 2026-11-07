<?php

declare(strict_types=1);

use App\Enums\FeatureStatus;
use App\Livewire\Roadmap\FeatureIndex;
use App\Livewire\Roadmap\FeatureShow;
use App\Models\Comment;
use App\Models\Feature;
use App\Models\User;
use App\Models\Vote;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the roadmap and feature pages to guests', function (): void {
    $feature = Feature::factory()->create(['name' => 'Public API']);

    $this->get(route('features.index'))
        ->assertOk()
        ->assertSeeLivewire(FeatureIndex::class)
        ->assertSee('Public API');

    $this->get(route('features.show', $feature))
        ->assertOk()
        ->assertSeeLivewire(FeatureShow::class)
        ->assertSee('Public API');
});

it('orders features by votes and filters by search and status', function (): void {
    $popular = Feature::factory()->has(Vote::factory()->count(3))->create(['name' => 'Popular idea']);
    $quiet = Feature::factory()->withStatus(FeatureStatus::Planned)->create(['name' => 'Quiet idea']);

    Livewire::test(FeatureIndex::class)
        ->assertSeeInOrder(['Popular idea', 'Quiet idea'])
        ->set('search', 'Quiet')
        ->assertSee('Quiet idea')
        ->assertDontSee('Popular idea')
        ->set('search', '')
        ->set('status', FeatureStatus::Planned->value)
        ->assertSee($quiet->name)
        ->assertDontSee($popular->name);
});

it('lets a user cast and withdraw a vote', function (): void {
    $user = User::factory()->create();
    $feature = Feature::factory()->create();

    $component = Livewire::actingAs($user)->test(FeatureShow::class, ['feature' => $feature])
        ->call('toggleVote', $feature->id);

    expect($feature->votes()->whereBelongsTo($user)->exists())->toBeTrue();

    $component->call('toggleVote', $feature->id);

    expect($feature->votes()->exists())->toBeFalse();
});

it('sends guests to the login page when they vote', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(FeatureIndex::class)
        ->call('toggleVote', $feature->id)
        ->assertRedirect(Filament::getLoginUrl());

    expect($feature->votes()->exists())->toBeFalse()
        ->and(session('url.intended'))->toBe(route('features.index'));
});

it('hides pending comments from guests and other users', function (): void {
    $feature = Feature::factory()->create();
    Comment::factory()->approved()->for($feature)->create(['body' => 'Approved thoughts']);
    $pending = Comment::factory()->pending()->for($feature)->create(['body' => 'Pending thoughts']);

    Livewire::test(FeatureShow::class, ['feature' => $feature])
        ->assertSee('Approved thoughts')
        ->assertDontSee('Pending thoughts');

    Livewire::actingAs(User::factory()->create())->test(FeatureShow::class, ['feature' => $feature])
        ->assertDontSee('Pending thoughts');

    Livewire::actingAs($pending->user)->test(FeatureShow::class, ['feature' => $feature])
        ->assertSee('Pending thoughts')
        ->assertSee('Awaiting approval');

    Livewire::actingAs(User::factory()->admin()->create())->test(FeatureShow::class, ['feature' => $feature])
        ->assertSee('Pending thoughts')
        ->assertSee('Approve');
});

it('holds comments from regular users for approval', function (): void {
    $user = User::factory()->create();
    $feature = Feature::factory()->create();

    Livewire::actingAs($user)->test(FeatureShow::class, ['feature' => $feature])
        ->set('body', 'Please add CSV export.')
        ->call('addComment')
        ->assertHasNoErrors()
        ->assertSet('body', '');

    $comment = $feature->comments()->sole();

    expect($comment->body)->toBe('Please add CSV export.')
        ->and($comment->user->is($user))->toBeTrue()
        ->and($comment->is_approved)->toBeFalse();
});

it('publishes admin comments immediately', function (): void {
    $feature = Feature::factory()->create();

    Livewire::actingAs(User::factory()->admin()->create())->test(FeatureShow::class, ['feature' => $feature])
        ->set('body', 'This is scheduled for next sprint.')
        ->call('addComment');

    expect($feature->comments()->sole()->is_approved)->toBeTrue();
});

it('validates the comment body', function (string $body): void {
    $feature = Feature::factory()->create();

    Livewire::actingAs(User::factory()->create())->test(FeatureShow::class, ['feature' => $feature])
        ->set('body', $body)
        ->call('addComment')
        ->assertHasErrors(['body']);

    expect($feature->comments()->exists())->toBeFalse();
})->with([
    'empty' => '',
    'too long' => str_repeat('a', 2001),
]);

it('stops guests from commenting', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(FeatureShow::class, ['feature' => $feature])
        ->set('body', 'Anonymous comment')
        ->call('addComment')
        ->assertRedirect(Filament::getLoginUrl());

    expect($feature->comments()->exists())->toBeFalse();
});

it('lets only admins approve comments from the feature page', function (): void {
    $comment = Comment::factory()->pending()->create();

    Livewire::actingAs($comment->user)->test(FeatureShow::class, ['feature' => $comment->feature])
        ->call('toggleCommentApproval', $comment->id)
        ->assertForbidden();

    expect($comment->refresh()->is_approved)->toBeFalse();

    Livewire::actingAs(User::factory()->admin()->create())->test(FeatureShow::class, ['feature' => $comment->feature])
        ->call('toggleCommentApproval', $comment->id);

    expect($comment->refresh()->is_approved)->toBeTrue();
});

it('lets authors delete their own comments but not others', function (): void {
    $comment = Comment::factory()->approved()->create();

    Livewire::actingAs(User::factory()->create())->test(FeatureShow::class, ['feature' => $comment->feature])
        ->call('deleteComment', $comment->id)
        ->assertForbidden();

    expect($comment->exists())->toBeTrue();

    Livewire::actingAs($comment->user)->test(FeatureShow::class, ['feature' => $comment->feature])
        ->call('deleteComment', $comment->id);

    expect(Comment::query()->whereKey($comment->id)->exists())->toBeFalse();
});

it('only acts on comments that belong to the feature being viewed', function (): void {
    $feature = Feature::factory()->create();
    $otherComment = Comment::factory()->pending()->create();

    Livewire::actingAs(User::factory()->admin()->create())->test(FeatureShow::class, ['feature' => $feature])
        ->call('toggleCommentApproval', $otherComment->id)
        ->assertNotFound();

    expect($otherComment->refresh()->is_approved)->toBeFalse();
});
