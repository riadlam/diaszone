<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\FinanceReport;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\AdminFinanceService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Overview';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $finance = app(AdminFinanceService::class);

        $monthStart = Carbon::now('Africa/Algiers')->startOfMonth();
        $monthEnd = Carbon::now('Africa/Algiers')->endOfDay();

        $thisMonth = $finance->summarizePeriod($monthStart, $monthEnd);
        $allTime = $finance->summarizePeriod();
        $monthTopups = $finance->deliveredTopupsCount($monthStart, $monthEnd);

        return [
            Stat::make('Revenue this month', $finance->formatMoney($thisMonth['revenue']))
                ->description($finance->formatMoney($thisMonth['profit']).' net profit')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->url(FinanceReport::getUrl()),

            Stat::make('Net profit this month', $finance->formatMoney($thisMonth['profit']))
                ->description($finance->formatPercentage($thisMonth['margin']).' gross margin')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color($thisMonth['profit'] >= 0 ? 'success' : 'danger')
                ->url(FinanceReport::getUrl()),

            Stat::make('Total revenue', $finance->formatMoney($allTime['revenue']))
                ->description($allTime['orders_count'].' paid orders')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag)
                ->color('info')
                ->url(FinanceReport::getUrl()),

            Stat::make('Total net profit', $finance->formatMoney($allTime['profit']))
                ->description($finance->formatPercentage($allTime['margin']).' gross margin')
                ->descriptionIcon(Heroicon::OutlinedCurrencyDollar)
                ->color($allTime['profit'] >= 0 ? 'warning' : 'danger')
                ->url(FinanceReport::getUrl()),

            Stat::make('Paid orders this month', (string) $thisMonth['orders_count'])
                ->description($finance->formatMoney($thisMonth['cost']).' provider cost')
                ->descriptionIcon(Heroicon::OutlinedReceiptPercent)
                ->color('gray')
                ->url(OrderResource::getUrl()),

            Stat::make('Delivered top-ups this month', (string) $monthTopups)
                ->description((string) $thisMonth['deliveries_count'].' on paid orders in range')
                ->descriptionIcon(Heroicon::OutlinedBolt)
                ->color('warning'),
        ];
    }
}
