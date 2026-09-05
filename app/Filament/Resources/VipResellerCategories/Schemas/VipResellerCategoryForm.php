<?php

namespace App\Filament\Resources\VipResellerCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VipResellerCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->description('VIP Reseller product category (e.g. Netflix). filter_game must match the VIP brand so the pack picker can load services.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL key, e.g. netflix. Auto-filled from name on create.'),

                        TextInput::make('filter_game')
                            ->label('VIP filter_game')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Required. Exact VIP brand/category string for type=services (e.g. Netflix, CapCut Pro). Used by the pack code picker.')
                            ->placeholder('Netflix'),

                        TextInput::make('product_url')
                            ->label('Product URL')
                            ->maxLength(500)
                            ->placeholder('/digital/netflix')
                            ->helperText('Storefront deep link on DiasZone.'),

                        FileUpload::make('image_path')
                            ->label('Image')
                            ->disk('public')
                            ->directory('vipreseller-categories')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('140')
                            ->maxSize(4096)
                            ->columnSpanFull(),

                        Select::make('required_fields')
                            ->label('Required fields')
                            ->multiple()
                            ->options([
                                'email' => 'Email (data_no)',
                                'password' => 'Password (data_zone)',
                                'user_id' => 'User ID',
                                'zone_id' => 'Zone ID',
                            ])
                            ->helperText('Checkout fields mapped to VIP order data_no / data_zone.')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('is_topseller')
                            ->label('Top seller')
                            ->default(false)
                            ->inline(false),

                        Toggle::make('is_newproduct')
                            ->label('New product')
                            ->default(false)
                            ->inline(false),
                    ]),
            ]);
    }
}
