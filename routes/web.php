<?php

declare(strict_types=1);

use App\Livewire\Roadmap\FeatureIndex;
use App\Livewire\Roadmap\FeatureShow;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): Factory|View => view('welcome'));

Route::livewire('/features', FeatureIndex::class)->name('features.index');
Route::livewire('/features/{feature}', FeatureShow::class)->name('features.show');
