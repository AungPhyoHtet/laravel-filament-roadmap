<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Filament\Resources\Features\RelationManagers\CommentsRelationManager;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CommentForm
{
    /**
     * Only admins pick the author; everyone else always comments as themselves.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function assignAuthor(array $data): array
    {
        if (! auth()->user()?->is_admin || blank($data['user_id'] ?? null)) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('feature_id')
                    ->relationship('feature', 'name')
                    ->hiddenOn(CommentsRelationManager::class)
                    ->required(),
                Select::make('user_id')
                    ->label('Author')
                    ->relationship('user', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->visible(fn (): bool => (bool) auth()->user()?->is_admin)
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
