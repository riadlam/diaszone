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
                    ->description('Pick a live VIP Reseller service. Code + H2H (IDR) fill automatically. Edit the display name for the website. Set DZD prices yourself.')
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

                                // Prefill display name from VIP; admin can edit afterward for the website.
                                $set('name', $service['name'] ?? $state);
                                $set('description', $service['description'] ?? null);
                                // Only H2H (special) — we do not store Member/Reseller tiers.
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
                            ->label('Display name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Shown on the website. Prefills from VIP — change it anytime.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->helperText('Optional website description. Prefills from VIP.')
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
                    ->description('Set your DiasZone sell price and cost in DZD.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_dzd')
                            ->label('Price (DZD)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('DZD')
                            ->helperText('Must be > 0 for the pack to appear on the storefront.'),

                        TextInput::make('base_price_dzd')
                            ->label('Base cost (DZD)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('DZD')
                            ->helperText('Your cost in DZD for margin (optional).'),

                        TextInput::make('price_usd')
                            ->label('Price (USD)')
                            ->numeric()
                            ->prefix('$'),

                        TextInput::make('discount_percentage')
                            ->label('Discount %')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->columnSpanFull(),
                    ]),

                Section::make('VIP cost (IDR)')
                    ->description('Only Special (H2H) — auto-filled from VIP when you pick a service.')
                    ->columns(1)
                    ->schema([
                        TextInput::make('price_special')
                            ->label('Special (H2H)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('This is the only VIP cost we store and use.'),
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
                ? 'Rp '.number_format((float) $special, 0, ',', '.').' H2H'
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

        return "Loaded {$count} available service(s) for filter_game \"{$filterGame}\" (prices show H2H).";
    }
}
