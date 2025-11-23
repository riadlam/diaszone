@extends('layouts.admin')

@section('title', 'Manage Users - Admin - DiasZone')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Manage Users</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">View and manage all users</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
            @if($users->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Joined</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="py-4 px-4 text-sm text-gray-900 font-mono">{{ $user->id }}</td>
                            <td class="py-4 px-4 text-sm font-semibold text-gray-900">{{ $user->name }}</td>
                            <td class="py-4 px-4 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold 
                                    {{ ($user->status ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($user->status ?? 'active') }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="py-4 px-4">
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm text-purple-600 hover:text-purple-700 font-semibold transition-colors">
                                        {{ ($user->status ?? 'active') === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($users as $user)
                <div class="bg-gradient-to-br from-white to-purple-50/30 rounded-xl border-2 border-purple-100 p-4 shadow-md">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">User ID</p>
                            <p class="text-sm font-bold text-gray-900 font-mono">#{{ $user->id }}</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold 
                            {{ ($user->status ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($user->status ?? 'active') }}
                        </span>
                    </div>
                    <div class="space-y-2 border-t border-gray-200 pt-3">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Name</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $user->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Email</span>
                            <span class="text-sm text-gray-700 truncate ml-2">{{ $user->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Joined</span>
                            <span class="text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200">
                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    {{ ($user->status ?? 'active') === 'active' ? 'Deactivate User' : 'Activate User' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
                {{ $users->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="text-gray-600 text-lg font-semibold">No users found</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

