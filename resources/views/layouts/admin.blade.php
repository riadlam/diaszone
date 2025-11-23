<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="admin-sidebar w-64 flex-shrink-0 hidden lg:block">
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
                    
                    <a href="{{ route('admin.settings') }}" 
                       class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-semibold">Settings</span>
                    </a>
                </nav>
                
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
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar (Mobile) -->
            <header class="bg-white shadow-sm border-b border-gray-200 lg:hidden">
                <div class="flex items-center justify-between p-4">
                    <button id="mobile-menu-toggle" class="p-2 text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h1 class="text-xl font-bold text-purple-600">DiasZone Admin</h1>
                    <div class="w-10"></div>
                </div>
            </header>
            
            <!-- Mobile Sidebar -->
            <aside id="mobile-sidebar" class="admin-sidebar fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full transition-transform duration-300 lg:hidden">
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
                        <a href="{{ route('admin.settings') }}" 
                           class="admin-sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg text-white {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-semibold">Settings</span>
                        </a>
                    </nav>
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
            
            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 min-h-screen">
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
                mobileSidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeMobileMenu() {
                mobileSidebar.classList.add('-translate-x-full');
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
        });
    </script>
    
    @stack('scripts')
</body>
</html>

