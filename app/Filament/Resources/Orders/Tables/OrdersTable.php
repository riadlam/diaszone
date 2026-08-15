<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OrdersTable
{
    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'pending_flexy' => 'Pending Flexy',
        'pending_bmccp' => 'Pending BaridiMob',
        'pending_cryptopay' => 'Pending Crypto',
        'pending_confirmation' => 'Pending confirmation',
        'pending_flexy_verification' => 'Flexy verification',
        'sending' => 'Sending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'failed' => 'Failed',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight('bold')
                    ->searchable()
                    ->copyable()
                    ->description(fn (Order $record): string => $record->gameLabel()),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (Order $record): string => $record->statusColor())
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(['name', 'email'])
                    ->placeholder('Guest')
                    ->description(fn (Order $record): ?string => $record->user?->email)
                    ->wrap(),

                TextColumn::make('amount')
                    ->state(fn (Order $record): string => number_format($record->displayAmount(), 0).' DZD')
                    ->weight('bold'),

                TextColumn::make('pack')
                    ->state(function (Order $record): string {
                        if ($record->orderItems->isNotEmpty()) {
                            return $record->orderItems->count().' item'.($record->orderItems->count() === 1 ? '' : 's');
                        }

                        return $record->diamondPack?->name ?? '—';
                    })
                    ->wrap(),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? ucwords(str_replace('_', ' ', $state))
                        : 'Not set')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('player')
                    ->state(fn (Order $record): string => $record->playerIdentifier())
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('progress')
                    ->state(fn (Order $record): string => $record->topupProgressLabel())
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_direct_topup')
                    ->label('Direct')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::STATUSES)
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->label('Payment method')
                    ->options([
                        'flexy' => 'Flexy',
                        'bmccp' => 'BaridiMob',
                        'baridimob' => 'BaridiMob / SofizPay',
                        'chargily' => 'Chargily',
                        'cryptocurrency' => 'Cryptocurrency',
                        'coupon_free' => 'Free coupon',
                    ])
                    ->multiple(),

                SelectFilter::make('seller_id')
                    ->label('Seller')
                    ->relationship('seller', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('coupon_id')
                    ->label('Has coupon')
                    ->nullable(),

                TernaryFilter::make('is_direct_topup')
                    ->label('Direct top-up'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
