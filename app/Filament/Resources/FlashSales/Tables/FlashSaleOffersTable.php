<?php

namespace App\Filament\Resources\FlashSales\Tables;

use App\Models\FlashSaleOffer;
use App\Support\GameProvider;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FlashSaleOffersTable
{
    public static function configure(Table $table): Table
    {
        $gameOptions = collect(GameProvider::digiflazzGames())
            ->unique()
            ->mapWithKeys(fn (string $type) => [$type => GameProvider::label($type)])
            ->all();

        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (FlashSaleOffer $record): ?string => $record->imageUrl())
                    ->square()
                    ->size(48),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('game_type')
                    ->label('Game')
                    ->formatStateUsing(fn (?string $state): string => GameProvider::label($state))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (FlashSaleOffer $record): string => $record->statusLabel())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'live' => 'success',
                        default => 'danger',
                    }),

                TextColumn::make('sale_price_dzd')
                    ->label('Sale')
                    ->money('DZD')
                    ->description(fn (FlashSaleOffer $record): string => 'Was '.number_format((float) $record->original_price_dzd, 0).' DZD'),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('On')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                SelectFilter::make('game_type')->label('Game')->options($gameOptions),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (FlashSaleOffer $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (FlashSaleOffer $record): string => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (FlashSaleOffer $record): string => $record->is_active ? 'gray' : 'success')
                    ->action(function (FlashSaleOffer $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Flash sale activated.' : 'Flash sale deactivated.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
