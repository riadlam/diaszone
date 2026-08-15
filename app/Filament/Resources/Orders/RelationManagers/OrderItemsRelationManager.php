<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Order items';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('diamondPack.name')
                    ->label('Pack')
                    ->weight('bold')
                    ->description(fn (OrderItem $record): ?string => $record->diamondPack?->game_type),

                TextColumn::make('quantity')
                    ->badge(),

                TextColumn::make('unit_price_dzd')
                    ->label('Unit price')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD'),

                TextColumn::make('discount_amount_dzd')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_dzd')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD')
                    ->weight('bold'),

                TextColumn::make('topups')
                    ->label('Delivered')
                    ->state(fn (OrderItem $record): string => $record->successfulTopupsCount().'/'.$record->quantity)
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
