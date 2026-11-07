<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Filament\Resources\Features\RelationManagers\CommentsRelationManager;
use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feature.name')
                    ->hiddenOn(CommentsRelationManager::class)
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('body')
                    ->limit(80)
                    ->wrap(),
                IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approved')
                    ->trueLabel('Approved')
                    ->falseLabel('Awaiting approval'),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ...self::approvalActions(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->authorize('approveAny')
                        ->action(fn (Collection $records): int => Comment::query()->whereKey($records->modelKeys())->update(['is_approved' => true]))
                        ->successNotificationTitle(fn (Collection $records): string => "{$records->count()} comments approved")
                        ->failureNotificationTitle(function (int $successCount, int $totalCount): string {
                            if ($successCount) {
                                return "{$successCount} of {$totalCount} comments approved";
                            }

                            return 'Failed to approve any comments';
                        })
                        ->deselectRecordsAfterCompletion()
                        ->slideOver(false)
                        ->requiresConfirmation(),
                    BulkAction::make('reject')
                        ->label('Unapprove')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('warning')
                        ->authorize('approveAny')
                        ->action(fn (Collection $records): int => Comment::query()->whereKey($records->modelKeys())->update(['is_approved' => false]))
                        ->successNotificationTitle(fn (Collection $records): string => "{$records->count()} comments unapproved")
                        ->failureNotificationTitle(function (int $successCount, int $totalCount): string {
                            if ($successCount) {
                                return "{$successCount} of {$totalCount} comments unapproved";
                            }

                            return 'Failed to unapprove any comments';
                        })
                        ->deselectRecordsAfterCompletion()
                        ->slideOver(false)
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Admin-only actions that publish a comment or pull it back into the moderation queue.
     *
     * @return array<Action>
     */
    public static function approvalActions(): array
    {
        return [
            Action::make('approve')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->authorize('approve')
                ->hidden(fn (Comment $record): bool => $record->is_approved)
                ->action(fn (Comment $record): bool => $record->update(['is_approved' => true])),
            Action::make('reject')
                ->label('Unapprove')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('warning')
                ->authorize('approve')
                ->visible(fn (Comment $record): bool => $record->is_approved)
                ->action(fn (Comment $record): bool => $record->update(['is_approved' => false])),
        ];
    }
}
