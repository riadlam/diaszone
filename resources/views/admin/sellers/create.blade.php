@extends('layouts.admin')

@section('title', 'Create New Seller - Admin')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
<div class="mb-6">
    <a href="{{ route('admin.sellers.index') }}" class="text-purple-600 hover:text-purple-700">
        ← Back to Sellers
    </a>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Seller</h1>
        
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('admin.sellers.store') }}" method="POST" class="space-y-5">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Username *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        pattern="[a-zA-Z0-9_-]+"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                    <p class="text-gray-500 text-xs mt-1">Used for store URL: /store/username</p>
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Password *</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Store Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Status *</label>
                    <select name="status" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">{{ __('seller.initial_wallet_balance') }}</label>
                    <input type="number" name="wallet_balance" value="{{ old('wallet_balance', 0) }}" step="0.01" min="0"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Allowed Games</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($gameTypes as $type)
                        <label class="flex items-center space-x-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="allowed_games[]" value="{{ $type }}"
                                class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-gray-700">
                                {{ ucfirst(str_replace('mobilelegends', 'Mobile Legends', str_replace('freefire', 'Free Fire', str_replace('pubgmobile', 'PUBG Mobile', str_replace('honorofkings', 'Honor of Kings', str_replace('bloodstrike', 'Blood Strike', $type)))))) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="text-gray-500 text-xs mt-1">Leave empty to allow all games</p>
            </div>
            
            <div class="pt-4">
                <button type="submit" class="w-full py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition">
                    Create Seller
                </button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>
@endsection
