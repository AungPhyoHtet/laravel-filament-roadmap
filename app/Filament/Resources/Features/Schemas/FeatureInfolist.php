<?php

namespace App\Filament\Resources\Features\Schemas;

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
            ]);
    }
}
