<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Feature;
use App\Models\Vote;
use Filament\Facades\Filament;

trait TogglesVotes
{
    /**
     * The page a guest should land back on after logging in.
     */
    abstract protected function returnUrl(): string;

    /**
     * Cast the current user's vote for a feature, or withdraw it if they already voted.
     */
    public function toggleVote(int $featureId): void
    {
        $feature = Feature::query()->findOrFail($featureId);

        $this->authorize('view', $feature);

        $user = auth()->user();

        if ($user === null) {
            $this->redirectToLogin();

            return;
        }

        $vote = $feature->votes()->whereBelongsTo($user)->first();

        if ($vote !== null) {
            $this->authorize('delete', $vote);

            $vote->delete();

            return;
        }

        $this->authorize('create', Vote::class);

        $feature->votes()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Send a guest to the login page, bringing them back here afterwards.
     */
    public function redirectToLogin(): void
    {
        redirect()->setIntendedUrl($this->returnUrl());

        $this->redirect(Filament::getLoginUrl());
    }
}
