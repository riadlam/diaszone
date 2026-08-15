<x-filament-panels::page>
    {{ $this->content }}

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($this->summaryCards() as $card)
            <div @class([
                'rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10',
                'border-s-4 border-success-500' => $card['color'] === 'success',
                'border-s-4 border-danger-500' => $card['color'] === 'danger',
                'border-s-4 border-warning-500' => $card['color'] === 'warning',
                'border-s-4 border-info-500' => $card['color'] === 'info',
            ])>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $card['label'] }}
                </p>

                <p class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $card['value'] }}
                </p>

                @if (filled($card['hint']))
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $card['hint'] }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                Daily breakdown
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Revenue, cost, and net profit grouped by order date.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Orders</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Deliveries</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Revenue</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cost</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Net profit</th>
                        <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($this->dailyRows() as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">
                                {{ \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['orders_count'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row['deliveries_count'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-success-600 dark:text-success-400">
                                {{ app(\App\Services\AdminFinanceService::class)->formatMoney($row['revenue']) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-danger-600 dark:text-danger-400">
                                {{ app(\App\Services\AdminFinanceService::class)->formatMoney($row['cost']) }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-950 dark:text-white">
                                {{ app(\App\Services\AdminFinanceService::class)->formatMoney($row['profit']) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ app(\App\Services\AdminFinanceService::class)->formatPercentage($row['margin']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No paid orders found for this range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
