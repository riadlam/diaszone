@extends('layouts.admin')

@section('title', 'Seller Management - Admin')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <h1 class="text-2xl font-bold text-gray-800">Seller Management</h1>
    <a href="{{ route('admin.sellers.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
        + Add New Seller
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl p-4 mb-6 shadow-sm">
    <form action="{{ route('admin.sellers.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-gray-600 text-sm mb-1">Status</label>
            <select name="status" class="px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-gray-600 text-sm mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, username..."
                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
        </div>
        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            Filter
        </button>
        <a href="{{ route('admin.sellers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            Reset
        </a>
    </form>
</div>

<!-- Sellers Table -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Wallet</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Orders</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Earnings</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($sellers as $seller)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold">{{ substr($seller->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $seller->name }}</p>
                                <p class="text-gray-500 text-sm">{{ $seller->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <a href="{{ route('seller.store.home', $seller->username) }}" target="_blank" class="text-purple-600 hover:text-purple-700">
                            @{{ $seller->username }}
                        </a>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="font-bold text-green-600">{{ number_format($seller->wallet_balance, 0, '.', '') }} DZD</span>
                    </td>
                    <td class="px-4 py-4 text-center text-gray-900">
                        {{ $seller->orders_count }}
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="text-purple-600 font-medium">{{ number_format($seller->total_earnings, 2) }} DZD</span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="px-3 py-1 text-xs rounded-full 
                            {{ $seller->status === 'active' ? 'bg-green-100 text-green-700' : 
                               ($seller->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($seller->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="{{ route('admin.sellers.show', $seller) }}" class="text-blue-600 hover:text-blue-700" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.sellers.edit', $seller) }}" class="text-yellow-600 hover:text-yellow-700" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        No sellers found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($sellers->hasPages())
        <div class="p-4 border-t">
            {{ $sellers->links() }}
        </div>
    @endif
</div>
    </div>
</div>
@endsection
