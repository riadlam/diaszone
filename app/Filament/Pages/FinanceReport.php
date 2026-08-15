<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Services\AdminFinanceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class FinanceReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Finance';

    protected static ?string $slug = 'finance';

    protected string $view = 'filament.pages.finance-report';

    /**
     * @var array<string, mixed>|null
     */
    #[Url]
    public ?array $filters = null;

    public function mount(): void
    {
        $this->filters ??= [
            'from' => Carbon::now('Africa/Algiers')->startOfMonth()->toDateString(),
            'to' => Carbon::now('Africa/Algiers')->toDateString(),
        ];

        $this->cacheSchema('filtersForm', $this->getFiltersForm());
        $this->getFiltersForm()->fill($this->filters);
    }

    public function updatedFilters(): void
    {
        $this->resetTable();
    }

    public function getFiltersForm(): Schema
    {
        if ((! $this->isCachingSchemas) && $this->hasCachedSchema('filtersForm')) {
            return $this->getSchema('filtersForm');
        }

        $schema = $this->makeSchema()
            ->columns([
                'md' => 2,
                'xl' => 4,
            ])
            ->extraAttributes(['wire:partial' => 'table-filters-form'])
            ->live()
            ->statePath('filters');

        return $this->filtersForm($schema);
    }

    public function filtersForm(Schema $schema): Schema
    {
        $finance = app(AdminFinanceService::class);

        return $schema
            ->components([
                DatePicker::make('from')
                    ->label('From')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection(),

                DatePicker::make('to')
                    ->label('To')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection(),

                Select::make('payment_method')
                    ->label('Payment method')
                    ->options($finance->paymentMethodOptions())
                    ->placeholder('All payment methods')
                    ->searchable(),

                Select::make('game_type')
                    ->label('Game')
                    ->options($finance->gameTypeOptions())
                    ->placeholder('All games')
                    ->searchable(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filters')
                    ->description('Revenue uses money received on paid orders. Cost uses provider deliveries on those orders.')
                    ->schema([
                        EmbeddedSchema::make('filtersForm'),
                    ])
                    ->columns(1)
                    ->compact()
                    ->footer([
                        Action::make('thisMonth')
                            ->label('This month')
                            ->color('gray')
                            ->action(fn () => $this->applyPreset(
                                Carbon::now('Africa/Algiers')->startOfMonth()->toDateString(),
                                Carbon::now('Africa/Algiers')->toDateString(),
                            )),
                        Action::make('lastMonth')
                            ->label('Last month')
                            ->color('gray')
                            ->action(function (): void {
                                $start = Carbon::now('Africa/Algiers')->subMonth()->startOfMonth();
                                $this->applyPreset(
                                    $start->toDateString(),
                                    $start->copy()->endOfMonth()->toDateString(),
                                );
                            }),
                        Action::make('last30Days')
                            ->label('Last 30 days')
                            ->color('gray')
                            ->action(fn () => $this->applyPreset(
                                Carbon::now('Africa/Algiers')->subDays(29)->toDateString(),
                                Carbon::now('Africa/Algiers')->toDateString(),
                            )),
                        Action::make('allTime')
                            ->label('All time')
                            ->color('gray')
                            ->action(fn () => $this->applyPreset(
                                Order::query()->min('created_at')
                                    ? Carbon::parse(Order::query()->min('created_at'))->toDateString()
                                    : Carbon::now('Africa/Algiers')->toDateString(),
                                Carbon::now('Africa/Algiers')->toDateString(),
                            )),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $finance = app(AdminFinanceService::class);

        return $table
            ->query(fn () => $this->filteredOrdersQuery())
            ->defaultSort('created_at', 'desc')
            ->heading('Order breakdown')
            ->description('Paid orders in the selected range with revenue, provider cost, and net profit.')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight('bold')
                    ->searchable()
                    ->copyable()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->description(fn (Order $record): string => $record->gameLabel()),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->description(fn (Order $record): ?string => $record->user?->email),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrdersTable::STATUSES[$state] ?? ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (Order $record): string => $record->statusColor()),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? ucwords(str_replace('_', ' ', $state))
                        : 'Not set')
                    ->color('gray'),

                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->state(fn (Order $record): string => $finance->formatMoney($finance->orderRevenue($record)))
                    ->weight('bold'),

                TextColumn::make('cost')
                    ->label('Cost')
                    ->state(fn (Order $record): string => $finance->formatMoney($finance->orderCost($record)))
                    ->description(fn (Order $record): string => $finance->orderDeliveriesCount($record).' delivered'),

                TextColumn::make('profit')
                    ->label('Net profit')
                    ->state(fn (Order $record): string => $finance->formatMoney($finance->orderProfit($record)))
                    ->badge()
                    ->color(fn (Order $record): string => $finance->orderProfit($record) >= 0 ? 'success' : 'danger'),

                TextColumn::make('margin')
                    ->label('Margin')
                    ->state(function (Order $record) use ($finance): string {
                        $revenue = $finance->orderRevenue($record);
                        $profit = $finance->orderProfit($record);

                        return $finance->formatPercentage($finance->grossMarginPercentage($revenue, $profit));
                    }),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ]);
    }

    /**
     * @return array{
     *     revenue: float,
     *     cost: float,
     *     profit: float,
     *     margin: ?float,
     *     orders_count: int,
     *     deliveries_count: int
     * }
     */
    public function summary(): array
    {
        [$from, $to] = $this->filterDates();

        return app(AdminFinanceService::class)->summarizePeriod($from, $to, $this->filters ?? []);
    }

    /**
     * @return Collection<int, array{
     *     date: string,
     *     revenue: float,
     *     cost: float,
     *     profit: float,
     *     margin: ?float,
     *     orders_count: int,
     *     deliveries_count: int
     * }>
     */
    public function dailyRows(): Collection
    {
        [$from, $to] = $this->filterDates();
        $finance = app(AdminFinanceService::class);

        $orders = $finance->ordersQuery($from, $to, $this->filters ?? [])
            ->with([
                'diamondPack',
                'orderItems.diamondPack',
                'digiflazzStatuses.diamondPack',
                'item4gamerOrders.diamondPack',
            ])
            ->orderByDesc('created_at')
            ->get();

        return $finance->dailyBreakdown($orders);
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string|null, color: string}>
     */
    public function summaryCards(): array
    {
        $finance = app(AdminFinanceService::class);
        $summary = $this->summary();

        return [
            [
                'label' => 'Gross revenue',
                'value' => $finance->formatMoney($summary['revenue']),
                'hint' => 'Money received on paid orders',
                'color' => 'success',
            ],
            [
                'label' => 'Provider cost',
                'value' => $finance->formatMoney($summary['cost']),
                'hint' => $summary['deliveries_count'].' delivered top-ups',
                'color' => 'danger',
            ],
            [
                'label' => 'Net profit',
                'value' => $finance->formatMoney($summary['profit']),
                'hint' => 'Revenue minus provider cost',
                'color' => $summary['profit'] >= 0 ? 'warning' : 'danger',
            ],
            [
                'label' => 'Gross margin',
                'value' => $finance->formatPercentage($summary['margin']),
                'hint' => $summary['orders_count'].' paid orders',
                'color' => 'info',
            ],
        ];
    }

    protected function filteredOrdersQuery()
    {
        [$from, $to] = $this->filterDates();

        return app(AdminFinanceService::class)
            ->ordersQuery($from, $to, $this->filters ?? [])
            ->with([
                'user',
                'diamondPack',
                'orderItems.diamondPack',
                'digiflazzStatuses.diamondPack',
                'item4gamerOrders.diamondPack',
            ]);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function filterDates(): array
    {
        $from = filled($this->filters['from'] ?? null)
            ? Carbon::parse($this->filters['from'], 'Africa/Algiers')->startOfDay()
            : null;

        $to = filled($this->filters['to'] ?? null)
            ? Carbon::parse($this->filters['to'], 'Africa/Algiers')->endOfDay()
            : null;

        return [$from, $to];
    }

    protected function applyPreset(string $from, string $to): void
    {
        $this->filters = array_merge($this->filters ?? [], [
            'from' => $from,
            'to' => $to,
        ]);

        $this->getFiltersForm()->fill($this->filters);
        $this->resetTable();
    }
}
