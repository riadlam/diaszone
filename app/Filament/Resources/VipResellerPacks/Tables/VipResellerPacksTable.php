<?php

namespace App\Filament\Resources\VipResellerPacks\Tables;

use App\Filament\Resources\VipResellerCategories\VipResellerCategoryResource;
use App\Models\VipResellerPack;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VipResellerPacksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Icon')
                    ->getStateUsing(fn (VipResellerPack $record): ?string => $record->imageUrl())
                    ->height(40)
                    ->square(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('price_dzd')
                    ->label('DZD')
                    ->money('DZD')
                    ->sortable(),

                TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('price_special')
                    ->label('VIP H2H')
                    ->formatStateUsing(fn ($state): string => $state !== null
                        ? 'Rp '.number_format((float) $state, 0, ',', '.')
                        : '—')
                    ->toggleable(),

                TextColumn::make('provider_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'available' ? 'success' : 'danger')
                    ->formatStateUsing(fn (?string $state): string => $state === 'available' ? 'Available' : 'Empty'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('provider_status')
                    ->label('Provider status')
                    ->options([
                        'available' => 'Available',
                        'empty' => 'Empty',
                    ]),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit VIP pack')
                    ->modalWidth('4xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['code'] = trim((string) ($data['code'] ?? ''));

                        return $data;
                    }),
                Action::make('category')
                    ->label('Category')
                    ->icon('heroicon-o-tag')
                    ->color('gray')
                    ->url(fn (VipResellerPack $record): ?string => $record->category_id
                        ? VipResellerCategoryResource::getUrl('edit', ['record' => $record->category_id])
                        : null)
                    ->visible(fn (VipResellerPack $record): bool => filled($record->category_id)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
