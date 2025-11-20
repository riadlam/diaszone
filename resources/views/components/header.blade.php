<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-purple-600">DiasZone</span>
                </a>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">Home</a>
                    <a href="{{ route('about') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">About Us</a>
                    <a href="{{ route('contact') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">Contact Us</a>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="hidden lg:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                    <input type="text" 
                           placeholder="Search products..." 
                           class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Language, Currency, Cart, Auth -->
            <div class="flex items-center space-x-4">
                <!-- My Orders Button (shown when encrypted_order_id exists) -->
                <a href="{{ route('dashboard.orders') }}" 
                   id="my-orders-btn" 
                   class="hidden items-center space-x-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>My Orders</span>
                </a>
                
                <!-- Language Dropdown -->
                <div class="relative language-dropdown">
                    <button class="flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm">
                        <span class="text-xl leading-none" style="font-size: 1.25rem;">🇬🇧</span>
                        <span class="text-sm font-semibold text-gray-800">EN</span>
                        <svg class="w-4 h-4 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="language-dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
                        <div class="px-3 py-2 border-b border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Select Language</span>
                        </div>
                        <a href="#" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                            <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center;">🇬🇧</span>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">English</div>
                                <div class="text-xs text-gray-500">English</div>
                            </div>
                            <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded">EN</span>
                        </a>
                        <a href="#" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                            <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center; display: inline-block;">🇩🇿</span>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">العربية</div>
                                <div class="text-xs text-gray-500">Arabic</div>
                            </div>
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">AR</span>
                        </a>
                        <a href="#" class="language-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                            <span class="text-2xl leading-none" style="font-size: 1.5rem; width: 28px; text-align: center;">🇫🇷</span>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Français</div>
                                <div class="text-xs text-gray-500">French</div>
                            </div>
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">FR</span>
                        </a>
                    </div>
                </div>
                
                <!-- Profile Dropdown -->
                <div class="relative profile-dropdown">
                    <button class="profile-dropdown-btn w-10 h-10 bg-purple-600 hover:bg-purple-700 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 shadow-md hover:shadow-lg">
                        @auth
                            <!-- Show user initial if logged in -->
                            <span class="text-white font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name ?? Auth::user()->email, 0, 1)) }}</span>
                        @else
                            <!-- Show icon if not logged in -->
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        @endauth
                    </button>
                    <div class="profile-dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
                        @auth
                            <!-- Logged In: Show User Info and Menu -->
                            <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-purple-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name ?? Auth::user()->email, 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name ?? 'User' }}</p>
                                        <p class="text-xs text-gray-600 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('dashboard.myaccount') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">My Account</span>
                            </a>
                            <a href="{{ route('dashboard.orders') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">My Orders</span>
                            </a>
                            <a href="{{ route('dashboard.notifications') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Notification</span>
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                    <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Logout</span>
                                </button>
                            </form>
                        @else
                            <!-- Not Logged In: Show Login/Signup Buttons -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</span>
                            </div>
                            <div class="px-4 py-3 space-y-2">
                                <a href="{{ route('login') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition-colors duration-200 shadow-md hover:shadow-lg">
                                    Login
                                </a>
                                <a href="{{ route('signup') }}" class="block w-full bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 font-semibold py-2.5 px-4 rounded-lg text-center transition-colors duration-200">
                                    Sign Up
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
                
                <!-- Cart Icon with Dropdown -->
                @if(!request()->routeIs('select-payment'))
                <div class="relative cart-dropdown group">
                    <a href="#" class="relative inline-flex items-center justify-center p-2 text-gray-700 hover:text-purple-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span id="cart-count" class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-tight hidden">0</span>
                    </a>
                    
                    <!-- Cart Dropdown -->
                    <div class="cart-dropdown-menu absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">Shopping Cart</h3>
                        </div>
                        <div id="cart-items" class="max-h-96 overflow-y-auto">
                            <!-- Cart items will be inserted here -->
                            <div class="px-4 py-8 text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">Your cart is empty</p>
                            </div>
                        </div>
                        <div id="cart-footer" class="hidden px-4 py-3 border-t border-gray-100 bg-gray-50">
                            <a href="{{ route('cart') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                                View Cart
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Mobile Search Icon -->
                <button class="lg:hidden p-2 text-gray-700 hover:text-purple-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </nav>
    </div>
</header>

