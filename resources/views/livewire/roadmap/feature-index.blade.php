<div class="mx-auto max-w-5xl px-4 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-950">Product roadmap</h1>
        <p class="mt-2 text-gray-600">See what we're working on, vote for the features you want most, and join the discussion.</p>
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row">
        <x-filament::input.wrapper class="flex-1" prefix-icon="heroicon-m-magnifying-glass">
            <x-filament::input type="search" wire:model.live.debounce.300ms="search" placeholder="Search features..." />
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach ($statuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->getLabel() }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>

        <x-filament::input.wrapper>
            <x-filament::input.select wire:model.live="sort" aria-label="Sort features">
                <option value="votes">Most votes</option>
                <option value="newest">Newest</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>

    <div class="space-y-4">
        @forelse ($this->features as $feature)
            <article wire:key="feature-{{ $feature->id }}" class="flex gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">
                @include('livewire.roadmap.partials.vote-button', [
                    'featureId' => $feature->id,
                    'voteCount' => $feature->votes_count,
                    'hasVoted' => (bool) $feature->has_voted,
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

                    <h2 class="mt-2 text-lg font-semibold text-gray-950">
                        <a href="{{ route('features.show', $feature) }}" wire:navigate class="hover:underline">{{ $feature->name }}</a>
                    </h2>

                    <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ str($feature->description)->stripTags()->squish() }}</p>

                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                        <span class="inline-flex items-center gap-1">
                            <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="size-4" />
                            {{ trans_choice('{0} No comments|{1} 1 comment|[2,*] :count comments', $feature->comments_count) }}
                        </span>
                        @if ($feature->target_delivery_date)
                            <span class="inline-flex items-center gap-1">
                                <x-filament::icon icon="heroicon-m-calendar-days" class="size-4" />
                                Target {{ $feature->target_delivery_date->format('M j, Y') }}
                            </span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <x-filament::empty-state icon="heroicon-o-light-bulb" heading="No features found" description="Try a different search or status filter." />
        @endforelse
    </div>

    <div class="mt-6">
        <x-filament::pagination :paginator="$this->features" />
    </div>
</div>
