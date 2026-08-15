<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('google_avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=92400e&background=fef3c7'),

                TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->sortable(),

                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('completed_orders_count')
                    ->label('Completed')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('delivered_topups_count')
                    ->label('Top-ups')
                    ->tooltip('Top-ups actually delivered by the provider, including multi-quantity orders')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('last_order_at')
                    ->label('Last order')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                TernaryFilter::make('is_admin')
                    ->label('Administrators'),

                TernaryFilter::make('google_id')
                    ->label('Google account')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('toggleStatus')
                    ->label(fn (User $record): string => $record->isActive() ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $record): string => $record->isActive() ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->isActive() ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->disabled(fn (User $record): bool => $record->is(auth()->user()))
                    ->action(function (User $record): void {
                        $record->update(['status' => $record->isActive() ? 'inactive' : 'active']);

                        Notification::make()
                            ->title($record->isActive() ? 'User activated.' : 'User deactivated.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
