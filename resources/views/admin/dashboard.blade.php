@extends('layouts.admin')

@section('title', 'Admin Dashboard - DiasZone')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
            <p class="text-gray-600 text-sm md:text-base">Welcome back, <span class="font-semibold text-purple-600">{{ Auth::user()->name ?? 'Admin' }}</span></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Orders</p>
                        <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_orders']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Revenue</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format(round($stats['total_revenue']), 0) }} DZD</p>
                        </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Pending Orders</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ number_format($stats['pending_orders']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-4">Revenue Overview</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Today's Revenue</span>
                        <span class="text-lg font-semibold text-green-600">{{ number_format(round($stats['today_revenue']), 0) }} DZD</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Monthly Revenue</span>
                        <span class="text-lg font-semibold text-purple-600">{{ number_format(round($stats['monthly_revenue']), 0) }} DZD</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Completed Orders</span>
                        <span class="text-lg font-semibold text-blue-600">{{ number_format($stats['completed_orders']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Refunded Orders</span>
                        <span class="text-lg font-semibold text-red-600">{{ number_format($stats['refunded_orders']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.orders') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center">
                        View All Orders
                    </a>
                    <a href="{{ route('admin.users') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center">
                        Manage Users
                    </a>
                    <a href="{{ route('admin.settings') }}" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center">
                        Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6 mb-6 md:mb-8">
            <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-4">Recent Orders</h2>
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">Order ID</th>
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">User</th>
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">Product</th>
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">Amount</th>
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">Status</th>
                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-700 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="py-3 px-4 text-sm text-gray-900 font-mono">{{ $order['id'] }}</td>
                            <td class="py-3 px-4 text-sm font-semibold text-gray-900">{{ $order['user'] }}</td>
                            <td class="py-3 px-4 text-sm text-gray-700">{{ $order['product'] }}</td>
                            <td class="py-3 px-4 text-sm font-bold text-purple-600">{{ number_format(round($order['amount']), 0) }} DZD</td>
                            <td class="py-3 px-4">
                                @php
                                    $status = $order['status'];
                                    $statusClass = 'bg-gray-100 text-gray-800';
                                    $statusText = ucfirst(str_replace('_', ' ', $status));
                                    
                                    if ($status === 'completed') {
                                        $statusClass = 'bg-green-100 text-green-800';
                                    } elseif (in_array($status, ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])) {
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } elseif ($status === 'sending') {
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                    } elseif (in_array($status, ['cancelled', 'refunded'])) {
                                        $statusClass = 'bg-red-100 text-red-800';
                                    }
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">{{ $order['date']->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-500">No recent orders</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                @forelse($recentOrders as $order)
                <div class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl border-2 border-purple-100 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Order ID</p>
                            <p class="text-sm font-bold text-gray-900 font-mono">{{ $order['id'] }}</p>
                        </div>
                        @php
                            $status = $order['status'];
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusText = ucfirst(str_replace('_', ' ', $status));
                            
                            if ($status === 'completed') {
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif (in_array($status, ['pending', 'pending_flexy', 'pending_bmccp', 'pending_cryptopay', 'pending_confirmation'])) {
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            } elseif ($status === 'sending') {
                                $statusClass = 'bg-blue-100 text-blue-800';
                            } elseif (in_array($status, ['cancelled', 'refunded'])) {
                                $statusClass = 'bg-red-100 text-red-800';
                            }
                        @endphp
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                    <div class="space-y-1.5 pt-2 border-t border-gray-200">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">User</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $order['user'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Product</span>
                            <span class="text-sm text-gray-700">{{ $order['product'] }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="text-xs text-gray-500">Amount</span>
                            <span class="text-base font-bold text-purple-600">{{ number_format(round($order['amount']), 0) }} DZD</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Date</span>
                            <span class="text-sm text-gray-600">{{ $order['date']->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">No recent orders</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-4">Recent Users</h2>
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentUsers as $user)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="py-4 px-4 text-sm font-semibold text-gray-900">{{ $user->name }}</td>
                            <td class="py-4 px-4 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="py-4 px-4 text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                @foreach($recentUsers as $user)
                <div class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl border-2 border-purple-100 p-4 shadow-md">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Name</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $user->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Email</span>
                            <span class="text-sm text-gray-700 truncate ml-2">{{ $user->email }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-xs text-gray-500">Joined</span>
                            <span class="text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

