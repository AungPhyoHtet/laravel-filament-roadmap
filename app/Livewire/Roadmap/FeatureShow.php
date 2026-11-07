<?php

declare(strict_types=1);

namespace App\Livewire\Roadmap;

use App\Livewire\Concerns\TogglesVotes;
use App\Models\Comment;
use App\Models\Feature;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeatureShow extends Component
{
    use TogglesVotes;

    public Feature $feature;

    #[Validate('required|string|max:2000')]
    public string $body = '';

    public function mount(Feature $feature): void
    {
        $this->authorize('view', $feature);

        $this->feature = $feature->load(['milestones' => fn ($query) => $query->orderBy('due_date')]);
    }

    #[Computed]
    public function voteCount(): int
    {
        return $this->feature->votes()->count();
    }

    #[Computed]
    public function hasVoted(): bool
    {
        $user = auth()->user();

        return $user !== null && $this->feature->votes()->whereBelongsTo($user)->exists();
    }

    /**
     * @return Collection<int, Comment>
     */
    #[Computed]
    public function comments(): Collection
    {
        return $this->feature->comments()
            ->visibleTo(auth()->user())
            ->with('user')
            ->latest()
            ->get();
    }

    /**
     * Post a comment. Admin comments are published straight away; everyone else's wait for approval.
     */
    public function addComment(): void
    {
        $user = auth()->user();

        if ($user === null) {
            $this->redirectToLogin();

            return;
        }

        $this->authorize('create', Comment::class);

        $this->validate();

        $this->feature->comments()->create([
            'user_id' => $user->id,
            'body' => $this->body,
            'is_approved' => $user->is_admin,
        ]);

        $this->reset('body');

        Notification::make()
            ->title($user->is_admin ? 'Comment posted' : 'Comment submitted')
            ->body($user->is_admin ? null : 'It will appear publicly once an admin approves it.')
            ->success()
            ->send();
    }

    public function toggleCommentApproval(int $commentId): void
    {
        $comment = $this->findComment($commentId);

        $this->authorize('approve', $comment);

        $comment->update(['is_approved' => ! $comment->is_approved]);
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->findComment($commentId);

        $this->authorize('delete', $comment);

        $comment->delete();
    }

    protected function findComment(int $commentId): Comment
    {
        return $this->feature->comments()->findOrFail($commentId);
    }

    protected function returnUrl(): string
    {
        return route('features.show', $this->feature);
    }

    public function render(): View
    {
        return view('livewire.roadmap.feature-show')
            ->title($this->feature->name);
    }
}
