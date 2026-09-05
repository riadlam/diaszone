<?php

namespace App\Filament\Resources\VipResellerPacks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VipResellerPackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pack')
                    ->description('VIP service code is sent as `service` on order. Provider price tiers are filled by cron later.')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        TextInput::make('code')
                            ->label('VIP code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Exact service code from VIP (e.g. NTFLXSHARE30DGAR14D-S1).'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('product_url')
                            ->label('Product URL')
                            ->maxLength(500)
                            ->placeholder('/digital/netflix')
                            ->helperText('Optional; falls back to category product URL.'),

                        FileUpload::make('image_path')
                            ->label('Image')
                            ->disk('public')
                            ->directory('vipreseller-packs')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('140')
                            ->maxSize(4096),

                        Select::make('provider_status')
                            ->label('Provider status')
                            ->options([
                                'available' => 'Available',
                                'empty' => 'Empty',
                            ])
                            ->default('available')
                            ->required()
                            ->native(false),

                        TextInput::make('server')
                            ->label('Server')
                            ->maxLength(50),

                        TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->integer()
                            ->minValue(0),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Sell prices')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_dzd')
                            ->label('Price (DZD)')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->prefix('DZD'),

                        TextInput::make('base_price_dzd')
                            ->label('Base cost (DZD)')
                            ->numeric()
                            ->prefix('DZD')
                            ->helperText('Our cost in DZD for margin calc.'),

                        TextInput::make('price_usd')
                            ->label('Price (USD)')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('discount_percentage')
                            ->label('Discount %')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->columnSpanFull(),
                    ]),

                Section::make('VIP provider prices (IDR)')
                    ->description('From VIP Member / Reseller / H2H tiers. Updated by cron later.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_basic')
                            ->label('Basic (Member)')
                            ->numeric()
                            ->prefix('Rp'),

                        TextInput::make('price_premium')
                            ->label('Premium (Reseller)')
                            ->numeric()
                            ->prefix('Rp'),

                        TextInput::make('price_special')
                            ->label('Special (H2H)')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),
            ]);
    }
}
