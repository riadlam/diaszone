<?php

namespace App\Filament\Resources\FlashSales\Schemas;

use App\Models\DiamondPack;
use App\Support\GameProvider;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FlashSaleOfferForm
{
    public static function configure(Schema $schema): Schema
    {
        $gameOptions = collect(GameProvider::digiflazzGames())
            ->unique()
            ->mapWithKeys(fn (string $type) => [$type => GameProvider::label($type)])
            ->all();

        return $schema
            ->components([
                Section::make('Offer details')
                    ->description('Digiflazz games only. The sale price is what the customer pays; original price is for the strikethrough.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Pack name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('game_type')
                            ->label('Game')
                            ->options($gameOptions)
                            ->required()
                            ->live()
                            ->selectablePlaceholder(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        TextInput::make('original_price_dzd')
                            ->label('Original price (DZD)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('DZD'),

                        TextInput::make('sale_price_dzd')
                            ->label('Sale price (DZD)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('DZD')
                            ->lte('original_price_dzd'),

                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false)
                            ->required(),

                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->required()
                            ->after('starts_at'),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->default(0)
                            ->integer(),

                        FileUpload::make('image_path')
                            ->label('Pack image')
                            ->helperText('Shown on the home Flash Sale card. Square images work best.')
                            ->disk('public')
                            ->directory('flash-sale-images')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('160')
                            ->maxSize(4096)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Products inside this pack')
                    ->description('Each product is delivered via Digiflazz with its quantity (e.g. Weekly Pass × 3 = 3 top-ups).')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->addActionLabel('Add product')
                            ->orderColumn('sort_order')
                            ->reorderableWithButtons()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->columns(2)
                            ->schema([
                                Select::make('diamond_pack_id')
                                    ->label('Diamond pack')
                                    ->options(fn (Get $get): array => static::packOptions($get('../../game_type') ?: $get('game_type')))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(1)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function packOptions(?string $gameType): array
    {
        if (! $gameType || ! GameProvider::usesDigiflazz($gameType)) {
            return [];
        }

        return DiamondPack::query()
            ->where('game_type', $gameType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get()
            ->mapWithKeys(fn (DiamondPack $pack) => [
                $pack->id => trim($pack->name.' · '.number_format((float) ($pack->price_dzd ?? 0), 0).' DZD'),
            ])
            ->all();
    }
}
