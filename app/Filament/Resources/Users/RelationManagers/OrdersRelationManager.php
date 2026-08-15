<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'diamondPack',
                'orderItems',
                'digiflazzStatuses',
                'item4gamerOrders',
            ]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight('bold')
                    ->searchable()
                    ->copyable()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (Order $record): string => $record->statusColor()),

                TextColumn::make('diamondPack.name')
                    ->label('Pack')
                    ->placeholder('Multiple items'),

                TextColumn::make('final_price')
                    ->label('Amount')
                    ->state(fn (Order $record): string => number_format($record->displayAmount(), 0).' DZD'),

                TextColumn::make('topup_progress')
                    ->label('Delivered')
                    ->tooltip('Provider deliveries versus quantity ordered')
                    ->state(fn (Order $record): string => $record->topupProgressLabel())
                    ->badge()
                    ->color(fn (Order $record): string => $record->statusColor()),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
