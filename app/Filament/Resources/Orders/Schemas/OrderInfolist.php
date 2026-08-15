<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Filament\Resources\Users\UserResource;
use App\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order overview')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order number')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => OrdersTable::STATUSES[$state] ?? ucwords(str_replace('_', ' ', $state)))
                            ->color(fn (Order $record): string => $record->statusColor()),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i:s'),

                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime('d M Y, H:i:s'),

                        TextEntry::make('notes')
                            ->placeholder('No internal notes')
                            ->columnSpanFull(),
                    ]),

                Section::make('Customer and game')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Customer')
                            ->placeholder('Guest checkout')
                            ->url(fn (Order $record): ?string => $record->user
                                ? UserResource::getUrl('view', ['record' => $record->user])
                                : null),

                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('previous_topups')
                            ->label('Previous successful top-ups')
                            ->state(fn (Order $record): int => $record->priorSuccessfulTopupsCount())
                            ->badge()
                            ->color('success'),

                        TextEntry::make('game')
                            ->state(fn (Order $record): string => $record->gameLabel()),

                        TextEntry::make('diamondPack.name')
                            ->label('Primary pack')
                            ->placeholder('See order items below'),

                        TextEntry::make('player')
                            ->label('Player / account')
                            ->state(fn (Order $record): string => $record->playerIdentifier())
                            ->copyable(),

                        TextEntry::make('quantity')
                            ->placeholder('1'),

                        TextEntry::make('topup_progress')
                            ->label('Top-up progress')
                            ->state(fn (Order $record): string => $record->topupProgressLabel())
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Pricing')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('original_price')
                            ->label('Original')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD')
                            ->placeholder('—'),

                        TextEntry::make('discount_amount')
                            ->label('Discount')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD')
                            ->placeholder('0 DZD'),

                        TextEntry::make('total')
                            ->label('Final amount')
                            ->state(fn (Order $record): string => number_format($record->displayAmount(), 0).' DZD')
                            ->weight('bold'),

                        TextEntry::make('coupon.code')
                            ->label('Coupon')
                            ->copyable()
                            ->placeholder('None'),
                    ]),

                Section::make('Payment')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('payment_method')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => $state
                                ? ucwords(str_replace('_', ' ', $state))
                                : 'Not set'),

                        TextEntry::make('sofizpayCibTransaction.transaction_id')
                            ->label('SofizPay transaction')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('nowpayments_payment_id')
                            ->label('NOWPayments ID')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('flexy_description')
                            ->label('Payment description')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Seller')
                    ->visible(fn (Order $record): bool => filled($record->seller_id))
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextEntry::make('seller.name')
                            ->label('Seller'),

                        TextEntry::make('seller_cost')
                            ->label('Cost')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD'),

                        TextEntry::make('seller_profit')
                            ->label('Profit')
                            ->formatStateUsing(fn ($state): string => number_format((float) $state, 0).' DZD'),

                        IconEntry::make('seller_profit_paid')
                            ->label('Profit paid')
                            ->boolean(),

                        IconEntry::make('wallet_deducted')
                            ->label('Wallet deducted')
                            ->boolean(),

                        IconEntry::make('is_direct_topup')
                            ->label('Direct top-up')
                            ->boolean(),
                    ]),

                Section::make('Internal references')
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('id')
                            ->label('Database ID'),

                        TextEntry::make('tlg_message_id')
                            ->label('Telegram message ID')
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('chargily_status_id')
                            ->label('Chargily status ID')
                            ->placeholder('—'),

                        TextEntry::make('cryptopay_id')
                            ->label('CryptoPay ID')
                            ->placeholder('—'),

                        TextEntry::make('bmccp_id')
                            ->label('BMCCP ID')
                            ->placeholder('—'),

                        TextEntry::make('flexy_id')
                            ->label('Flexy ID')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
