<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets any user view approved comments and create comments', function (): void {
    $user = User::factory()->create();
    $comment = Comment::factory()->approved()->create();

    expect($user->can('viewAny', Comment::class))->toBeTrue()
        ->and($user->can('view', $comment))->toBeTrue()
        ->and($user->can('create', Comment::class))->toBeTrue();
});

it('lets the author update and delete their own comment', function (): void {
    $comment = Comment::factory()->create();

    expect($comment->user->can('update', $comment))->toBeTrue()
        ->and($comment->user->can('delete', $comment))->toBeTrue()
        ->and($comment->user->can('forceDelete', $comment))->toBeFalse();
});

it('stops other users from changing a comment', function (): void {
    $otherUser = User::factory()->create();
    $comment = Comment::factory()->create();

    expect($otherUser->can('update', $comment))->toBeFalse()
        ->and($otherUser->can('delete', $comment))->toBeFalse();
});

it('lets admins manage any comment', function (): void {
    $admin = User::factory()->admin()->create();
    $comment = Comment::factory()->create();

    expect($admin->can('update', $comment))->toBeTrue()
        ->and($admin->can('delete', $comment))->toBeTrue()
        ->and($admin->can('restore', $comment))->toBeTrue()
        ->and($admin->can('forceDelete', $comment))->toBeTrue();
});

it('belongs to a feature and a user', function (): void {
    $comment = Comment::factory()->create();

    expect($comment->feature->comments->sole()->is($comment))->toBeTrue()
        ->and($comment->user->comments->sole()->is($comment))->toBeTrue();
});

it('lets only admins bulk delete comments', function (): void {
    expect(User::factory()->admin()->create()->can('deleteAny', Comment::class))->toBeTrue()
        ->and(User::factory()->create()->can('deleteAny', Comment::class))->toBeFalse();
});
