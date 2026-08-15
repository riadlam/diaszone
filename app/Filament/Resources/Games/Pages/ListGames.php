<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Models\DiamondPack;
use App\Support\GameProvider;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListGames extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = GameResource::class;

    protected string $view = 'filament.resources.games.pages.list-games';

    protected static ?string $title = 'Games (Digiflazz)';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->gamesQuery())
            ->defaultSort('topups_count', 'desc')
            ->emptyStateHeading('No Digiflazz games found')
            ->recordUrl(fn (DiamondPack $record): string => GameResource::getUrl('packs', [
                'gameType' => $record->game_type,
            ]))
            ->columns([
                TextColumn::make('game_type')
                    ->label('Game')
                    ->weight('bold')
                    ->formatStateUsing(fn (string $state): string => GameProvider::label($state))
                    ->description(fn (DiamondPack $record): string => $record->game_type)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $needle = str_replace([' ', '-'], '_', strtolower($search));

                        return $query->where(function (Builder $inner) use ($search, $needle): void {
                            $inner->where('diamond_packs.game_type', 'like', '%'.$search.'%')
                                ->orWhere('diamond_packs.game_type', 'like', '%'.$needle.'%')
                                ->orWhere('diamond_packs.game_type', 'like', '%'.str_replace('_', '', $needle).'%');
                        });
                    })
                    ->sortable(),

                TextColumn::make('packs_count')
                    ->label('Packs')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('active_packs_count')
                    ->label('Active')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('avg_profit_percentage')
                    ->label('Avg. profit')
                    ->state(fn (DiamondPack $record): ?string => $record->avg_profit_percentage === null
                        ? null
                        : number_format((float) $record->avg_profit_percentage, 1).' %')
                    ->placeholder('—')
                    ->description(fn (DiamondPack $record): ?string => $record->unconverted_cost_count > 0
                        ? $record->unconverted_cost_count.' pack(s) still priced in IDR'
                        : null)
                    ->badge()
                    ->color(fn (DiamondPack $record): string => match (true) {
                        $record->avg_profit_percentage === null, $record->unconverted_cost_count > 0 => 'gray',
                        (float) $record->avg_profit_percentage <= 0 => 'danger',
                        (float) $record->avg_profit_percentage < 15 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                TextColumn::make('topups_count')
                    ->label('Top-ups')
                    ->tooltip('Top-ups Digiflazz confirmed as delivered for this game')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('last_topup_at')
                    ->label('Last top-up')
                    ->dateTime('d M Y, H:i')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('has_topups')
                    ->label('Sold at least once')
                    ->query(fn (Builder $query): Builder => $query->havingRaw('topups_count > 0')),

                Filter::make('has_active_packs')
                    ->label('Has active packs')
                    ->query(fn (Builder $query): Builder => $query->havingRaw('active_packs_count > 0')),
            ])
            ->recordActions([
                Action::make('packs')
                    ->label('View packs')
                    ->icon('heroicon-o-square-3-stack-3d')
                    ->url(fn (DiamondPack $record): string => GameResource::getUrl('packs', [
                        'gameType' => $record->game_type,
                    ])),
            ]);
    }

    protected function gamesQuery(): Builder
    {
        $deliveries = DB::table('digiflazz_statuses')
            ->join('diamond_packs', 'diamond_packs.id', '=', 'digiflazz_statuses.diamond_pack_id')
            ->selectRaw('diamond_packs.game_type as game_type')
            ->selectRaw('COUNT(*) as topups')
            ->selectRaw('MAX(digiflazz_statuses.created_at) as last_topup_at')
            ->where(function ($query): void {
                $query->whereRaw("LOWER(digiflazz_statuses.status) = 'sukses'")
                    ->orWhere('digiflazz_statuses.rc', '00');
            })
            ->groupBy('diamond_packs.game_type');

        $query = DiamondPack::query()
            ->leftJoinSub($deliveries, 'deliveries', 'deliveries.game_type', '=', 'diamond_packs.game_type')
            ->selectRaw('MIN(diamond_packs.id) as id')
            ->selectRaw('diamond_packs.game_type as game_type')
            ->selectRaw('COUNT(*) as packs_count')
            ->selectRaw('SUM(CASE WHEN diamond_packs.is_active = 1 THEN 1 ELSE 0 END) as active_packs_count')
            ->selectRaw('AVG(CASE WHEN diamond_packs.base_price_dzd > 0 THEN ((diamond_packs.price_dzd - diamond_packs.base_price_dzd) / diamond_packs.base_price_dzd) * 100 ELSE NULL END) as avg_profit_percentage')
            ->selectRaw(
                'SUM(CASE WHEN diamond_packs.base_price_dzd IS NOT NULL AND diamond_packs.price > 0 AND diamond_packs.base_price_dzd >= diamond_packs.price * ? THEN 1 ELSE 0 END) as unconverted_cost_count',
                [DiamondPack::UNCONVERTED_COST_RATIO]
            )
            ->selectRaw('COALESCE(MAX(deliveries.topups), 0) as topups_count')
            ->selectRaw('MAX(deliveries.last_topup_at) as last_topup_at')
            ->groupBy('diamond_packs.game_type');

        return GameProvider::scopeDigiflazz($query, 'diamond_packs.game_type');
    }
}
