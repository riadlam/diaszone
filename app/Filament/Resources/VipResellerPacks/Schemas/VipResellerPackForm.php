<?php

namespace App\Filament\Resources\VipResellerPacks\Schemas;

use App\Models\VipResellerCategory;
use App\Services\VipResellerService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VipResellerPackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('VIP service')
                    ->description('Pick a live VIP Reseller service. Code and IDR prices fill automatically. You set DZD sell/cost prices yourself.')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(function (Set $set): void {
                                $set('code', null);
                                $set('name', null);
                                $set('description', null);
                                $set('price_basic', null);
                                $set('price_premium', null);
                                $set('price_special', null);
                                $set('server', null);
                                $set('provider_status', 'available');
                            })
                            ->helperText('Category must have filter_game set (e.g. Netflix) for the picker to load.'),

                        Toggle::make('show_empty_services')
                            ->label('Include empty (out of stock) services')
                            ->default(false)
                            ->live()
                            ->dehydrated(false)
                            ->inline(false)
                            ->columnSpanFull(),

                        Select::make('code')
                            ->label('VIP service')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (Get $get): bool => blank($get('category_id')))
                            ->options(function (Get $get): array {
                                $options = static::serviceOptions(
                                    categoryId: $get('category_id'),
                                    includeEmpty: (bool) $get('show_empty_services'),
                                );

                                $current = $get('code');
                                if (filled($current) && ! isset($options[$current])) {
                                    $options[$current] = (string) $current.' — (current)';
                                }

                                return $options;
                            })
                            ->getOptionLabelUsing(function (?string $value, Get $get): ?string {
                                if (blank($value)) {
                                    return null;
                                }

                                $options = static::serviceOptions(
                                    categoryId: $get('category_id'),
                                    includeEmpty: true,
                                );

                                return $options[$value] ?? $value;
                            })
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                if (blank($state)) {
                                    return;
                                }

                                $category = VipResellerCategory::query()->find($get('category_id'));
                                $service = app(VipResellerService::class)->findServiceByCode(
                                    $state,
                                    $category?->filter_game
                                );

                                if (! $service) {
                                    return;
                                }

                                $set('name', $service['name'] ?? $state);
                                $set('description', $service['description'] ?? null);
                                $set('price_basic', $service['price_basic'] ?? null);
                                $set('price_premium', $service['price_premium'] ?? null);
                                $set('price_special', $service['price_special'] ?? null);
                                $set('server', $service['server'] ?? null);
                                $set('provider_status', $service['status'] ?? 'available');

                                if ($category && blank($get('product_url')) && filled($category->product_url)) {
                                    $set('product_url', $category->product_url);
                                }
                            })
                            ->helperText(fn (Get $get): string => static::pickerHelperText($get('category_id')))
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        TextInput::make('product_url')
                            ->label('Product URL')
                            ->maxLength(500)
                            ->placeholder('/digital/netflix')
                            ->helperText('Optional; defaults from category when you pick a service.'),

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
                            ->disabled()
                            ->dehydrated()
                            ->native(false),

                        TextInput::make('server')
                            ->label('Server')
                            ->maxLength(50)
                            ->disabled()
                            ->dehydrated(),

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

                Section::make('Sell prices (manual)')
                    ->description('Set your DiasZone sell price and cost in DZD. IDR tiers above come from VIP automatically.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_dzd')
                            ->label('Price (DZD)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('DZD'),

                        TextInput::make('base_price_dzd')
                            ->label('Base cost (DZD)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('DZD')
                            ->helperText('Your cost in DZD for margin.'),

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
                    ->description('Auto-filled from VIP Member / Reseller / H2H when you pick a service. Not editable.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_basic')
                            ->label('Basic (Member)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('price_premium')
                            ->label('Premium (Reseller)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('price_special')
                            ->label('Special (H2H)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function serviceOptions(mixed $categoryId, bool $includeEmpty = false): array
    {
        if (blank($categoryId)) {
            return [];
        }

        $category = VipResellerCategory::query()->find($categoryId);
        $filterGame = trim((string) ($category?->filter_game ?? ''));
        if ($filterGame === '') {
            return [];
        }

        $status = $includeEmpty ? null : 'available';
        $response = app(VipResellerService::class)->getServices($filterGame, $status);

        if (! ($response['result'] ?? false)) {
            return [];
        }

        $options = [];
        foreach ($response['data'] ?? [] as $service) {
            $code = (string) ($service['code'] ?? '');
            if ($code === '') {
                continue;
            }

            if (! $includeEmpty && ($service['status'] ?? '') === 'empty') {
                continue;
            }

            $special = $service['price_special'] ?? null;
            $priceLabel = $special !== null
                ? 'Rp '.number_format((float) $special, 0, ',', '.')
                : '—';

            $statusBadge = ($service['status'] ?? '') === 'empty' ? ' [empty]' : '';
            $options[$code] = $code.' — '.($service['name'] ?? $code).' ('.$priceLabel.')'.$statusBadge;
        }

        return $options;
    }

    public static function pickerHelperText(mixed $categoryId): string
    {
        if (blank($categoryId)) {
            return 'Select a category first.';
        }

        $category = VipResellerCategory::query()->find($categoryId);
        $filterGame = trim((string) ($category?->filter_game ?? ''));
        if ($filterGame === '') {
            return 'This category has no filter_game. Edit the category and set it (e.g. Netflix), then reopen this form.';
        }

        $response = app(VipResellerService::class)->getServices($filterGame, 'available');
        if (! ($response['result'] ?? false)) {
            return 'Could not load VIP services: '.($response['message'] ?? 'unknown error');
        }

        $count = count($response['data'] ?? []);

        return "Loaded {$count} available service(s) for filter_game \"{$filterGame}\".";
    }
}
