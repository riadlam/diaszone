<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Admin Dashboard - DiasZone')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    
    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <style>
        .admin-sidebar {
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
        }
        .admin-sidebar-item {
            transition: all 0.3s ease;
        }
        .admin-sidebar-item:hover {
            background: rgba(147, 51, 234, 0.2);
            transform: translateX(5px);
        }
        .admin-sidebar-item.active {
            background: linear-gradient(90deg, rgba(147, 51, 234, 0.3) 0%, rgba(147, 51, 234, 0.1) 100%);
            border-left: 4px solid #9333ea;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .admin-sidebar-item:hover {
                transform: none;
            }
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Better table responsiveness */
        @media (max-width: 640px) {
            table {
                font-size: 0.875rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100">
    @php $isRtlAdmin = app()->getLocale() == 'ar'; @endphp
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="admin-sidebar w-64 flex-shrink-0 hidden lg:block sticky top-0 h-screen">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="p-6 border-b border-purple-800/30">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">DZ</span>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-xl">DiasZone</h1>
                            <p class="text-purple-300 text-xs">Admin Panel</p>
                        </div>
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="font-semibold">Dashboard</span>
                    </a>
                    
                    <a href="{{ route('admin.orders') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="font-semibold">Orders</span>
                    </a>
                    
                    <a href="{{ route('admin.users') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="font-semibold">Users</span>
                    </a>
                    
                    <a href="{{ route('admin.sellers.index') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-semibold">Sellers</span>
                    </a>
                    <a href="{{ route('admin.topups.index') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.topups.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="font-semibold">Top-ups</span>
                    </a>
                    
                    <a href="{{ route('admin.game-content.index') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.game-content.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-semibold">Game Content</span>
                    </a>
                    
                    <a href="{{ route('admin.settings') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-semibold">Settings</span>
                    </a>
                </nav>
                
                <!-- Language Switcher -->
                <div class="p-4 border-b border-purple-800/30">
                    @include('components.language-dropdown')
                </div>
                
                <!-- User Info & Logout -->
                <div class="p-4 border-t border-purple-800/30">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white font-semibold text-sm truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-purple-300 text-xs truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('home') }}" class="flex-1 bg-purple-600/30 hover:bg-purple-600/50 text-white text-sm font-semibold py-2 px-3 rounded-lg transition-colors text-center">
                            View Site
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-red-600/30 hover:bg-red-600/50 text-white text-sm font-semibold py-2 px-3 rounded-lg transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar (Mobile) -->
            <header class="bg-white shadow-sm border-b border-gray-200 lg:hidden">
                <div class="flex items-center justify-between p-3 px-4">
                    <button id="mobile-menu-toggle" class="flex-shrink-0 p-2 text-gray-600 hover:text-purple-600 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h1 class="text-lg font-bold text-purple-600 flex-1 text-center px-2 truncate">DiasZone Admin</h1>
                    <div class="flex-shrink-0 relative language-dropdown">
                        <button type="button" aria-haspopup="listbox" aria-expanded="false" class="language-dropdown-toggle flex items-center justify-center p-2 text-gray-600 hover:text-purple-600 transition-colors rounded-lg hover:bg-gray-100">
                            @php
                                $currentLocale = app()->getLocale();
                                $localeFlags = ['en' => '🇬🇧', 'ar' => '🇩🇿', 'fr' => '🇫🇷'];
                                $currentFlag = $localeFlags[$currentLocale] ?? '🇬🇧';
                            @endphp
                            <span class="text-xl leading-none" style="font-size: 1.25rem;">{{ $currentFlag }}</span>
                        </button>
                        <div class="language-dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-[9999] overflow-hidden">
                            <div class="px-3 py-2 border-b border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('language.title') }}</span>
                            </div>
                            <a href="{{ route('language.switch', 'en') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group {{ app()->getLocale() == 'en' ? 'bg-purple-50' : '' }}">
                                <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center;">🇬🇧</span>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">English</div>
                                    <div class="text-xs text-gray-500">{{ __('language.en') }}</div>
                                </div>
                                <span class="text-xs font-medium {{ app()->getLocale() == 'en' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">EN</span>
                            </a>
                            <a href="{{ route('language.switch', 'ar') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group {{ app()->getLocale() == 'ar' ? 'bg-purple-50' : '' }}">
                                <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center; display: inline-block;">🇩🇿</span>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">العربية</div>
                                    <div class="text-xs text-gray-500">{{ __('language.ar') }}</div>
                                </div>
                                <span class="text-xs font-medium {{ app()->getLocale() == 'ar' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">AR</span>
                            </a>
                            <a href="{{ route('language.switch', 'fr') }}" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group {{ app()->getLocale() == 'fr' ? 'bg-purple-50' : '' }}">
                                <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center;">🇫🇷</span>
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Français</div>
                                    <div class="text-xs text-gray-500">{{ __('language.fr') }}</div>
                                </div>
                                <span class="text-xs font-medium {{ app()->getLocale() == 'fr' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">FR</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Mobile Sidebar -->
            <aside id="mobile-sidebar" class="admin-sidebar fixed inset-y-0 z-50 w-64 transform transition-transform duration-300 lg:hidden {{ $isRtlAdmin ? 'translate-x-full right-0' : '-translate-x-full left-0' }}">
                <div class="flex flex-col h-full">
                    <div class="p-4 border-b border-purple-800/30 flex items-center justify-between">
                        <h1 class="text-white font-bold text-xl">DiasZone</h1>
                        <button id="mobile-menu-close" class="p-2 text-white hover:text-purple-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="font-semibold">Dashboard</span>
                        </a>
                        <a href="{{ route('admin.orders') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="font-semibold">Orders</span>
                        </a>
                        <a href="{{ route('admin.users') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="font-semibold">Users</span>
                        </a>
                        <a href="{{ route('admin.sellers.index') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="font-semibold">Sellers</span>
                        </a>
                        <a href="{{ route('admin.settings') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-semibold">Settings</span>
                        </a>
                    </nav>
                    <!-- Language Switcher (Mobile) -->
                    <div class="p-4 border-b border-purple-800/30">
                        @include('components.language-dropdown')
                    </div>
                    
                    <div class="p-4 border-t border-purple-800/30">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-semibold text-sm truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-purple-300 text-xs truncate">{{ Auth::user()->email ?? '' }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('home') }}" class="flex-1 bg-purple-600/30 hover:bg-purple-600/50 text-white text-sm font-semibold py-2 px-3 rounded-lg transition-colors text-center">
                                View Site
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-red-600/30 hover:bg-red-600/50 text-white text-sm font-semibold py-2 px-3 rounded-lg transition-colors">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Mobile Overlay -->
            <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>
            
            <!-- Content Area (scrollable) -->
            <main class="flex-1 bg-gray-50 overflow-y-auto max-h-screen">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenuClose = document.getElementById('mobile-menu-close');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const mobileOverlay = document.getElementById('mobile-overlay');
            
            function openMobileMenu() {
                const isRtl = document.documentElement.dir === 'rtl';
                const hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
                mobileSidebar.classList.remove(hideClass);
                mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeMobileMenu() {
                const isRtl = document.documentElement.dir === 'rtl';
                const hideClass = isRtl ? 'translate-x-full' : '-translate-x-full';
                mobileSidebar.classList.add(hideClass);
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', openMobileMenu);
            }
            
            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', closeMobileMenu);
            }
            
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', closeMobileMenu);
            }
            
            // Language Dropdown
            const languageDropdowns = document.querySelectorAll('.language-dropdown');
            languageDropdowns.forEach(ld => {
                const toggle = ld.querySelector('.language-dropdown-toggle');
                const menu = ld.querySelector('.language-dropdown-menu');
                if (!toggle || !menu) return;

                function openLangMenu() {
                    // Ensure menu is properly displayed
                    menu.style.display = '';
                    menu.style.visibility = '';
                    menu.classList.remove('opacity-0', 'invisible');
                    menu.classList.add('opacity-100', 'visible');
                    toggle.classList.add('dropdown-open');
                    toggle.setAttribute('aria-expanded', 'true');
                    document.addEventListener('click', outsideHandler);
                    document.addEventListener('keydown', escHandler);
                }
                
                function closeLangMenu() {
                    menu.classList.add('opacity-0', 'invisible');
                    menu.classList.remove('opacity-100', 'visible');
                    toggle.classList.remove('dropdown-open');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.removeEventListener('click', outsideHandler);
                    document.removeEventListener('keydown', escHandler);
                }
                
                function outsideHandler(e) {
                    if (!ld.contains(e.target)) closeLangMenu();
                }
                
                function escHandler(e) {
                    if (e.key === 'Escape' || e.key === 'Esc') closeLangMenu();
                }

                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (menu.classList.contains('visible')) {
                        closeLangMenu();
                    } else {
                        openLangMenu();
                    }
                });
            });
        });
    </script>
    
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <!-- DataTables JS: load only if jQuery is present to avoid runtime errors -->
    <script>
        if (typeof window.jQuery !== 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
            document.head.appendChild(s);
        }
    </script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    @include('components.whatsapp-fab')
    @stack('scripts')
</body>
</html>
