<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        ImageEntry::make('google_avatar')
                            ->label('Avatar')
                            ->circular()
                            ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=92400e&background=fef3c7'),

                        TextEntry::make('name')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->copyable(),

                        TextEntry::make('id')
                            ->label('User ID')
                            ->badge(),

                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('email_verified_at')
                            ->label('Email verified')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not verified'),
                    ]),

                Section::make('Access')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),

                        IconEntry::make('is_admin')
                            ->label('Administrator')
                            ->boolean(),

                        IconEntry::make('google_id')
                            ->label('Google connected')
                            ->state(fn (User $record): bool => filled($record->google_id))
                            ->boolean(),
                    ]),

                Section::make('Activity')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('orders_count')
                            ->label('All orders')
                            ->state(fn (User $record): int => $record->orders()->count())
                            ->badge()
                            ->color('info'),

                        TextEntry::make('completed_orders_count')
                            ->label('Completed orders')
                            ->state(fn (User $record): int => $record->orders()->where('status', 'completed')->count())
                            ->badge()
                            ->color('success'),

                        TextEntry::make('delivered_topups')
                            ->label('Delivered top-ups')
                            ->helperText('Counted from provider deliveries, including multi-quantity orders')
                            ->state(fn (User $record): int => $record->deliveredTopupsCount())
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('ordered_items_quantity')
                            ->label('Items ordered')
                            ->state(fn (User $record): int => $record->orderedItemsQuantity())
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('lifetime_spend')
                            ->label('Completed spend')
                            ->state(fn (User $record): string => number_format($record->lifetimeSpendDzd(), 0, '.', ' ').' DZD')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('wheel_claims_count')
                            ->label('Wheel rewards')
                            ->state(fn (User $record): int => $record->wheelClaims()->count())
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('last_order_at')
                            ->label('Last order')
                            ->state(fn (User $record): ?string => $record->orders()->max('created_at'))
                            ->dateTime('d M Y, H:i')
                            ->placeholder('No orders yet'),
                    ]),
            ]);
    }
}
