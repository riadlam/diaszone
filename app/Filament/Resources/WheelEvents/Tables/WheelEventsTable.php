<?php

namespace App\Filament\Resources\WheelEvents\Tables;

use App\Models\WheelEvent;
use App\Services\WheelQualificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class WheelEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (WheelEvent $record): ?string => $record->description),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (WheelEvent $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'upcoming' => 'info',
                        'ended' => 'gray',
                        default => 'danger',
                    }),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('rewards_count')
                    ->label('Rewards')
                    ->counts('rewards')
                    ->badge(),

                TextColumn::make('claims_count')
                    ->label('Claims')
                    ->counts('claims')
                    ->badge()
                    ->color('warning'),

                IconColumn::make('is_active')
                    ->label('Enabled')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Enabled'),

                SelectFilter::make('game_type')
                    ->label('Game')
                    ->options([WheelQualificationService::GAME_TYPE => 'Mobile Legends']),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (WheelEvent $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (WheelEvent $record): string => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (WheelEvent $record): string => $record->is_active ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(function (WheelEvent $record): void {
                        if (! $record->is_active && static::hasOverlappingActiveEvent($record)) {
                            Notification::make()
                                ->title('Another active event already covers this date range.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Event is now live.' : 'Event deactivated.')
                            ->success()
                            ->send();
                    }),

                ActionGroup::make([
                    EditAction::make(),
                    Action::make('backfill')
                        ->label('Backfill spins')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->modalDescription('Scans qualifying Digiflazz top-ups inside the event window and credits any missing spins. Safe to run more than once.')
                        ->action(function (WheelEvent $record): void {
                            $credited = app(WheelQualificationService::class)->backfillEvent($record);

                            Notification::make()
                                ->title($credited > 0 ? "Credited {$credited} spin(s)." : 'No missing spins found.')
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function hasOverlappingActiveEvent(WheelEvent $event): bool
    {
        return WheelEvent::query()
            ->where('game_type', $event->game_type)
            ->where('is_active', true)
            ->whereKeyNot($event->getKey())
            ->where('starts_at', '<', $event->ends_at)
            ->where('ends_at', '>', $event->starts_at)
            ->exists();
    }
}
