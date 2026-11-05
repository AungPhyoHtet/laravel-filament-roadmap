<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feature Details')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema(self::getFeatureDetailsSection()),

                Section::make('Description')
                    ->description('A brief description of the feature.')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('Milestones')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('milestones')
                            ->hiddenLabel()
                            ->label('Milestones')
                            ->table([
                                RepeatableEntry\TableColumn::make('Title'),
                                RepeatableEntry\TableColumn::make('Due Date'),
                                RepeatableEntry\TableColumn::make('Is Completed'),
                            ])
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('due_date')
                                    ->date()
                                    ->placeholder('-'),
                                IconEntry::make('is_completed')
                                    ->boolean(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getFeatureDetailsSection(): array
    {
        return [
            TextEntry::make('name'),
            TextEntry::make('type')
                ->badge(),
            TextEntry::make('status')
                ->badge(),
            TextEntry::make('effort_in_days')
                ->label('Effort')
                ->suffix(' days'),
            TextEntry::make('priority'),
            TextEntry::make('cost')
                ->money('USD'),
            TextEntry::make('target_delivery_date')
                ->date()
                ->placeholder('N/A'),
            TextEntry::make('delivered_at')
                ->dateTime()
                ->placeholder('N/A'),
            TextEntry::make('created_at')
                ->dateTime(),
            TextEntry::make('updated_at')
                ->dateTime(),
        ];
    }
}
