<?php

namespace App\Filament\Resources\Features\Schemas;

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class FeatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->live()
                    ->options(FeatureStatus::class)
                    ->searchable()
                    ->default(FeatureStatus::Proposed)
                    ->required()
                    ->live(),
                DatePicker::make('target_delivery_date')
                    ->rules([
                        function (Get $get) {
                            return Rule::requiredIf(
                                $get('status') === FeatureStatus::Planned
                                    || $get('status') === FeatureStatus::InProgress
                            );
                        },
                    ])
                    ->visibleJs(
                        <<<'JS'
                                $get('status') === 'Planned' || $get('status') === 'In Progress'
                            JS
                    ),
                ToggleButtons::make('type')
                    ->hiddenLabel()
                    ->options(FeatureType::class)
                    ->inline()
                    ->default(FeatureType::Feature)
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('effort_in_days')
                    ->label('Effort')
                    ->integer()
                    ->minValue(0)
                    ->maxValue(65535)
                    ->default(0)
                    ->suffix('days')
                    ->required(),
                Slider::make('priority')
                    ->range(minValue: 1, maxValue: 10)
                    ->fillTrack()
                    ->pips(PipsMode::Steps)
                    ->step(1)
                    ->default(1)
                    ->required(),
                TextInput::make('cost')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->step(0.01)
                    ->default(0)
                    ->prefix('$')
                    ->required(),
                DateTimePicker::make('delivered_at'),
            ]);
    }
}
