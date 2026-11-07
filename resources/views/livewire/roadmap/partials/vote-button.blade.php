<button
    type="button"
    wire:click="toggleVote({{ $featureId }})"
    wire:loading.attr="disabled"
    @class([
        'flex h-16 w-14 shrink-0 flex-col items-center justify-center rounded-lg text-sm font-semibold ring-1 transition disabled:opacity-60',
        'bg-primary-600 text-white ring-primary-600 hover:bg-primary-500' => $hasVoted,
        'bg-white text-gray-700 ring-gray-950/10 hover:bg-gray-50' => ! $hasVoted,
    ])
    aria-pressed="{{ $hasVoted ? 'true' : 'false' }}"
    title="{{ auth()->check() ? ($hasVoted ? 'Remove your vote' : 'Vote for this feature') : 'Log in to vote' }}"
>
    <x-filament::icon icon="heroicon-m-chevron-up" class="size-5" />
    <span>{{ $voteCount }}</span>
</button>
