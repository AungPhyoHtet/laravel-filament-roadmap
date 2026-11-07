<?php

declare(strict_types=1);

namespace App\Livewire\Roadmap;

use App\Enums\FeatureStatus;
use App\Livewire\Concerns\TogglesVotes;
use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Roadmap')]
class FeatureIndex extends Component
{
    use TogglesVotes;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'votes')]
    public string $sort = 'votes';

    public function mount(): void
    {
        $this->authorize('viewAny', Feature::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status', 'sort'], true)) {
            $this->resetPage();
        }
    }

    /**
     * @return LengthAwarePaginator<int, Feature>
     */
    #[Computed]
    public function features(): LengthAwarePaginator
    {
        $user = auth()->user();
        $status = FeatureStatus::tryFrom($this->status);

        return Feature::query()
            ->when(filled($this->search), fn (Builder $query): Builder => $query->whereLike('name', "%{$this->search}%"))
            ->when($status, fn (Builder $query): Builder => $query->where('status', $status))
            ->withCount([
                'votes',
                'comments' => fn (Builder $query): Builder => $query->visibleTo($user),
            ])
            ->when($user, fn (Builder $query): Builder => $query->withExists([
                'votes as has_voted' => fn (Builder $query): Builder => $query->whereBelongsTo($user),
            ]))
            ->when(
                $this->sort === 'newest',
                fn (Builder $query): Builder => $query->latest(),
                fn (Builder $query): Builder => $query->orderByDesc('votes_count')->latest(),
            )
            ->paginate(12);
    }

    protected function returnUrl(): string
    {
        return route('features.index');
    }

    public function render(): View
    {
        return view('livewire.roadmap.feature-index', [
            'statuses' => FeatureStatus::cases(),
        ]);
    }
}
