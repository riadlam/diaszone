@extends('layouts.admin')

@section('title', 'Edit Seller - Admin')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
<div class="mb-6">
    <a href="{{ route('admin.sellers.show', $seller) }}" class="text-purple-600 hover:text-purple-700">
        ← Back to Seller
    </a>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Seller: {{ $seller->name }}</h1>
        
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('admin.sellers.update', $seller) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $seller->name) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Username</label>
                    <input type="text" value="{{ $seller->username }}" disabled
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    <p class="text-gray-500 text-xs mt-1">Username cannot be changed</p>
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $seller->email) }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $seller->phone) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Store Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $seller->store_name) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Status *</label>
                <select name="status" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-purple-500 outline-none">
                    <option value="active" {{ old('status', $seller->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ old('status', $seller->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ old('status', $seller->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            
            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">Allowed Games</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($gameTypes as $type)
                        <label class="flex items-center space-x-2 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="allowed_games[]" value="{{ $type }}"
                                {{ in_array($type, $seller->allowed_games ?? []) ? 'checked' : '' }}
                                class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-gray-700">
                                {{ ucfirst(str_replace('mobilelegends', 'Mobile Legends', str_replace('freefire', 'Free Fire', str_replace('pubgmobile', 'PUBG Mobile', str_replace('honorofkings', 'Honor of Kings', str_replace('bloodstrike', 'Blood Strike', $type)))))) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="text-gray-500 text-xs mt-1">Leave empty to allow all games</p>
            </div>
            
            <div class="pt-4 flex space-x-4">
                <button type="submit" class="flex-1 py-3 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 transition">
                    Update Seller
                </button>
                <a href="{{ route('admin.sellers.show', $seller) }}" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
    </div>
</div>
@endsection
