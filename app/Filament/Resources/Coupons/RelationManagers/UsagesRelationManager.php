<?php

namespace App\Filament\Resources\Coupons\RelationManagers;

use App\Models\CouponUsage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Usage history';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn (CouponUsage $record): ?string => $record->user?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('original_amount')
                    ->label('Original')
                    ->money('DZD'),

                TextColumn::make('discount_applied')
                    ->label('Discount')
                    ->money('DZD'),

                TextColumn::make('final_amount')
                    ->label('Final')
                    ->money('DZD'),

                TextColumn::make('created_at')
                    ->label('Used at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
