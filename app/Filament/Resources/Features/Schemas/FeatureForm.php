<?php

namespace App\Filament\Resources\Features\Schemas;

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(FeatureType::class)
                    ->default(FeatureType::Feature)
                    ->required(),
                Select::make('status')
                    ->options(FeatureStatus::class)
                    ->searchable()
                    ->default(FeatureStatus::Proposed)
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('effort_in_days')
                    ->label('Effort')
                    ->integer()
                    ->minValue(0)
                    ->maxValue(65535)
                    ->default(0)
                    ->suffix('days')
                    ->required(),
                TextInput::make('priority')
                    ->integer()
                    ->minValue(0)
                    ->maxValue(65535)
                    ->default(0)
                    ->required(),
                TextInput::make('cost')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->step(0.01)
                    ->default(0)
                    ->prefix('$')
                    ->required(),
                DatePicker::make('target_delivery_date'),
                DateTimePicker::make('delivered_at'),
            ]);
    }
}
