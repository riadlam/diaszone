<?php

namespace App\Filament\Resources\WheelEvents\Pages;

use App\Filament\Resources\WheelEvents\WheelEventResource;
use App\Models\WheelClaim;
use App\Models\WheelSpinLedger;
use App\Models\WheelUserProgress;
use App\Services\WheelQualificationService;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventParticipants extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = WheelEventResource::class;

    protected string $view = 'filament.resources.wheel-events.pages.event-participants';

    protected static ?string $title = 'Player progress';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Player progress — '.$this->getRecord()->name;
    }

    public function getBreadcrumb(): string
    {
        return 'Player progress';
    }

    public function table(Table $table): Table
    {
        $eventId = $this->getRecord()->getKey();

        return $table
            ->query(fn (): Builder => $this->participantsQuery($eventId))
            ->defaultSort('event_spins', 'desc')
            ->emptyStateHeading('No players have earned spins in this event yet')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Player')
                    ->description(fn (WheelUserProgress $record): ?string => $record->user?->email)
                    ->searchable(),

                TextColumn::make('event_spins')
                    ->label('Spins this event')
                    ->badge()
                    ->sortable(),

                TextColumn::make('available_spins')
                    ->label('Unused spins')
                    ->state(fn (WheelUserProgress $record): int => $record->availableSpins())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),

                TextColumn::make('currentReward.label')
                    ->label('Next reward')
                    ->placeholder('All rewards unlocked'),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (WheelUserProgress $record): string {
                        $required = $record->currentReward?->draws_required;

                        return $required
                            ? $record->draws_toward_current.' / '.$required
                            : '—';
                    })
                    ->badge()
                    ->color(fn (WheelUserProgress $record): string => static::isCloseToReward($record) ? 'success' : 'gray'),

                TextColumn::make('total_spins_earned')
                    ->label('Lifetime spins')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_rewards_unlocked')
                    ->label('Rewards unlocked')
                    ->sortable(),

                TextColumn::make('event_claims')
                    ->label('Claims here')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('close_to_reward')
                    ->label('Two draws or less from a reward')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('current_reward_id')
                        ->whereRaw('draws_toward_current >= GREATEST(1, (SELECT draws_required FROM wheel_rewards WHERE wheel_rewards.id = wheel_user_progress.current_reward_id) - 2)')),

                Filter::make('has_unused_spins')
                    ->label('Has unused spins')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('total_spins_earned', '>', 'total_spins_used')),

                Filter::make('awaiting_fulfilment')
                    ->label('Awaiting fulfilment')
                    ->query(fn (Builder $query) => $query->whereExists(
                        fn ($sub) => $sub->selectRaw('1')
                            ->from('wheel_claims')
                            ->whereColumn('wheel_claims.user_id', 'wheel_user_progress.user_id')
                            ->where('wheel_claims.wheel_event_id', $this->getRecord()->getKey())
                            ->whereIn('wheel_claims.status', ['unlocked', 'contacted'])
                    )),
            ])
            ->recordActions([
                Action::make('history')
                    ->label('History')
                    ->icon('heroicon-o-clock')
                    ->modalHeading(fn (WheelUserProgress $record): string => 'Wheel history — '.($record->user?->name ?? 'Player #'.$record->user_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (WheelUserProgress $record) => view(
                        'filament.resources.wheel-events.partials.player-history',
                        [
                            'progress' => $record,
                            'claims' => WheelClaim::with(['reward.diamondPack', 'coupon'])
                                ->where('user_id', $record->user_id)
                                ->where('wheel_event_id', $this->getRecord()->getKey())
                                ->orderByDesc('unlocked_at')
                                ->get(),
                            'ledgers' => WheelSpinLedger::with('order')
                                ->where('user_id', $record->user_id)
                                ->where('wheel_event_id', $this->getRecord()->getKey())
                                ->orderByDesc('id')
                                ->limit(50)
                                ->get(),
                        ]
                    )),
            ]);
    }

    protected function participantsQuery(int | string $eventId): Builder
    {
        $spins = WheelSpinLedger::query()
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('wheel_spin_ledger.user_id', 'wheel_user_progress.user_id')
            ->where('wheel_spin_ledger.wheel_event_id', $eventId)
            ->where('wheel_spin_ledger.entry_type', 'credit');

        $claims = WheelClaim::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('wheel_claims.user_id', 'wheel_user_progress.user_id')
            ->where('wheel_claims.wheel_event_id', $eventId);

        return WheelUserProgress::query()
            ->with(['user', 'currentReward'])
            ->select('wheel_user_progress.*')
            ->selectSub($spins, 'event_spins')
            ->selectSub($claims, 'event_claims')
            ->where('game_type', WheelQualificationService::GAME_TYPE)
            ->where(function (Builder $query) use ($eventId): void {
                $query->whereExists(
                    fn ($sub) => $sub->selectRaw('1')
                        ->from('wheel_spin_ledger')
                        ->whereColumn('wheel_spin_ledger.user_id', 'wheel_user_progress.user_id')
                        ->where('wheel_spin_ledger.wheel_event_id', $eventId)
                )->orWhereExists(
                    fn ($sub) => $sub->selectRaw('1')
                        ->from('wheel_claims')
                        ->whereColumn('wheel_claims.user_id', 'wheel_user_progress.user_id')
                        ->where('wheel_claims.wheel_event_id', $eventId)
                );
            });
    }

    protected static function isCloseToReward(WheelUserProgress $record): bool
    {
        $required = $record->currentReward?->draws_required;

        return $required !== null && $record->draws_toward_current >= max(1, $required - 2);
    }
}
