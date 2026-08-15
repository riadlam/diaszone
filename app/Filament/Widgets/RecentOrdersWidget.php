<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use App\Services\AdminFinanceService;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent orders';

    public function table(Table $table): Table
    {
        $finance = app(AdminFinanceService::class);

        return $table
            ->query(
                Order::query()
                    ->with(['user', 'diamondPack', 'orderItems'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (Order $record): string => $record->gameLabel()),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => OrdersTable::STATUSES[$state] ?? ucwords(str_replace('_', ' ', $state)))
                    ->color(fn (Order $record): string => $record->statusColor()),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->placeholder('Guest')
                    ->description(fn (Order $record): ?string => $record->user?->email),

                TextColumn::make('amount')
                    ->label('Revenue')
                    ->state(fn (Order $record): string => $finance->formatMoney($finance->orderRevenue($record)))
                    ->weight('bold'),

                TextColumn::make('profit')
                    ->label('Net profit')
                    ->state(function (Order $record) use ($finance): string {
                        if (! in_array($record->status, $finance->paidStatuses(), true)) {
                            return '—';
                        }

                        return $finance->formatMoney($finance->orderProfit($record));
                    })
                    ->badge()
                    ->color(function (Order $record) use ($finance): string {
                        if (! in_array($record->status, $finance->paidStatuses(), true)) {
                            return 'gray';
                        }

                        return $finance->orderProfit($record) >= 0 ? 'success' : 'danger';
                    }),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y, H:i')
                    ->since(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
