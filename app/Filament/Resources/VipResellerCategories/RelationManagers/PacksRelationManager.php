<?php

namespace App\Filament\Resources\VipResellerCategories\RelationManagers;

use App\Filament\Resources\VipResellerPacks\Schemas\VipResellerPackForm;
use App\Filament\Resources\VipResellerPacks\VipResellerPackResource;
use App\Models\VipResellerPack;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PacksRelationManager extends RelationManager
{
    protected static string $relationship = 'packs';

    protected static ?string $title = 'Services / packs';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return VipResellerPackForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordUrl(
                fn (VipResellerPack $record): string => VipResellerPackResource::getUrl('edit', ['record' => $record]),
                shouldOpenInNewTab: true,
            )
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
                    ->limit(45)
                    ->wrap(),

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
                    ->label('VIP')
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
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add pack')
                    ->url(fn (): string => VipResellerPackResource::getUrl('create', [
                        'category_id' => $this->getOwnerRecord()->getKey(),
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (VipResellerPack $record): string => VipResellerPackResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
