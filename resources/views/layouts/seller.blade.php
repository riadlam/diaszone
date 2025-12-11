<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Seller Dashboard - DiasZone')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    
    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
        .seller-sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
        }
        .seller-sidebar-item {
            transition: all 0.3s ease;
        }
        .seller-sidebar-item:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: translateX(5px);
        }
        .seller-sidebar-item.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.3) 0%, rgba(59, 130, 246, 0.1) 100%);
            border-left: 4px solid #3b82f6;
        }
        .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }
        
        @media (max-width: 768px) {
            .seller-sidebar-item:hover {
                transform: none;
            }
        }

        /* RTL adjustments */
        [dir="rtl"] .seller-sidebar-item:hover {
            transform: translateX(-5px);
        }
        [dir="rtl"] .seller-sidebar-item.active {
            border-left: none;
            border-right: 4px solid #3b82f6;
        }
        /* Swap dropdown anchors in RTL to avoid overflow */
        [dir="rtl"] .language-dropdown-menu,
        [dir="rtl"] .cart-dropdown-menu,
        [dir="rtl"] .currency-dropdown-menu,
        [dir="rtl"] .profile-dropdown-menu {
            left: 0 !important;
            right: auto !important;
        }
        /* Header icons aligned to the right in RTL */
        [dir="rtl"] .header-left-icon {
            left: auto !important;
            right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0.75rem !important;
        }
        [dir="rtl"] .search-input {
            padding-left: 1rem !important;
            padding-right: 2.5rem !important;
            text-align: right !important;
        }
        [dir="rtl"] .search-input::placeholder {
            text-align: right !important;
        }
    </style>
    
    @stack('styles')
