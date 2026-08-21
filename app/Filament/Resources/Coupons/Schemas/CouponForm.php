<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\DiamondPack;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CouponForm
{
    /**
     * Game codes used by checkout coupon validation / Telegram create.
     *
     * @return array<string, string>
     */
    public static function gameOptions(): array
    {
        return [
            'mlbb' => 'Mobile Legends (mlbb)',
            'freefire' => 'Free Fire',
            'pubg' => 'PUBG Mobile (pubg)',
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon details')
                    ->description('Percentage (including 100% free), fixed DZD, site-wide or game/package scoped — same rules as checkout and Telegram.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Saved uppercase. Customers enter this at checkout.')
                            ->disabled(fn (?\App\Models\Coupon $record): bool => $record?->created_by === 'wheel_event')
                            ->dehydrated(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Select::make('discount_type')
                            ->label('Discount type')
                            ->options([
                                'percentage' => 'Percentage (%)',
                                'fixed' => 'Fixed amount (DZD)',
                            ])
                            ->required()
                            ->live()
                            ->selectablePlaceholder(false)
                            ->default('percentage'),

                        TextInput::make('discount_value')
                            ->label(fn (Get $get): string => $get('discount_type') === 'fixed' ? 'Discount (DZD)' : 'Discount (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn (Get $get): ?float => $get('discount_type') === 'percentage' ? 100 : null)
                            ->helperText('Use 100% for a free order coupon (coupon_free checkout path).')
                            ->prefix(fn (Get $get): string => $get('discount_type') === 'fixed' ? 'DZD' : '%'),

                        Select::make('applies_to')
                            ->label('Applies to')
                            ->options([
                                'all' => 'All games & packs',
                                'specific' => 'Specific games / packs',
                            ])
                            ->required()
                            ->live()
                            ->selectablePlaceholder(false)
                            ->default('all')
                            ->columnSpanFull(),

                        Select::make('allowed_games')
                            ->label('Allowed games')
                            ->options(static::gameOptions())
                            ->multiple()
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('applies_to') === 'specific')
                            ->helperText('Required when scoping to specific games (codes: mlbb, freefire, pubg).')
                            ->columnSpanFull(),

                        Select::make('allowed_packages')
                            ->label('Allowed packs (optional)')
                            ->options(fn (): array => static::packOptions())
                            ->multiple()
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('applies_to') === 'specific')
                            ->helperText('Leave empty to allow all packs for the selected game(s).')
                            ->columnSpanFull(),
                    ]),

                Section::make('Limits & schedule')
                    ->columns(2)
                    ->schema([
                        TextInput::make('max_uses')
                            ->label('Max total uses')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->helperText('Leave empty for unlimited.'),

                        TextInput::make('max_uses_per_user')
                            ->label('Max uses per user')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->default(1),

                        TextInput::make('min_order_amount')
                            ->label('Minimum order (DZD)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('DZD')
                            ->helperText('Leave empty for no minimum.'),

                        TextInput::make('used_count')
                            ->label('Times used')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false)
                            ->helperText('Optional. Blank = available immediately.'),

                        DateTimePicker::make('expires_at')
                            ->label('Expires at')
                            ->seconds(false)
                            ->helperText('Optional. Blank = never expires.')
                            ->after('starts_at'),

                        Textarea::make('description')
                            ->label('Internal notes')
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        TextInput::make('created_by')
                            ->label('Created by')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function packOptions(): array
    {
        return DiamondPack::query()
            ->where('is_active', true)
            ->orderBy('game_type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (DiamondPack $pack) => [
                $pack->id => trim(($pack->game_type ?? 'pack').' · '.$pack->name.' · '.number_format((float) ($pack->price_dzd ?? 0), 0).' DZD'),
            ])
            ->all();
    }
}
