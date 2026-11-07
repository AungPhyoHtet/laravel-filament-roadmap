<?php

namespace App\Filament\Resources\Features\RelationManagers;

use App\Filament\Resources\Comments\Schemas\CommentForm;
use App\Filament\Resources\Comments\Tables\CommentsTable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Schema $schema): Schema
    {
        return CommentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CommentsTable::configure($table)
            ->recordTitleAttribute('body')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->visibleTo(auth()->user()))
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => CommentForm::applyApproval(CommentForm::assignAuthor($data))),
            ])
            ->recordActions([
                ...CommentsTable::approvalActions(),
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => CommentForm::applyApproval($data)),
                DeleteAction::make(),
            ]);
    }
}
