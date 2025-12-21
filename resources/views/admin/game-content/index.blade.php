@extends('layouts.admin')

@section('title', 'Game Content Management - Admin - DiasZone')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Game Content Management</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">Manage game descriptions, instructions, and images</p>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
            @if($games->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Game</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Game Type</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Content Status</th>
                            <th class="text-left py-4 px-4 text-sm font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($games as $game)
                        <tr class="hover:bg-purple-50 transition-colors">
                            <td class="py-4 px-4 text-sm font-semibold text-gray-900">{{ $game->name }}</td>
                            <td class="py-4 px-4 text-sm text-gray-700 font-mono">{{ $game->game_type }}</td>
                            <td class="py-4 px-4">
                                @if($game->content)
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        Has Content
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                        No Content
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <a href="{{ route('admin.game-content.edit', $game) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($games as $game)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $game->name }}</h3>
                            <p class="text-xs text-gray-500 font-mono mt-1">{{ $game->game_type }}</p>
                        </div>
                        @if($game->content)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                Has Content
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                No Content
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('admin.game-content.edit', $game) }}" 
                       class="block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        Manage Content
                    </a>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-gray-600">No games found.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
