<?php

namespace App\Filament\Resources\Votes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class VoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('feature_id')
                    ->relationship('feature', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }
}
