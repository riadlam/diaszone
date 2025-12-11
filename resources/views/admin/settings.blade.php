@extends('layouts.admin')

@section('title', 'Settings - Admin - DiasZone')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Settings</h1>
            <p class="text-gray-600 mt-2 text-sm md:text-base">Manage system settings and configurations</p>
        </div>

        <!-- Settings Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
            <!-- General Settings -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">General Settings</h2>
                </div>
                <p class="text-gray-600 text-sm">Configure general application settings</p>
                <button class="mt-4 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Configure
                </button>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Payment Settings</h2>
                </div>
                <p class="text-gray-600 text-sm">Manage payment gateways and configurations</p>
                <button class="mt-4 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Configure
                </button>
            </div>

            <!-- API Settings -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">API Settings</h2>
                </div>
                <p class="text-gray-600 text-sm">Configure API keys and integrations</p>
                <button class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Configure
                </button>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">Notifications</h2>
                </div>
                <p class="text-gray-600 text-sm">Manage email and system notifications</p>
                <button class="mt-4 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Configure
                </button>
            </div>
        </div>

        <!-- Coming Soon Notice -->
        <div class="mt-6 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border-2 border-purple-200 p-6 text-center">
            <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('settings.settings_panel_coming_soon') }}</h3>
            <p class="text-gray-600">We're working on building a comprehensive settings panel for managing all aspects of your DiasZone platform.</p>
        </div>
    </div>
</div>
@endsection

