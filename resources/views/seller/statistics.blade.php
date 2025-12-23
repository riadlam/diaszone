@extends('layouts.seller')

@section('title', __('seller.statistics') . ' - ' . __('seller.seller_panel'))
@section('header', __('seller.statistics'))

@section('content')
<!-- Overview Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-slate-800 rounded-xl p-5 border border-slate-700">
        <p class="text-gray-400 text-sm">{{ __('seller.total_orders') }}</p>
        <p class="text-2xl font-bold text-white">{{ number_format($stats['total_orders']) }}</p>
    </div>
    <div class="bg-slate-800 rounded-xl p-5 border border-green-500/30">
        <p class="text-gray-400 text-sm">{{ __('seller.completed') }}</p>
        <p class="text-2xl font-bold text-green-400">{{ number_format($stats['completed_orders']) }}</p>
    </div>
    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-5">
        <p class="text-blue-100 text-sm">{{ __('seller.wallet_balance') }}</p>
        <p class="text-2xl font-bold text-white">{{ number_format($stats['wallet_balance'], 0, '.', '') }} DZD</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Monthly Stats -->
    <div class="bg-slate-800 rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">{{ __('seller.monthly_performance') }}</h3>
        <div class="space-y-3">
            @forelse($monthlyStats as $month)
                <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                    <div>
                        <p class="font-medium text-white">{{ date('F Y', mktime(0, 0, 0, $month->month, 1, $month->year)) }}</p>
                        <p class="text-gray-500 text-sm">{{ $month->orders }} {{ __('seller.orders') }}</p>
                    </div>
                    <p class="text-green-400 font-bold">+{{ number_format($month->profit ?? 0, 2) }} DZD</p>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4">{{ __('seller.no_data_available') }}</p>
            @endforelse
        </div>
    </div>
    
    <!-- Top Selling Packs -->
    <div class="bg-slate-800 rounded-xl p-6">
        <h3 class="text-lg font-bold mb-4">{{ __('seller.top_selling_packs') }}</h3>
        <div class="space-y-3">
            @forelse($topPacks as $index => $item)
                <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center font-bold text-sm">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="font-medium text-white">{{ $item->diamondPack->name ?? 'Unknown' }}</p>
                            <p class="text-gray-500 text-xs">{{ ucfirst($item->diamondPack->game_type ?? '') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-white font-medium">{{ $item->count }} {{ __('seller.sold') }}</p>
                        <p class="text-green-400 text-sm">+{{ number_format($item->profit ?? 0, 2) }} DZD</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4">{{ __('seller.no_sales_data_yet') }}</p>
            @endforelse
        </div>
    </div>
</div>

<!-- 30 Day Chart -->
<div class="bg-slate-800 rounded-xl p-6">
    <h3 class="text-lg font-bold mb-4">{{ __('seller.last_30_days_performance') }}</h3>
    <div class="h-80">
        <canvas id="performanceChart"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: {!! json_encode(__('seller.orders')) !!},
                data: chartData.map(d => d.orders),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderRadius: 4,
                yAxisID: 'y'
            }, {
                label: {!! json_encode(__('seller.revenue')) !!},
                data: chartData.map(d => d.revenue),
                type: 'line',
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: { color: '#9ca3af' },
                    grid: { color: 'rgba(156, 163, 175, 0.1)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    ticks: { color: '#9ca3af' },
                    grid: { drawOnChartArea: false }
                },
                x: {
                    ticks: { color: '#9ca3af', maxRotation: 45 },
                    grid: { color: 'rgba(156, 163, 175, 0.1)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            }
        }
    });
</script>
@endpush
