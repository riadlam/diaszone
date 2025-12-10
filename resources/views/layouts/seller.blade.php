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
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-900 text-white">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="seller-sidebar w-64 flex-shrink-0 hidden lg:block">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-blue-800/30">
                    <a href="{{ route('seller.dashboard') }}" class="flex items-center space-x-3">
                        @php $me = Auth::guard('seller')->user(); @endphp
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center overflow-hidden">
                            @if(!empty($me->store_logo_thumb ?? $me->store_logo))
                                <img src="/storage_public/{{ $me->store_logo_thumb ?? $me->store_logo }}" alt="{{ $me->store_name ?? $me->name }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-white font-bold text-xl">DZ</span>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-xl">DiasZone</h1>
                            <p class="text-blue-300 text-xs">Seller Panel</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="{{ route('seller.dashboard') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('seller.packs') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.packs*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>Packs & Pricing</span>
                    </a>
                    
                    <a href="{{ route('seller.orders') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.orders*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>Orders</span>
                    </a>
                    
                    <a href="{{ route('seller.wallet') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.wallet*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Wallet</span>
                    </a>
                    
                    <a href="{{ route('seller.direct-topup') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.direct-topup*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Direct Top-Up</span>
                    </a>
                    
                    <a href="{{ route('seller.statistics') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.statistics*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Statistics</span>
                    </a>

                    <a href="{{ route('seller.settings') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.settings*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 4.5v1.5m0 12V21m8.25-8.25h-1.5M4.5 11.25H3m15.536 6.364l-1.06-1.06M6.324 6.324l-1.06-1.06m0 12.728l1.06-1.06M18.686 6.324l1.06-1.06" />
                        </svg>
                        <span>Settings</span>
                    </a>
                    
                    <div class="border-t border-blue-800/30 my-4"></div>
                    
                    <a href="{{ route('seller.store.home', ['username' => Auth::guard('seller')->user()->username]) }}" target="_blank" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span>View My Store</span>
                    </a>
                    
                    <a href="{{ route('seller.profile') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300 hover:text-white {{ request()->routeIs('seller.profile*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Profile</span>
                    </a>
                </nav>
                
                <!-- Wallet Balance -->
                <div class="p-4 border-t border-blue-800/30">
                    <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg p-4">
                        <p class="text-blue-100 text-sm">Wallet Balance</p>
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
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Menu Button -->
        <div class="lg:hidden fixed top-4 left-4 z-50">
            <button id="mobile-menu-btn" class="p-2 bg-slate-800 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-slate-800 shadow-lg p-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">@yield('header', 'Dashboard')</h2>
                        <p class="text-gray-400 text-sm">Welcome back, {{ Auth::guard('seller')->user()->name }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-400 mr-2">{{ Auth::guard('seller')->user()->store_name ?? Auth::guard('seller')->user()->name }}</span>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center overflow-hidden">
                                @php $me2 = Auth::guard('seller')->user(); @endphp
                                @if(!empty($me2->store_logo_thumb ?? $me2->store_logo))
                                    <img src="/storage_public/{{ $me2->store_logo_thumb ?? $me2->store_logo }}" alt="{{ $me2->store_name ?? $me2->name }}" class="w-full h-full object-cover rounded-full" />
                                @else
                                    <span class="text-white font-bold">{{ substr($me2->name, 0, 1) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
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
    <div id="mobile-menu" class="lg:hidden fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" id="mobile-menu-overlay"></div>
        <aside class="seller-sidebar w-64 h-full relative z-10">
            <!-- Same content as desktop sidebar -->
            <div class="flex flex-col h-full">
                <div class="p-6 border-b border-blue-800/30 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">DZ</span>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-xl">DiasZone</h1>
                            <p class="text-blue-300 text-xs">Seller Panel</p>
                        </div>
                    </div>
                    <button id="close-mobile-menu" class="text-white">
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
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('seller.packs') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span>Packs & Pricing</span>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>Orders</span>
                    </a>
                    <a href="{{ route('seller.wallet') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Wallet</span>
                    </a>
                    <a href="{{ route('seller.direct-topup') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Direct Top-Up</span>
                    </a>
                    <a href="{{ route('seller.statistics') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Statistics</span>
                    </a>
                    <a href="{{ route('seller.settings') }}" class="seller-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 4.5v1.5m0 12V21m8.25-8.25h-1.5M4.5 11.25H3m15.536 6.364l-1.06-1.06M6.324 6.324l-1.06-1.06m0 12.728l1.06-1.06M18.686 6.324l1.06-1.06" />
                        </svg>
                        <span>Settings</span>
                    </a>
                </nav>
            </div>
        </aside>
    </div>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.remove('hidden');
        });
        document.getElementById('close-mobile-menu')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.add('hidden');
        });
        document.getElementById('mobile-menu-overlay')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.add('hidden');
        });
    </script>
    
    @include('components.whatsapp-fab')
    @stack('scripts')
</body>
</html>
