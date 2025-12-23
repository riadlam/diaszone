@extends('layouts.admin')

@section('title', 'Seller Details - Admin')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
<div class="mb-6">
    <a href="{{ route('admin.sellers.index') }}" class="text-purple-600 hover:text-purple-700">
        ← Back to Sellers
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Seller Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-2xl">{{ substr($seller->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $seller->name }}</h1>
                        <p class="text-gray-500">@{{ $seller->username }}</p>
                    </div>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-medium 
                    {{ $seller->status === 'active' ? 'bg-green-100 text-green-700' : 
                       ($seller->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($seller->status) }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 text-sm">Email</p>
                    <p class="text-gray-900">{{ $seller->email }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Phone</p>
                    <p class="text-gray-900">{{ $seller->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Store Name</p>
                    <p class="text-gray-900">{{ $seller->store_name ?? $seller->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Primary Platform</p>
                    <p class="text-gray-900">{{ ucfirst($seller->main_platform ?? 'N/A') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Platform URL</p>
                    <p class="text-gray-900">@if(!empty($seller->platform_url))<a href="{{ $seller->platform_url }}" target="_blank" class="text-blue-600 underline">{{ $seller->platform_url }}</a>@else N/A @endif</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Registered</p>
                    <p class="text-gray-900">{{ $seller->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            
            @if($seller->store_description)
                <div class="mt-4">
                    <p class="text-gray-500 text-sm">Store Description</p>
                    <p class="text-gray-900">{{ $seller->store_description }}</p>
                </div>
            @endif
            
            <div class="mt-6 flex space-x-3">
                <a href="{{ route('admin.sellers.edit', $seller) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Edit Seller
                </a>
                <a href="{{ route('seller.store.home', $seller->username) }}" target="_blank" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    View Store →
                </a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-gray-500 text-sm">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-gray-500 text-sm">Completed</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['completed_orders']) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-gray-500 text-sm">Total Revenue</p>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-gray-500 text-sm">Total Profit</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_profit'], 2) }}</p>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
                <a href="{{ route('admin.sellers.orders', $seller) }}" class="text-purple-600 hover:text-purple-700 text-sm">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($seller->orders as $order)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-gray-500 text-sm">{{ $order->created_at->format('M d, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">+{{ number_format($order->seller_profit ?? 0, 2) }} DZD</p>
                            <span class="text-xs px-2 py-0.5 rounded-full 
                                {{ $order->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No orders yet</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Wallet -->
        <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl p-6 text-white">
            <p class="text-purple-200">{{ __('seller.wallet_balance') }}</p>
            <p class="text-3xl font-bold">{{ number_format($seller->wallet_balance, 0, '.', '') }} DZD</p>
        </div>
        
        <!-- Top Up Wallet -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top Up Wallet</h3>
            <form action="{{ route('admin.sellers.topup', $seller) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-gray-600 text-sm mb-1">Amount (DZD)</label>
                    <input type="number" name="amount" step="0.01" min="1" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-gray-600 text-sm mb-1">Description</label>
                    <input type="text" name="description" placeholder="Optional"
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Add Funds
                </button>
            </form>
        </div>
        
        <!-- Change Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Change Status</h3>
            <form action="{{ route('admin.sellers.status', $seller) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                    <option value="active" {{ $seller->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ $seller->status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ $seller->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="w-full py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Update Status
                </button>
            </form>
        </div>
        
        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                <a href="{{ route('admin.sellers.transactions', $seller) }}" class="text-purple-600 hover:text-purple-700 text-sm">View All →</a>
            </div>
            <div class="space-y-3">
                @forelse($seller->walletTransactions as $transaction)
                    <div class="flex items-center justify-between p-2 border-b border-gray-100">
                        <div>
                            <p class="text-sm text-gray-900">{{ Str::limit($transaction->description, 20) }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->created_at->format('M d') }}</p>
                        </div>
                        <p class="font-bold {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500 text-center text-sm">No transactions</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection
