<?php

namespace App\Filament\Resources\Giveaways\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GiveawaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('entries_count')
                    ->counts('entries')
                    ->label('Entries')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
