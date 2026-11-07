<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view any comments.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Approved comments are public; pending ones are visible only to their author and admins.
     */
    public function view(?User $user, Comment $comment): bool
    {
        if ($comment->is_approved) {
            return true;
        }

        return $user !== null && ($user->is_admin || $comment->user()->is($user));
    }

    /**
     * Determine whether the user can create comments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->is_admin || $comment->user()->is($user);
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->is_admin || $comment->user()->is($user);
    }

    /**
     * Determine whether the user can approve or reject the comment.
     */
    public function approve(User $user, Comment $comment): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can bulk approve or reject comments.
     */
    public function approveAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can bulk delete comments.
     */
    public function deleteAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the comment.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the comment.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->is_admin;
    }
}
