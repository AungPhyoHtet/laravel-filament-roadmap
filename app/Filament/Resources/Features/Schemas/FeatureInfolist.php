<?php

namespace App\Filament\Resources\Features\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('description')
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('effort_in_days')
                    ->label('Effort')
                    ->suffix(' days'),
                TextEntry::make('priority'),
                TextEntry::make('cost')
                    ->money('USD'),
                TextEntry::make('target_delivery_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('delivered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                RepeatableEntry::make('milestones')
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
            ]);
    }
}
