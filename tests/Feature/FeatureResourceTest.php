<?php

declare(strict_types=1);

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use App\Filament\Resources\Features\Pages\CreateFeature;
use App\Filament\Resources\Features\Pages\EditFeature;
use App\Filament\Resources\Features\Pages\ListFeatures;
use App\Filament\Resources\Features\Pages\ViewFeature;
use App\Models\Feature;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->actingAs(User::factory()->admin()->create());
});

it('lists features', function (): void {
    $features = Feature::factory()->count(3)->create();

    Livewire::test(ListFeatures::class)
        ->assertCanSeeTableRecords($features)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('status');
});

it('filters features by status', function (): void {
    $delivered = Feature::factory()->delivered()->create();
    $proposed = Feature::factory()->create();

    Livewire::test(ListFeatures::class)
        ->filterTable('status', FeatureStatus::Delivered->value)
        ->assertCanSeeTableRecords([$delivered])
        ->assertCanNotSeeTableRecords([$proposed]);
});

it('creates a feature', function (): void {
    Livewire::test(CreateFeature::class)
        ->fillForm([
            'name' => 'Dark mode',
            'type' => FeatureType::Enhancement,
            'status' => FeatureStatus::Planned,
            'description' => 'Support a dark colour scheme.',
            'effort_in_days' => 5,
            'priority' => 3,
            'cost' => 1250.50,
            'target_delivery_date' => '2026-12-01',
            'milestones' => [
                ['name' => 'Beta release', 'due_date' => '2026-11-20', 'is_completed' => false],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $feature = Feature::sole();

    expect($feature->name)->toBe('Dark mode')
        ->and($feature->type)->toBe(FeatureType::Enhancement)
        ->and($feature->status)->toBe(FeatureStatus::Planned)
        ->and($feature->effort_in_days)->toBe(5)
        ->and($feature->cost)->toBe('1250.50')
        ->and($feature->target_delivery_date->toDateString())->toBe('2026-12-01')
        ->and($feature->milestones()->sole()->name)->toBe('Beta release');
});

it('requires a name and description', function (): void {
    Livewire::test(CreateFeature::class)
        ->fillForm(['name' => null, 'description' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'description' => 'required']);
});

it('updates a feature', function (): void {
    $feature = Feature::factory()->has(Milestone::factory())->create();

    Livewire::test(EditFeature::class, ['record' => $feature->getRouteKey()])
        ->assertSchemaStateSet(['name' => $feature->name])
        ->fillForm(['status' => FeatureStatus::InProgress, 'priority' => 9])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($feature->refresh()->status)->toBe(FeatureStatus::InProgress)
        ->and($feature->priority)->toBe(9);
});

it('shows a feature', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(ViewFeature::class, ['record' => $feature->getRouteKey()])
        ->assertSee($feature->name);
});

it('saves milestones to the feature', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(EditFeature::class, ['record' => $feature->getRouteKey()])
        ->fillForm([
            'milestones' => [
                ['name' => 'Beta release', 'due_date' => '2026-12-01', 'is_completed' => true],
                ['name' => 'General availability', 'due_date' => '2027-01-15', 'is_completed' => false],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $milestones = $feature->milestones()->orderBy('due_date')->get();

    expect($milestones)->toHaveCount(2)
        ->and($milestones[0]->name)->toBe('Beta release')
        ->and($milestones[0]->is_completed)->toBeTrue()
        ->and($milestones[1]->due_date->toDateString())->toBe('2027-01-15');
});

it('loads existing milestones into the form', function (): void {
    $feature = Feature::factory()->has(Milestone::factory()->count(2))->create();

    $milestoneNames = collect(
        Livewire::test(EditFeature::class, ['record' => $feature->getRouteKey()])->get('data.milestones')
    )->pluck('name')->sort()->values()->all();

    expect($milestoneNames)->toBe($feature->milestones->pluck('name')->sort()->values()->all());
});

it('requires at least one milestone', function (): void {
    $feature = Feature::factory()->create();

    Livewire::test(EditFeature::class, ['record' => $feature->getRouteKey()])
        ->fillForm(['milestones' => []])
        ->call('save')
        ->assertHasFormErrors(['milestones' => 'min']);
});

it('stops non-admins from creating features', function (): void {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateFeature::class)->assertForbidden();
});
