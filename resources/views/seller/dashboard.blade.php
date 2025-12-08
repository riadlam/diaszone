@extends('layouts.seller')

@section('title', 'Dashboard - Seller Panel')
@section('header', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="stat-card rounded-xl p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Today's Orders</p>
                <p class="text-3xl font-bold text-white">{{ $todayOrders }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="stat-card rounded-xl p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Orders</p>
                <p class="text-3xl font-bold text-white">{{ $totalOrders }}</p>
            </div>
            <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
        <div class="mt-2 flex items-center text-sm">
            <span class="text-green-400">{{ $completedOrders }} completed</span>
            <span class="mx-2 text-gray-600">•</span>
            <span class="text-yellow-400">{{ $pendingOrders }} pending</span>
        </div>
    </div>
    
    <!-- Today's Profit card removed per request -->
    
    <div class="stat-card rounded-xl p-6 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">This Month</p>
                <p class="text-3xl font-bold text-white">{{ number_format($monthRevenue, 2) }} <span class="text-lg">DZD</span></p>
            </div>
            <div class="w-12 h-12 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions removed per design request -->

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Recent Orders</h3>
            <a href="{{ route('seller.orders') }}" class="text-blue-400 hover:text-blue-300 text-sm">View All →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                    <div>
                        <p class="font-medium text-sm">{{ $order->order_number }}</p>
                        <p class="text-gray-400 text-xs">{{ $order->diamondPack->name ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm">+{{ number_format($order->seller_profit ?? 0, 2) }} DZD</p>
                        <span class="inline-block px-2 py-0.5 text-xs rounded-full 
                            {{ $order->status === 'completed' ? 'bg-green-500/20 text-green-400' : 
                               ($order->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-500/20 text-gray-400') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-400 py-8">
                    <p>No orders yet</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="bg-slate-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Recent Transactions</h3>
            <a href="{{ route('seller.wallet') }}" class="text-blue-400 hover:text-blue-300 text-sm">View All →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentTransactions as $transaction)
                <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg">
                    <div>
                        <p class="font-medium text-sm">{{ $transaction->description }}</p>
                        <p class="text-gray-400 text-xs">{{ $transaction->created_at->format('M d, H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm {{ $transaction->type === 'credit' ? 'text-green-400' : 'text-red-400' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} DZD
                        </p>
                        <p class="text-gray-400 text-xs">Balance: {{ number_format($transaction->balance_after, 2) }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-400 py-8">
                    <p>No transactions yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Chart -->
<div class="mt-6 bg-slate-800 rounded-xl p-6">
    <h3 class="text-lg font-bold mb-4">Last 7 Days Performance</h3>
    <div class="h-64" id="chart-container">
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
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [{
                label: 'Orders',
                data: chartData.map(d => d.orders),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Revenue (DZD)',
                data: chartData.map(d => d.revenue),
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
                    ticks: { color: '#9ca3af' },
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
