<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\DigiflazzStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DigiflazzStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'digiflazzStatuses';

    protected static ?string $title = 'Provider top-ups';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('ref_id')
                    ->label('Reference')
                    ->copyable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('trxid')
                    ->label('Transaction')
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (DigiflazzStatus $record): string => (
                        strtolower((string) $record->status) === 'sukses'
                        || (string) $record->rc === '00'
                    ) ? 'success' : (in_array(strtolower((string) $record->status), ['pending', 'processing'], true) ? 'warning' : 'danger')),

                TextColumn::make('rc')
                    ->label('Code')
                    ->badge(),

                TextColumn::make('customer_no')
                    ->label('Customer number')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('sn')
                    ->label('Serial / note')
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