</head>
@php $isRtl = app()->getLocale() == 'ar'; @endphp
<body class="bg-slate-900 text-white {{ $isRtl ? 'overflow-x-hidden' : '' }}">
    <div class="flex lg:h-screen lg:overflow-hidden">
        <!-- Sidebar -->
        <aside class="seller-sidebar w-64 flex-shrink-0 hidden lg:block">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-blue-800/30">
                    <a href="{{ route('seller.dashboard') }}" class="flex items-center space-x-3">
                        @php $me = Auth::guard('seller')->user(); @endphp
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center overflow-hidden">
                            @if(!empty($me->store_logo_thumb ?? $me->store_logo))
                                <img src="{{ storage_public_url($me->store_logo_thumb ?? $me->store_logo) }}" alt="{{ $me->store_name ?? $me->name }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-white font-bold text-xl">DZ</span>
                            @endif
                        <div>
                            <h2 class="text-xl font-bold">@yield('header', __('seller.dashboard'))</h2>
                        </div>
                                <p class="text-blue-300 text-xs">{{ __('seller.seller_panel') }}</p>
                            </div>
                        </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="{{ route('seller.dashboard') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>{{ __('seller.dashboard') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.packs') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.packs*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>{{ __('seller.packs_pricing') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.orders') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.orders*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>{{ __('seller.orders') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.wallet') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.wallet*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>{{ __('seller.wallet') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.direct-topup') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.direct-topup*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>{{ __('seller.direct_topup') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.statistics') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.statistics*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>{{ __('seller.statistics') }}</span>
                    </a>

                    <a href="{{ route('seller.settings') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.settings*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 4.5v1.5m0 12V21m8.25-8.25h-1.5M4.5 11.25H3m15.536 6.364l-1.06-1.06M6.324 6.324l-1.06-1.06m0 12.728l1.06-1.06M18.686 6.324l1.06-1.06" />
                        </svg>
                        <span>{{ __('seller.settings') }}</span>
                    </a>
                    
                    <div class="border-t border-blue-800/30 my-4"></div>
                    
                    <a href="{{ route('seller.store.home', ['username' => Auth::guard('seller')->user()->username]) }}" target="_blank" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span>{{ __('seller.view_my_store') }}</span>
                    </a>
                    
                    <a href="{{ route('seller.profile') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.profile*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ __('seller.profile') }}</span>
                    </a>
                </nav>
                
                <!-- Wallet Balance -->
                <div class="p-4 border-t border-blue-800/30">
                    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg p-4">
                        <p class="text-blue-100 text-sm">{{ __('seller.wallet_balance') }}</p>
                        <p class="text-white text-2xl font-bold">{{ number_format(Auth::guard('seller')->user()->wallet_balance, 0, '.', '') }} DZD</p>
                    </div>
                </div>
                
                <!-- Logout -->
                <div class="p-4 border-t border-blue-800/30">
                    <form action="{{ route('seller.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>{{ __('profile.logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Menu Button - moved into header to avoid floating -->
        
        <!-- Main Content -->
        <!-- On mobile: scroll main (header included) so header is not sticky. On lg+: make content scrollable while keeping header visible. -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-slate-800 shadow-lg p-4 relative lg:sticky lg:top-0 lg:flex-shrink-0 z-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="lg:hidden mr-3">
                            <button id="mobile-menu-btn" aria-controls="mobile-menu" aria-expanded="false" aria-label="{{ __('nav.open_navigation') }}" class="p-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg ring-1 ring-slate-700/50 hover:ring-2 hover:ring-blue-500 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    
                        <h2 class="text-xl font-bold">@yield('header', __('seller.dashboard'))</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-400 mr-2">{{ Auth::guard('seller')->user()->store_name ?? Auth::guard('seller')->user()->name }}</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center overflow-hidden">
                                @php $me2 = Auth::guard('seller')->user(); @endphp
                                @if(!empty($me2->store_logo_thumb ?? $me2->store_logo))
                                    <img src="{{ storage_public_url($me2->store_logo_thumb ?? $me2->store_logo) }}" alt="{{ $me2->store_name ?? $me2->name }}" class="w-full h-full object-cover rounded-full" />
                                @else
                                    <span class="text-white font-bold">{{ substr($me2->name, 0, 1) }}</span>
                                @endif
                            </div>
                        </div>
                        <!-- Mobile language dropdown: visible on mobile navbar (not in side menu) -->
                        <div class="lg:hidden">
                            @include('components.language-dropdown')
                        </div>
                    </div>
                    <!-- Global loading spinner overlay -->
                    <div id="global-loading-spinner" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40 hidden">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-12 w-12 text-cyan-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span class="text-white text-lg font-semibold">{{ __('common.loading') }}</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <!-- On lg+: make inner content scrollable (header remains visible); on smaller screens it's just a regular block so whole main scrolls including header. -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-600/20 border border-green-500/30 text-green-400 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-600/20 border border-red-500/30 text-red-400 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-600/20 border border-red-500/30 text-red-400 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="lg:hidden fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="absolute inset-0 bg-black/50" id="mobile-menu-overlay" tabindex="-1"></div>
        <aside id="mobile-menu-panel" class="seller-sidebar w-72 sm:w-80 md:w-64 h-full relative z-10 transform transition-transform duration-300 ease-in-out {{ $isRtl ? 'translate-x-full right-0' : '-translate-x-full left-0' }}">
            <!-- Same content as desktop sidebar -->
            <div class="flex flex-col h-full">
                <div class="p-6 border-b border-blue-800/30 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">DZ</span>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-xl">DiasZone</h1>
                            <p class="text-blue-300 text-xs">{{ __('seller.seller_panel') }}</p>
                        </div>
                    </div>
                    <button id="close-mobile-menu" aria-label="{{ __('nav.close_navigation') }}" class="text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="{{ route('seller.dashboard') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>{{ __('seller.dashboard') }}</span>
                    </a>
                    <a href="{{ route('seller.packs') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>{{ __('seller.packs_pricing') }}</span>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>{{ __('seller.orders') }}</span>
                    </a>
                    <a href="{{ route('seller.wallet') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>{{ __('seller.wallet') }}</span>
                    </a>
                    <a href="{{ route('seller.direct-topup') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>{{ __('seller.direct_topup') }}</span>
                    </a>
                    <a href="{{ route('seller.statistics') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>{{ __('seller.statistics') }}</span>
                    </a>
                    <a href="{{ route('seller.settings') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 4.5v1.5m0 12V21m8.25-8.25h-1.5M4.5 11.25H3m15.536 6.364l-1.06-1.06M6.324 6.324l-1.06-1.06m0 12.728l1.06-1.06M18.686 6.324l1.06-1.06" />
                        </svg>
                        <span>{{ __('seller.settings') }}</span>
                    </a>
                </nav>
            </div>
        </aside>
    </div>
    
    <script>
        // Mobile menu toggle with accessibility and sliding animation
        (function () {
            const openBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-mobile-menu');
            const mobileMenu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');
            const panel = document.getElementById('mobile-menu-panel');
            const isRtl = document.documentElement.dir === 'rtl';
            const hidePanelClass = isRtl ? 'translate-x-full' : '-translate-x-full';

            function openMenu() {
                if (!mobileMenu) return;
                mobileMenu.classList.remove('hidden');
                mobileMenu.setAttribute('aria-hidden', 'false');
                if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
                if (panel) panel.classList.remove(hidePanelClass);
                // trap focus to first focusable element in panel
                setTimeout(() => {
                    const focusable = panel.querySelector('button, a, input, [tabindex]:not([tabindex="-1"])');
                    if (focusable) focusable.focus();
                }, 50);
                // add escape handler
                document.addEventListener('keydown', escHandler);
            }

            function closeMenu() {
                if (!mobileMenu) return;
                if (panel) panel.classList.add(hidePanelClass);
                mobileMenu.setAttribute('aria-hidden', 'true');
                if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
                // remove after transition
                setTimeout(() => mobileMenu.classList.add('hidden'), 250);
                document.removeEventListener('keydown', escHandler);
                // return focus to open button
                if (openBtn) openBtn.focus();
            }

            function escHandler(e) {
                if (e.key === 'Escape' || e.key === 'Esc') closeMenu();
            }

            openBtn?.addEventListener('click', openMenu);
            closeBtn?.addEventListener('click', closeMenu);
            overlay?.addEventListener('click', closeMenu);
        })();

        // Language Dropdown for seller header (mobile)
        (function () {
            const languageDropdowns = document.querySelectorAll('.language-dropdown');
            languageDropdowns.forEach(ld => {
                const toggle = ld.querySelector('.language-dropdown-toggle');
                const menu = ld.querySelector('.language-dropdown-menu');
                if (!toggle || !menu) return;

                function openLangMenu() {
                    // Make invisible but measurable for width/height calculation
                    menu.classList.remove('opacity-0', 'invisible');
                    menu.classList.add('opacity-0', 'visible');
                    menu.style.visibility = 'hidden';
                    menu.style.position = 'fixed';
                    menu.style.zIndex = 99999;

                    // allow the DOM to update so size becomes measurable
                    requestAnimationFrame(() => {
                        const rect = toggle.getBoundingClientRect();
                        const menuRect = menu.getBoundingClientRect();
                        let left = rect.right - menuRect.width; // align right edges
                        let top = rect.bottom + 6; // small gap
                        // keep inside viewport
                        left = Math.max(8, Math.min(left, window.innerWidth - menuRect.width - 8));
                        if (top + menuRect.height > window.innerHeight - 8) {
                            top = Math.max(8, rect.top - menuRect.height - 6);
                        }
                        menu.style.left = left + 'px';
                        menu.style.display = 'block';
                        menu.style.top = top + 'px';
                        menu.style.visibility = 'visible';
                        /* debug ring removed */
                        menu.classList.remove('opacity-0');
                        menu.classList.add('opacity-100');
                        toggle.classList.add('dropdown-open');
                        toggle.setAttribute('aria-expanded', 'true');
                        menu.setAttribute('tabindex', '0');
                        menu.focus();
                        document.addEventListener('click', outsideHandler);
                        document.addEventListener('keydown', escHandler);
                        window.addEventListener('resize', resizeHandler);
                    });
                }
                function closeLangMenu() {
                    menu.classList.add('opacity-0', 'invisible');
                    menu.classList.remove('opacity-100', 'visible');
                    toggle.classList.remove('dropdown-open');
                    toggle.setAttribute('aria-expanded', 'false');
                    menu.setAttribute('tabindex', '-1');
                    document.removeEventListener('click', outsideHandler);
                    document.removeEventListener('keydown', escHandler);
                    window.removeEventListener('resize', resizeHandler);
                    menu.style.display = '';
                    /* debug ring removed */
                }
                function outsideHandler(e) {
                    if (!ld.contains(e.target)) closeLangMenu();
                }
                function escHandler(e) {
                    if (e.key === 'Escape' || e.key === 'Esc') closeLangMenu();
                    if (e.key === 'Tab' && document.activeElement === menu) closeLangMenu();
                }

                function resizeHandler() {
                    // reposition menu on viewport changes
                    const rect = toggle.getBoundingClientRect();
                    menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';
                    menu.style.top = rect.bottom + 'px';
                }

                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (menu.classList.contains('visible')) closeLangMenu(); else openLangMenu();
                });
                // event handlers attached for language dropdown

                // Show loading spinner on language change
                menu.querySelectorAll('.language-option').forEach(option => {
                    option.addEventListener('click', function (evt) {
                        const spinner = document.getElementById('global-loading-spinner');
                        if (spinner) spinner.classList.remove('hidden');
                    });
                });
            });
        })();
    </script>
    <script>
        // Auto-select language based on browser default (only once per user unless they change it manually)
        (function () {
            try {
                if (localStorage.getItem('locale_auto_set') === '1') return;
                const supported = ['en', 'ar', 'fr'];
                const currentLocale = '{{ app()->getLocale() }}';
                const path = window.location.pathname || '';
                if (path.indexOf('/language') !== -1) return; // avoid loops on language path

                let browserLang = (navigator.languages && navigator.languages[0]) || navigator.language || navigator.userLanguage || 'en';
                browserLang = browserLang.toLowerCase();
                const primary = (browserLang || '').split('-')[0];
                let mapped = 'en';
                if (primary === 'ar') mapped = 'ar';
                else if (primary === 'fr') mapped = 'fr';
                else mapped = 'en';

                if (supported.includes(mapped) && mapped !== currentLocale) {
                    localStorage.setItem('locale_auto_set', '1');
                    const switchUrl = '{{ url('/language') }}' + '/' + mapped;
                    window.location.href = switchUrl;
                }
            } catch (e) {
                // do nothing on error
            }
        })();
    </script>
    
    @include('components.whatsapp-fab')
    @stack('scripts')
</body>
</html>
