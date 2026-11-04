<?php

namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')->action(function () {
                dd('test');
            })
                ->authorize(Gate::allows('test', FeatureResource::getModel())),
            CreateAction::make(),
        ];
    }
}
