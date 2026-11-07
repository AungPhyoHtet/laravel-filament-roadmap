<div class="mx-auto max-w-4xl px-4 py-10">
    <a href="{{ route('features.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-950">
        <x-filament::icon icon="heroicon-m-arrow-left" class="size-4" />
        Back to roadmap
    </a>

    <div class="mt-6 flex gap-5">
        @include('livewire.roadmap.partials.vote-button', [
            'featureId' => $feature->id,
            'voteCount' => $this->voteCount,
            'hasVoted' => $this->hasVoted,
        ])

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::badge :color="$feature->status->getColor()" :icon="$feature->status->getIcon()">
                    {{ $feature->status->getLabel() }}
                </x-filament::badge>
                <x-filament::badge :color="$feature->type->getColor()">
                    {{ $feature->type->getLabel() }}
                </x-filament::badge>
            </div>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950">{{ $feature->name }}</h1>

            <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                @if ($feature->target_delivery_date)
                    <div class="flex gap-1">
                        <dt>Target delivery:</dt>
                        <dd class="font-medium text-gray-950">{{ $feature->target_delivery_date->format('M j, Y') }}</dd>
                    </div>
                @endif
                @if ($feature->delivered_at)
                    <div class="flex gap-1">
                        <dt>Delivered:</dt>
                        <dd class="font-medium text-gray-950">{{ $feature->delivered_at->format('M j, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <x-filament::section class="mt-8" heading="Description">
        <div class="fi-prose">
            {!! str($feature->description)->sanitizeHtml() !!}
        </div>
    </x-filament::section>

    @if ($feature->milestones->isNotEmpty())
        <x-filament::section class="mt-6" heading="Milestones">
            <ul class="divide-y divide-gray-100">
                @foreach ($feature->milestones as $milestone)
                    <li wire:key="milestone-{{ $milestone->id }}" class="flex items-center justify-between gap-4 py-2 text-sm">
                        <span class="inline-flex items-center gap-2">
                            <x-filament::icon
                                :icon="$milestone->is_completed ? 'heroicon-s-check-circle' : 'heroicon-o-clock'"
                                @class(['size-5', 'text-success-600' => $milestone->is_completed, 'text-gray-400' => ! $milestone->is_completed])
                            />
                            {{ $milestone->name }}
                        </span>
                        <span class="text-gray-500">{{ $milestone->due_date->format('M j, Y') }}</span>
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif

    <x-filament::section class="mt-6" :heading="'Comments ('.$this->comments->where('is_approved', true)->count().')'">
        @auth
            <form wire:submit="addComment" class="mb-6 space-y-3">
                <x-filament::input.wrapper :valid="! $errors->has('body')">
                    <textarea
                        wire:model="body"
                        rows="3"
                        maxlength="2000"
                        placeholder="Share your thoughts on this feature..."
                        aria-label="Comment"
                        class="fi-input block w-full border-none bg-transparent px-3 py-2 text-sm"
                    ></textarea>
                </x-filament::input.wrapper>
                @error('body')
                    <p class="text-sm text-danger-600">{{ $message }}</p>
                @enderror
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-500">
                        @if (auth()->user()->is_admin)
                            Your comment will be published immediately.
                        @else
                            Comments are reviewed by an admin before they appear publicly.
                        @endif
                    </p>
                    <x-filament::button type="submit" wire:target="addComment">Post comment</x-filament::button>
                </div>
            </form>
        @else
            <div class="mb-6 rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                <button type="button" wire:click="redirectToLogin" class="font-semibold text-primary-600 hover:underline">Log in</button>
                to vote and join the discussion.
            </div>
        @endauth

        <ul class="space-y-4">
            @forelse ($this->comments as $comment)
                <li wire:key="comment-{{ $comment->id }}" @class(['rounded-lg p-4 ring-1', 'ring-gray-950/5' => $comment->is_approved, 'bg-warning-50 ring-warning-200' => ! $comment->is_approved])>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-950">{{ $comment->user->name }}</span>
                            <span class="text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                            @unless ($comment->is_approved)
                                <x-filament::badge color="warning" size="sm">Awaiting approval</x-filament::badge>
                            @endunless
                        </div>

                        <div class="flex items-center gap-1">
                            @can('approve', $comment)
                                <x-filament::link
                                    tag="button"
                                    size="sm"
                                    :color="$comment->is_approved ? 'warning' : 'success'"
                                    wire:click="toggleCommentApproval({{ $comment->id }})"
                                >
                                    {{ $comment->is_approved ? 'Unapprove' : 'Approve' }}
                                </x-filament::link>
                            @endcan
                            @can('delete', $comment)
                                <x-filament::icon-button
                                    icon="heroicon-m-trash"
                                    color="danger"
                                    size="sm"
                                    label="Delete comment"
                                    wire:click="deleteComment({{ $comment->id }})"
                                    wire:confirm="Delete this comment?"
                                />
                            @endcan
                        </div>
                    </div>
                    <p class="mt-2 text-sm whitespace-pre-line text-gray-700">{{ $comment->body }}</p>
                </li>
            @empty
                <li class="text-sm text-gray-500">No comments yet. Be the first to share your thoughts.</li>
            @endforelse
        </ul>
    </x-filament::section>
</div>
