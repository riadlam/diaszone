<?php

namespace App\Filament\Resources\VipResellerCategories\Tables;

use App\Models\VipResellerCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VipResellerCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (VipResellerCategory $record): ?string => $record->imageUrl())
                    ->height(40),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('filter_game')
                    ->label('VIP filter')
                    ->placeholder('—'),

                TextColumn::make('product_url')
                    ->label('URL')
                    ->limit(30)
                    ->placeholder('—'),

                TextColumn::make('packs_count')
                    ->counts('packs')
                    ->label('Packs')
                    ->alignCenter(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('On')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit VIP category')
                    ->modalWidth('3xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
