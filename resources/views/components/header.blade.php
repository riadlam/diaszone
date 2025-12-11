@php $isRtl = app()->getLocale() == 'ar'; @endphp
<header class="bg-white shadow-sm lg:sticky lg:top-0 z-50">
    <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-16">
            <!-- Left: Mobile Menu Button (mobile only) -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 text-gray-700 hover:text-purple-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Desktop: Logo + Menu Items -->
            <div class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-purple-600">DiasZone</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">{{ __('nav.home') }}</a>
                    <a href="{{ route('about') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">{{ __('nav.about') }}</a>
                    <a href="{{ route('contact') }}" class="text-gray-700 hover:text-purple-600 transition-colors font-medium">{{ __('nav.contact') }}</a>
                </div>
            </div>
            
            <!-- Center: Logo (mobile only) -->
            <div class="lg:hidden flex-1 flex justify-center">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <span class="text-xl font-bold text-purple-600">DiasZone</span>
                </a>
            </div>
            
            <!-- Search Bar (desktop only) -->
            <div class="hidden lg:flex flex-1 max-w-md mx-8">
                <div class="relative w-full">
                          <input type="text" 
                           placeholder="{{ __('nav.search_placeholder') }}" 
                              class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm search-input">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none header-left-icon">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Cart (mobile) / Full menu (desktop) -->
            <div class="flex items-center space-x-2 lg:space-x-4">
                <!-- Cart Icon (visible on all screens) -->
                @if(!request()->routeIs('select-payment'))
                <div class="relative cart-dropdown group">
                    <a href="{{ route('cart') }}" class="relative inline-flex items-center justify-center p-2 text-gray-700 hover:text-purple-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span id="cart-count" class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-tight hidden">0</span>
                    </a>
                    
                    <!-- Cart Dropdown -->
                    <div class="cart-dropdown-menu absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('cart_dropdown.title') }}</h3>
                        </div>
                        <div id="cart-items" class="max-h-96 overflow-y-auto">
                            <!-- Cart items will be inserted here -->
                            <div class="px-4 py-8 text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">{{ __('cart_dropdown.empty') }}</p>
                            </div>
                        </div>
                        <div id="cart-footer" class="hidden px-4 py-3 border-t border-gray-100 bg-gray-50">
                            <a href="{{ route('cart') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition-colors">
                                {{ __('cart_dropdown.view_cart') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Desktop: Language, Profile (desktop only) -->
                <div class="hidden lg:flex items-center space-x-4">
                <!-- My Orders Button (shown when encrypted_order_id exists) -->
                <a href="{{ route('dashboard.orders') }}" 
                   id="my-orders-btn" 
                   class="hidden items-center space-x-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>{{ __('nav.my_orders') }}</span>
                </a>
                
                <!-- Currency Dropdown -->
                <div class="relative currency-dropdown">
                    <button id="currency-dropdown-btn" class="currency-dropdown-btn flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm">
                        <span class="text-sm font-semibold text-gray-800 currency-symbol">USD</span>
                        <svg class="w-4 h-4 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="currency-dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
                        <div class="px-3 py-2 border-b border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('currency.title') }}</span>
                        </div>
                        <a href="#" data-currency="USD" class="currency-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">US Dollar</div>
                                <div class="text-xs text-gray-500">USD</div>
                            </div>
                            <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded currency-badge">USD</span>
                        </a>
                        <a href="#" data-currency="DZD" class="currency-option flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Algerian Dinar</div>
                                <div class="text-xs text-gray-500">DZD</div>
                            </div>
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded currency-badge">DZD</span>
                        </a>
                    </div>
                </div>
                
                <!-- Language Dropdown -->
                <div class="relative language-dropdown">
                    <button class="flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm">
                        @php
                            $currentLocale = app()->getLocale();
                            $localeData = [
                                'en' => ['flag' => '🇬🇧', 'code' => 'EN'],
                                'ar' => ['flag' => '🇩🇿', 'code' => 'AR'],
                                'fr' => ['flag' => '🇫🇷', 'code' => 'FR']
                            ];
                            $current = $localeData[$currentLocale] ?? $localeData['en'];
                        @endphp
                        <span class="text-xl leading-none" style="font-size: 1.25rem;">{{ $current['flag'] }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $current['code'] }}</span>
                        <svg class="w-4 h-4 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="language-dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 opacity-0 invisible transition-all duration-300 z-50 overflow-hidden">
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
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.my_account') }}</span>
                            </a>
                            <a href="{{ route('dashboard.orders') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.my_orders') }}</span>
                            </a>
                            <a href="{{ route('dashboard.notifications') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.notifications') }}</span>
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                    <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.logout') }}</span>
                                </button>
                            </form>
                        @else
                            <!-- Not Logged In: Show Login/Signup Buttons -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('profile.account') }}</span>
                            </div>
                            <div class="px-4 py-3 space-y-2">
                                <a href="{{ route('login') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition-colors duration-200 shadow-md hover:shadow-lg">
                                    {{ __('profile.login') }}
                                </a>
                                <a href="{{ route('signup') }}" class="block w-full bg-white border-2 border-purple-600 text-purple-600 hover:bg-purple-50 font-semibold py-2.5 px-4 rounded-lg text-center transition-colors duration-200">
                                    {{ __('profile.signup') }}
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Mobile Side Drawer -->
<div id="mobile-drawer" class="fixed inset-y-0 z-50 w-80 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:hidden {{ $isRtl ? 'translate-x-full right-0' : '-translate-x-full left-0' }}" style="{{ $isRtl ? 'transform: translateX(100%);' : 'transform: translateX(-100%);' }}">
    <div class="flex flex-col h-full">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-purple-600">{{ __('nav.menu') }}</h2>
            <button id="close-drawer-btn" class="p-2 text-gray-700 hover:text-purple-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Drawer Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Search Bar -->
            <div class="relative">
                <input type="text" 
                       placeholder="{{ __('nav.search_placeholder') }}" 
                       class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none header-left-icon">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Menu Items -->
            <div class="space-y-2">
                <a href="{{ route('home') }}" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                    {{ __('nav.home') }}
                </a>
                <a href="{{ route('about') }}" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                    {{ __('nav.about') }}
                </a>
                <a href="{{ route('contact') }}" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                    {{ __('nav.contact') }}
                </a>
            </div>
            
            <!-- Currency Selector (mobile) -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">{{ __('currency.title') }}</label>
                <div class="space-y-2 px-4">
                    <button data-currency="USD" class="mobile-currency-option w-full flex items-center justify-between px-4 py-3 bg-white border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">US Dollar</div>
                                <div class="text-xs text-gray-500">USD</div>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded mobile-currency-badge">USD</span>
                    </button>
                    <button data-currency="DZD" class="mobile-currency-option w-full flex items-center justify-between px-4 py-3 bg-white border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Algerian Dinar</div>
                                <div class="text-xs text-gray-500">DZD</div>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded mobile-currency-badge">DZD</span>
                    </button>
                </div>
            </div>
            
            <!-- My Orders Button (mobile) -->
            <a href="{{ route('dashboard.orders') }}" 
               id="mobile-my-orders-btn" 
               class="hidden block w-full items-center space-x-2 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-md mt-4">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span>{{ __('seller.my_orders') }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Drawer Overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" style="display: none;"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile drawer toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeDrawerBtn = document.getElementById('close-drawer-btn');
        const mobileDrawer = document.getElementById('mobile-drawer');
        const isRtl = document.documentElement.dir === 'rtl';
        const hideDrawerClass = isRtl ? 'translate-x-full' : '-translate-x-full';
        const drawerOverlay = document.getElementById('drawer-overlay');
        
        // Ensure drawer starts closed (force with inline style)
        if (mobileDrawer) {
            mobileDrawer.style.transform = isRtl ? 'translateX(100%)' : 'translateX(-100%)';
            mobileDrawer.classList.add(hideDrawerClass);
        }
        if (drawerOverlay) {
            drawerOverlay.style.display = 'none';
            drawerOverlay.classList.add('hidden');
        }
        
        function openDrawer() {
            if (mobileDrawer) {
                mobileDrawer.style.transform = 'translateX(0)';
                mobileDrawer.classList.remove(hideDrawerClass);
            }
            if (drawerOverlay) {
                drawerOverlay.style.display = 'block';
                drawerOverlay.classList.remove('hidden');
            }
            // Prevent body scroll when drawer is open
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            // Store scroll position
            const scrollY = window.scrollY;
            document.body.style.top = `-${scrollY}px`;
            document.body.dataset.scrollY = scrollY;
        }
        
        function closeDrawer() {
            if (mobileDrawer) {
                mobileDrawer.style.transform = isRtl ? 'translateX(100%)' : 'translateX(-100%)';
                mobileDrawer.classList.add(hideDrawerClass);
            }
            if (drawerOverlay) {
                drawerOverlay.style.display = 'none';
                drawerOverlay.classList.add('hidden');
            }
            // Restore body scroll
            const scrollY = document.body.dataset.scrollY || 0;
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.top = '';
            window.scrollTo(0, parseInt(scrollY) || 0);
            delete document.body.dataset.scrollY;
        }
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openDrawer();
            });
        }
        
        if (closeDrawerBtn) {
            closeDrawerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeDrawer();
            });
        }
        
        if (drawerOverlay) {
            drawerOverlay.addEventListener('click', function(e) {
                e.preventDefault();
                closeDrawer();
            });
        }
        
        // Sync My Orders button visibility between desktop and mobile drawer
        function updateMyOrdersButton() {
            const encryptedOrderIds = localStorage.getItem('diaszone_encrypted_order_ids');
            const hasOrders = encryptedOrderIds && encryptedOrderIds !== '[]' && encryptedOrderIds !== '';
            
            const desktopBtn = document.getElementById('my-orders-btn');
            const mobileDrawerBtn = document.getElementById('mobile-my-orders-btn');
            
            if (desktopBtn) {
                if (hasOrders) {
                    desktopBtn.classList.remove('hidden');
                    desktopBtn.classList.add('flex');
                } else {
                    desktopBtn.classList.add('hidden');
                    desktopBtn.classList.remove('flex');
                }
            }
            
            if (mobileDrawerBtn) {
                if (hasOrders) {
                    mobileDrawerBtn.classList.remove('hidden');
                    mobileDrawerBtn.classList.add('block');
                } else {
                    mobileDrawerBtn.classList.add('hidden');
                    mobileDrawerBtn.classList.remove('block');
                }
            }
        }
        
        // Check on page load and when localStorage changes
        updateMyOrdersButton();
        window.addEventListener('storage', updateMyOrdersButton);
        
        // Also check periodically (for same-tab updates)
        setInterval(updateMyOrdersButton, 1000);
        
        // Currency Dropdown
        const currencyDropdown = document.querySelector('.currency-dropdown');
        const currencyButton = document.getElementById('currency-dropdown-btn');
        const currencyMenu = document.querySelector('.currency-dropdown-menu');
        
        // Get other dropdown elements
        const languageDropdown = document.querySelector('.language-dropdown');
        const languageButton = languageDropdown ? languageDropdown.querySelector('button') : null;
        const languageMenu = languageDropdown ? languageDropdown.querySelector('.language-dropdown-menu') : null;
        const profileDropdown = document.querySelector('.profile-dropdown');
        const profileMenu = profileDropdown ? profileDropdown.querySelector('.profile-dropdown-menu') : null;
        
        // Initialize currency from localStorage or default to DZD
        const savedCurrency = localStorage.getItem('diaszone_currency') || 'DZD';
        updateCurrencyDisplay(savedCurrency);
        
        // Currency dropdown toggle
        if (currencyButton && currencyMenu) {
            currencyButton.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = currencyMenu.classList.contains('opacity-100');
                
                // Close other dropdowns
                if (languageMenu) {
                    languageMenu.classList.remove('opacity-100', 'visible');
                    languageMenu.classList.add('opacity-0', 'invisible');
                }
                if (languageButton) {
                    languageButton.classList.remove('dropdown-open');
                }
                if (profileMenu) {
                    profileMenu.classList.remove('opacity-100', 'visible');
                    profileMenu.classList.add('opacity-0', 'invisible');
                }
                
                // Toggle currency dropdown
                if (isOpen) {
                    currencyMenu.classList.remove('opacity-100', 'visible');
                    currencyMenu.classList.add('opacity-0', 'invisible');
                } else {
                    currencyMenu.classList.remove('opacity-0', 'invisible');
                    currencyMenu.classList.add('opacity-100', 'visible');
                }
            });
        }
        
        // Handle currency selection
        if (currencyMenu) {
            currencyMenu.querySelectorAll('.currency-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const currency = option.getAttribute('data-currency');
                    localStorage.setItem('diaszone_currency', currency);
                    updateCurrencyDisplay(currency);
                    updateMobileCurrencyDisplay(currency); // Also update mobile display
                    currencyMenu.classList.remove('opacity-100', 'visible');
                    currencyMenu.classList.add('opacity-0', 'invisible');
                    
                    // Trigger currency change event
                    window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency } }));
                    
                    // Reload prices on the page
                    if (typeof updatePricesOnPage === 'function') {
                        updatePricesOnPage();
                    }
                });
            });
        }
        
        // Update currency display
        function updateCurrencyDisplay(currency) {
            if (currencyButton) {
                const currencySymbol = currencyButton.querySelector('.currency-symbol');
                if (currencySymbol) {
                    currencySymbol.textContent = currency;
                }
            }
            
            // Update badges
            if (currencyMenu) {
                currencyMenu.querySelectorAll('.currency-badge').forEach(badge => {
                    const option = badge.closest('.currency-option');
                    if (option && option.getAttribute('data-currency') === currency) {
                        badge.classList.remove('text-gray-600', 'bg-gray-100');
                        badge.classList.add('text-purple-600', 'bg-purple-100');
                    } else {
                        badge.classList.remove('text-purple-600', 'bg-purple-100');
                        badge.classList.add('text-gray-600', 'bg-gray-100');
                    }
                });
            }
        }
        
        // Close currency dropdown when clicking outside (if not already handled by app.js)
        if (currencyDropdown) {
            document.addEventListener('click', (e) => {
                if (!currencyDropdown.contains(e.target)) {
                    if (currencyMenu) {
                        currencyMenu.classList.remove('opacity-100', 'visible');
                        currencyMenu.classList.add('opacity-0', 'invisible');
                    }
                }
            });
        }
        
        // Mobile currency selector
        const mobileCurrencyOptions = document.querySelectorAll('.mobile-currency-option');
        
        function updateMobileCurrencyDisplay(currency) {
            mobileCurrencyOptions.forEach(option => {
                const currencyValue = option.getAttribute('data-currency');
                const badge = option.querySelector('.mobile-currency-badge');
                
                if (currencyValue === currency) {
                    option.classList.remove('border-gray-200');
                    option.classList.add('border-purple-500', 'bg-purple-50');
                    if (badge) {
                        badge.classList.remove('text-gray-600', 'bg-gray-100');
                        badge.classList.add('text-purple-600', 'bg-purple-100');
                    }
                } else {
                    option.classList.remove('border-purple-500', 'bg-purple-50');
                    option.classList.add('border-gray-200');
                    if (badge) {
                        badge.classList.remove('text-purple-600', 'bg-purple-100');
                        badge.classList.add('text-gray-600', 'bg-gray-100');
                    }
                }
            });
        }
        
        // Initialize mobile currency display
        const savedCurrencyMobile = localStorage.getItem('diaszone_currency') || 'DZD';
        updateMobileCurrencyDisplay(savedCurrencyMobile);
        
        mobileCurrencyOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const currency = this.getAttribute('data-currency');
                localStorage.setItem('diaszone_currency', currency);
                updateMobileCurrencyDisplay(currency);
                updateCurrencyDisplay(currency); // Also update desktop display
                
                // Trigger currency change event
                window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency } }));
                
                // Reload prices on the page
                if (typeof updatePricesOnPage === 'function') {
                    updatePricesOnPage();
                }
                
                // Close drawer after selection (optional)
                // closeDrawer();
            });
        });
    });
</script>

