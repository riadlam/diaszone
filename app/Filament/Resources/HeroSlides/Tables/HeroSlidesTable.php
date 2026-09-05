<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use App\Models\HeroSlide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->getStateUsing(fn (HeroSlide $record): ?string => $record->imageUrl())
                    ->height(48),

                TextColumn::make('page')
                    ->label('Page')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'digital' => 'Digital',
                        default => 'Home',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'digital' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Label')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('link_url')
                    ->label('Link')
                    ->limit(40)
                    ->placeholder('No link'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('On')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('page')
                    ->label('Page')
                    ->options([
                        'home' => 'Home',
                        'digital' => 'Digital',
                    ]),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
