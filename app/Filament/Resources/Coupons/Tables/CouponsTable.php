<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('discount')
                    ->label('Discount')
                    ->state(fn (Coupon $record): string => $record->discount_type === 'percentage'
                        ? rtrim(rtrim(number_format((float) $record->discount_value, 2), '0'), '.').'%'
                        : number_format((float) $record->discount_value, 0).' DZD')
                    ->badge()
                    ->color(fn (Coupon $record): string => $record->isFullDiscount() ? 'success' : 'info'),

                TextColumn::make('applies_to')
                    ->label('Scope')
                    ->formatStateUsing(fn (?string $state): string => $state === 'specific' ? 'Specific' : 'All')
                    ->description(fn (Coupon $record): ?string => $record->applies_to === 'specific'
                        ? collect([
                            $record->allowed_games ? 'Games: '.implode(', ', $record->allowed_games) : null,
                            $record->allowed_packages ? 'Packs: '.implode(', ', $record->allowed_packages) : null,
                        ])->filter()->implode(' · ') ?: null
                        : null),

                TextColumn::make('usage')
                    ->label('Usage')
                    ->state(fn (Coupon $record): string => $record->max_uses === null
                        ? $record->used_count.' / ∞'
                        : $record->used_count.' / '.$record->max_uses),

                TextColumn::make('max_uses_per_user')
                    ->label('Per user')
                    ->alignCenter(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Never')
                    ->sortable(),

                TextColumn::make('created_by')
                    ->label('Source')
                    ->badge()
                    ->placeholder('—')
                    ->color(fn (?string $state): string => match ($state) {
                        'wheel_event' => 'warning',
                        'Telegram' => 'info',
                        'admin' => 'gray',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('On')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                SelectFilter::make('discount_type')
                    ->label('Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed DZD',
                    ]),
                SelectFilter::make('applies_to')
                    ->label('Scope')
                    ->options([
                        'all' => 'All',
                        'specific' => 'Specific',
                    ]),
                SelectFilter::make('created_by')
                    ->label('Source')
                    ->options([
                        'admin' => 'Admin',
                        'Telegram' => 'Telegram',
                        'wheel_event' => 'Lucky Wheel',
                    ]),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (Coupon $record): string => $record->is_active ? 'Disable' : 'Enable')
                    ->icon(fn (Coupon $record): string => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (Coupon $record): string => $record->is_active ? 'gray' : 'success')
                    ->action(function (Coupon $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Coupon enabled.' : 'Coupon disabled.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Coupon $record): bool => $record->created_by !== 'wheel_event'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $records
                                ->reject(fn (Coupon $coupon): bool => $coupon->created_by === 'wheel_event')
                                ->each->delete();
                        }),
                ]),
            ]);
    }
}
