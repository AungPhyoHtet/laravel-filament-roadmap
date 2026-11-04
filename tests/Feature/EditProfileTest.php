<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->actingAs($this->user);
});

it('uses the custom profile page with a phone field', function (): void {
    expect(Filament::getPanel('admin')->getProfilePage())->toBe(EditProfile::class);

    Livewire::test(EditProfile::class)
        ->assertOk()
        ->assertFormFieldExists('phone');
});

it('saves a phone number', function (): void {
    $this->user->update(['phone' => null]);

    Livewire::test(EditProfile::class)
        ->fillForm(['phone' => '(555) 123-4567'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->user->refresh()->phone)->toBe('(555) 123-4567');
});

it('allows the phone number to be empty', function (): void {
    Livewire::test(EditProfile::class)
        ->fillForm(['phone' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->user->refresh()->phone)->toBeNull();
});

it('rejects a badly formatted phone number', function (): void {
    $originalPhone = $this->user->phone;

    Livewire::test(EditProfile::class)
        ->fillForm(['phone' => '5551234567'])
        ->call('save')
        ->assertHasFormErrors(['phone' => 'regex']);

    expect($this->user->refresh()->phone)->toBe($originalPhone);
});
