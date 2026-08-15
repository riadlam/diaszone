<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Models\DiamondPack;
use App\Support\GameProvider;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GamePacks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = GameResource::class;

    protected string $view = 'filament.resources.games.pages.game-packs';

    public string $gameType = '';

    public function mount(string $gameType): void
    {
        if (! GameProvider::usesDigiflazz($gameType)) {
            throw new NotFoundHttpException;
        }

        $this->gameType = $gameType;
    }

    public function getTitle(): string
    {
        return GameProvider::label($this->gameType);
    }

    public function getBreadcrumb(): string
    {
        return GameProvider::label($this->gameType);
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function stats(): array
    {
        $packs = $this->packsQuery()->get();
        $margins = $packs->map(fn (DiamondPack $pack): ?float => $pack->profitPercentage())->filter(fn (?float $value): bool => $value !== null);

        return [
            [
                'label' => 'Packs',
                'value' => (string) $packs->count(),
                'hint' => $packs->where('is_active', true)->count().' active',
            ],
            [
                'label' => 'Delivered top-ups',
                'value' => (string) $packs->sum('topups_count'),
                'hint' => $packs->sum('failed_topups_count').' failed attempts',
            ],
            [
                'label' => 'Average profit',
                'value' => $margins->isEmpty() ? '—' : number_format($margins->avg(), 1).' %',
                'hint' => $margins->isEmpty() ? null : 'Between '.number_format($margins->min(), 1).' % and '.number_format($margins->max(), 1).' %',
            ],
            [
                'label' => 'Best seller',
                'value' => (string) ($packs->sortByDesc('topups_count')->first()?->topups_count ?? 0),
                'hint' => $packs->sortByDesc('topups_count')->first()?->name,
            ],
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->packsQuery())
            ->defaultSort('topups_count', 'desc')
            ->emptyStateHeading('This game has no packs yet')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->weight('bold')
                    ->copyable()
                    ->placeholder('No SKU')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Pack')
                    ->description(fn (DiamondPack $record): ?string => $record->membership_name)
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('diamonds')
                    ->label('Diamonds')
                    ->state(fn (DiamondPack $record): string => $record->bonus_diamonds > 0
                        ? number_format($record->diamonds).' + '.number_format($record->bonus_diamonds).' bonus'
                        : number_format($record->diamonds))
                    ->description(fn (DiamondPack $record): ?string => $record->bonus_diamonds > 0
                        ? number_format($record->total_diamonds).' total'
                        : null)
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Digiflazz price')
                    ->state(fn (DiamondPack $record): string => number_format((float) $record->price, 0, '.', ' ').' IDR')
                    ->sortable(),

                TextColumn::make('base_price_dzd')
                    ->label('Cost')
                    ->state(fn (DiamondPack $record): string => $record->base_price_dzd === null
                        ? '—'
                        : number_format((float) $record->base_price_dzd, 0, '.', ' ').' DZD')
                    ->color(fn (DiamondPack $record): string => $record->hasUnconvertedCost() ? 'danger' : 'gray')
                    ->tooltip(fn (DiamondPack $record): ?string => $record->hasUnconvertedCost()
                        ? 'This cost still looks like the Digiflazz IDR price, so it was never converted to DZD'
                        : null)
                    ->sortable(),

                TextColumn::make('price_dzd')
                    ->label('Selling price')
                    ->state(fn (DiamondPack $record): string => $record->price_dzd === null
                        ? '—'
                        : number_format((float) $record->price_dzd, 0, '.', ' ').' DZD')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('profit_percentage')
                    ->label('Profit')
                    ->state(fn (DiamondPack $record): string => $record->profitPercentage() === null
                        ? '—'
                        : number_format($record->profitPercentage(), 1).' %')
                    ->description(fn (DiamondPack $record): ?string => $record->profitDzd() === null
                        ? null
                        : '+'.number_format($record->profitDzd(), 0, '.', ' ').' DZD')
                    ->badge()
                    ->color(fn (DiamondPack $record): string => match (true) {
                        $record->profitPercentage() === null, $record->hasUnconvertedCost() => 'gray',
                        $record->profitPercentage() <= 0 => 'danger',
                        $record->profitPercentage() < 15 => 'warning',
                        default => 'success',
                    })
                    ->tooltip(fn (DiamondPack $record): ?string => $record->hasUnconvertedCost()
                        ? 'Unreliable: the cost for this pack is still stored in IDR'
                        : null)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw(
                        '((diamond_packs.price_dzd - diamond_packs.base_price_dzd) / NULLIF(diamond_packs.base_price_dzd, 0)) '.$direction
                    )),

                TextColumn::make('topups_count')
                    ->label('Top-ups')
                    ->tooltip('Top-ups Digiflazz confirmed as delivered for this pack')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('failed_topups_count')
                    ->label('Failed')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('last_topup_at')
                    ->label('Last top-up')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->placeholder('Never')
                    ->toggleable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active packs'),

                Filter::make('sold')
                    ->label('Sold at least once')
                    ->query(fn (Builder $query): Builder => $query->having('topups_count', '>', 0)),

                Filter::make('losing_money')
                    ->label('Selling at or below cost')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('base_price_dzd')
                        ->where('base_price_dzd', '>', 0)
                        ->whereColumn('price_dzd', '<=', 'base_price_dzd')),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('allGames')
                ->label('All games')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(fn (): string => GameResource::getUrl('index')),
        ];
    }

    protected function packsQuery(): Builder
    {
        return DiamondPack::query()
            ->where('diamond_packs.game_type', $this->gameType)
            ->withCount([
                'digiflazzStatuses as topups_count' => fn (Builder $query) => $query->successful(),
                'digiflazzStatuses as failed_topups_count' => fn (Builder $query) => $query->whereNot(
                    fn (Builder $inner) => $inner
                        ->whereRaw("LOWER(digiflazz_statuses.status) = 'sukses'")
                        ->orWhere('digiflazz_statuses.rc', '00')
                ),
            ])
            ->withMax([
                'digiflazzStatuses as last_topup_at' => fn (Builder $query) => $query->successful(),
            ], 'created_at')
            ->orderBy('diamond_packs.sort_order');
    }
}
