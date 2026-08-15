<?php

namespace App\Filament\Resources\WheelEvents\RelationManagers;

use App\Models\WheelClaim;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClaimsRelationManager extends RelationManager
{
    protected static string $relationship = 'claims';

    protected static ?string $title = 'Claims';

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'unlocked' => 'Unlocked',
        'contacted' => 'Contacted',
        'fulfilled' => 'Fulfilled',
        'used' => 'Used',
        'failed' => 'Failed',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(self::STATUSES)
                    ->selectablePlaceholder(false)
                    ->required(),

                Textarea::make('admin_notes')
                    ->label('Internal notes')
                    ->rows(3)
                    ->maxLength(2000),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('claim_code')
            ->defaultSort('unlocked_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Player')
                    ->description(fn (WheelClaim $record): ?string => $record->user?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reward.label')
                    ->label('Reward')
                    ->description(fn (WheelClaim $record): string => 'Occurrence #'.$record->occurrence),

                TextColumn::make('reward_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'discount' ? 'Discount' : 'Diamond pack')
                    ->color(fn (string $state): string => $state === 'discount' ? 'info' : 'warning'),

                TextColumn::make('claim_code')
                    ->label('Claim code')
                    ->copyable()
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('coupon.code')
                    ->label('Coupon')
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'unlocked' => 'warning',
                        'contacted' => 'info',
                        'fulfilled' => 'success',
                        'used' => 'gray',
                        default => 'danger',
                    }),

                TextColumn::make('unlocked_at')
                    ->label('Unlocked')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('fulfilled_at')
                    ->label('Fulfilled')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('used_at')
                    ->label('Used')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::STATUSES),

                SelectFilter::make('reward_type')
                    ->label('Type')
                    ->options([
                        'diamond_pack' => 'Diamond pack',
                        'discount' => 'Discount',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Update status')
                    ->modalHeading('Update claim')
                    ->mutateDataUsing(fn (WheelClaim $record, array $data): array => static::withStatusTimestamps($record, $data)),

                Action::make('markFulfilled')
                    ->label('Mark fulfilled')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (WheelClaim $record): bool => in_array($record->status, ['unlocked', 'contacted'], true))
                    ->action(fn (WheelClaim $record) => $record->forceFill([
                        'status' => 'fulfilled',
                        'fulfilled_at' => $record->fulfilled_at ?? now(),
                    ])->save()),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function withStatusTimestamps(WheelClaim $record, array $data): array
    {
        if (($data['status'] ?? null) === 'fulfilled' && ! $record->fulfilled_at) {
            $data['fulfilled_at'] = now();
        }

        if (($data['status'] ?? null) === 'used' && ! $record->used_at) {
            $data['used_at'] = now();
        }

        return $data;
    }
}
