<?php

declare(strict_types=1);

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use App\Filament\Resources\Features\Pages\CreateFeature;
use App\Filament\Resources\Features\Pages\EditFeature;
use App\Filament\Resources\Features\Pages\ListFeatures;
use App\Filament\Resources\Features\Pages\ViewFeature;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
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
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $feature = Feature::sole();

    expect($feature->name)->toBe('Dark mode')
        ->and($feature->type)->toBe(FeatureType::Enhancement)
        ->and($feature->status)->toBe(FeatureStatus::Planned)
        ->and($feature->effort_in_days)->toBe(5)
        ->and($feature->cost)->toBe('1250.50')
        ->and($feature->target_delivery_date->toDateString())->toBe('2026-12-01');
});

it('requires a name and description', function (): void {
    Livewire::test(CreateFeature::class)
        ->fillForm(['name' => null, 'description' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'description' => 'required']);
});

it('updates a feature', function (): void {
    $feature = Feature::factory()->create();

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
