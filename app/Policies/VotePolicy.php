<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;

class VotePolicy
{
    /**
     * Determine whether the user can view any votes.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the vote.
     */
    public function view(?User $user, Vote $vote): bool
    {
        return true;
    }

    /**
     * Determine whether the user can cast votes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Votes are cast or withdrawn, never edited.
     */
    public function update(User $user, Vote $vote): bool
    {
        return false;
    }

    /**
     * Determine whether the user can withdraw the vote.
     */
    public function delete(User $user, Vote $vote): bool
    {
        return $user->is_admin || $vote->user()->is($user);
    }

    /**
     * Determine whether the user can bulk delete votes.
     */
    public function deleteAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the vote.
     */
    public function restore(User $user, Vote $vote): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the vote.
     */
    public function forceDelete(User $user, Vote $vote): bool
    {
        return $user->is_admin;
    }
}
