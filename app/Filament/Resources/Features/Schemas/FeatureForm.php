<?php

namespace App\Filament\Resources\Features\Schemas;

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
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
                Tabs::make('Feature Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->columns(2)
                            ->schema(
                                self::getGeneralInfoSection(),
                            ),
                        Tabs\Tab::make('Effort & Cost')
                            ->columns(2)
                            ->schema(
                                self::getEffortAndCostSection(),
                            ),
                        Tabs\Tab::make('Milestones')
                            ->columns(2)
                            ->schema(
                                self::getMilestonesSection()
                            ),
                    ]),
            ]);
    }

    public static function getGeneralInfoSection(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Slider::make('priority')
                ->extraFieldWrapperAttributes([
                    'class' => 'pl-2',
                ])
                ->range(minValue: 1, maxValue: 10)
                ->fillTrack()
                ->pips(PipsMode::Steps)
                ->step(1)
                ->default(1)
                ->required(),
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
            DateTimePicker::make('delivered_at')
                ->visibleJs(
                    <<<'JS'
                        $get('status') === 'Delivered'
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
                ->extraInputAttributes([
                    'style' => 'min-height: 150px;',
                ])
                ->required()
                ->columnSpanFull(),

        ];
    }

    public static function getEffortAndCostSection(): array
    {
        return [
            TextInput::make('effort_in_days')
                ->label('Effort')
                ->integer()
                ->minValue(0)
                ->maxValue(65535)
                ->default(0)
                ->suffix('days')
                ->required()
                ->afterStateUpdatedJs(
                    <<<'JS'
                        const isHighCost = $get('is_high_cost');
                        const effort = $state;
                        const costPerDay = isHighCost ? 1500 : 1000;
                        $set('cost', effort * costPerDay);
                    JS
                ),
            TextInput::make('cost')
                ->numeric()
                ->minValue(0)
                ->maxValue(99999999.99)
                ->step(0.01)
                ->default(0)
                ->prefix('$')
                ->required(),
            Toggle::make('is_high_cost')
                ->label('Is High Cost')
                ->dehydrated(false)
                ->afterStateUpdatedJs(
                    <<<'JS'
                        const isHighCost = $state;
                        const effort = $get('effort_in_days');
                        const costPerDay = isHighCost ? 1500 : 1000;
                        $set('cost', effort * costPerDay);
                    JS
                ),
        ];
    }

    public static function getMilestonesSection(): array
    {
        return [
            Repeater::make('milestones')
                ->label('Milestones')
                ->relationship()
                ->compact()
                ->minItems(0)
                ->maxItems(3)
                ->table([
                    Repeater\TableColumn::make('Name*'),
                    Repeater\TableColumn::make('Due Date'),
                    Repeater\TableColumn::make('Is Completed'),
                ])
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('due_date')
                        ->required(),
                    Toggle::make('is_completed')
                        ->label('Is Completed')
                        ->default(false),
                ])
                ->columnSpanFull(),
        ];
    }
}
