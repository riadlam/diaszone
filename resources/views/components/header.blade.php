@php $isRtl = app()->getLocale() == 'ar'; @endphp
<header class="bg-white shadow-sm lg:sticky lg:top-0 z-50" style="height: 64px; overflow: visible !important; position: relative; z-index: 99999 !important;">
    <div class="container mx-auto px-4 max-w-full h-full" style="overflow: visible !important;">
        <nav class="flex items-center justify-between h-full" style="overflow: visible; height: 64px; position: relative; z-index: 999999 !important;">
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
                    <a href="{{ route('event.show', 'mobilelegends') }}" class="text-gray-700 hover:text-amber-600 transition-colors font-medium">{{ __('nav.lucky_wheel') }}</a>
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
                <div class="relative w-full" id="navbar-search-wrapper" style="z-index: 10000;">
                    <input type="text" 
                           id="navbar-search-input"
                           placeholder="{{ __('nav.search_placeholder') }}" 
                           class="w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                           autocomplete="off">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button type="button" id="navbar-search-clear" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-purple-600 transition-colors hidden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div id="navbar-search-loading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                        <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <!-- Search Results Dropdown -->
                    <div id="navbar-search-results" class="hidden bg-white border-2 border-purple-200 rounded-xl shadow-2xl overflow-hidden" style="z-index: 10000 !important;">
                        <div id="navbar-search-results-content" class="overflow-y-auto p-4" style="max-height: 400px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Language (mobile compact), Cart, Language/Profile (desktop) -->
            <div class="flex items-center space-x-2 lg:space-x-4" style="position: relative; z-index: 999999 !important;">
                <!-- Language Switcher (mobile compact) -->
                <div class="lg:hidden relative language-dropdown">
                    <button type="button" aria-haspopup="listbox" onclick="this.nextElementSibling.classList.toggle('hidden'); event.stopPropagation();" class="language-dropdown-toggle flex items-center justify-center p-2 text-gray-700 hover:text-purple-600 transition-colors rounded-lg hover:bg-gray-100">
                        @php
                            $currentLocale = app()->getLocale();
                            $localeFlags = ['en' => '🇬🇧', 'ar' => '🇩🇿', 'fr' => '🇫🇷'];
                            $currentFlag = $localeFlags[$currentLocale] ?? '🇬🇧';
                        @endphp
                        <span class="text-xl leading-none" style="font-size: 1.25rem;">{{ $currentFlag }}</span>
                    </button>
                    <div class="language-dropdown-menu fixed right-4 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 hidden" style="top: auto; display: none !important; z-index: 999999 !important; position: fixed;">
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
                
                <!-- Cart Icon (visible on all screens - not clickable) -->
                @if(!request()->routeIs('select-payment'))
                <div class="relative cart-dropdown group">
                    <span class="relative inline-flex items-center justify-center p-2 text-gray-700 cursor-default pointer-events-none" title="Cart">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span id="cart-count" class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 leading-tight hidden">0</span>
                    </span>
                    
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
                            <a href="{{ route('cart') }}" class="block w-full bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg text-center hover:bg-purple-700 transition-colors cursor-pointer">
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
                    <button type="button" id="currency-dropdown-btn" onclick="event.stopPropagation(); var menu = document.getElementById('currency-dropdown-menu'); if (menu) { if (menu.classList.contains('hidden')) { menu.classList.remove('hidden'); menu.style.display = ''; } else { menu.classList.add('hidden'); menu.style.display = 'none'; } }" class="currency-dropdown-btn flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm">
                        <span class="text-sm font-semibold text-gray-800 currency-symbol">USD</span>
                        <svg class="w-4 h-4 text-gray-600 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="currency-dropdown-menu" class="currency-dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 hidden" style="display: none !important; z-index: 999999 !important; position: absolute;">
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
                <div class="relative language-dropdown" style="z-index: 999999 !important; position: relative;">
                    <button type="button" aria-haspopup="listbox" onclick="event.stopPropagation(); var menu = this.nextElementSibling; if (menu) { if (menu.classList.contains('hidden')) { menu.classList.remove('hidden'); menu.style.display = ''; } else { menu.classList.add('hidden'); menu.style.display = 'none'; } }" class="language-dropdown-toggle flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg hover:border-purple-500 hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 shadow-sm">
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
                    <div class="language-dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 hidden" style="display: none !important; z-index: 999999 !important; position: absolute;">
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
                <div class="relative profile-dropdown" id="profile-dropdown-container" style="z-index: 999999 !important; position: relative;">
                    <button type="button" id="profile-dropdown-btn" onclick="event.preventDefault(); event.stopPropagation(); var menu = document.getElementById('profile-dropdown-menu'); if (menu) { if (menu.classList.contains('hidden')) { menu.classList.remove('hidden'); menu.style.display = ''; } else { menu.classList.add('hidden'); menu.style.display = 'none'; } }" class="profile-dropdown-btn w-10 h-10 bg-purple-600 hover:bg-purple-700 rounded-full flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 shadow-md hover:shadow-lg" style="cursor: pointer;">
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
                    <div id="profile-dropdown-menu" class="profile-dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 hidden overflow-hidden" style="display: none !important; z-index: 999999 !important; position: absolute;">
                        @auth
                            <!-- Logged In: Show My Orders -->
                            <a href="{{ route('dashboard.orders') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.my_orders') }}</span>
                            </a>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-red-50 transition-all duration-200 group text-start">
                                        <svg class="w-5 h-5 text-gray-600 group-hover:text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-sm font-semibold text-gray-800 group-hover:text-red-700">{{ __('profile.logout') }}</span>
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- Not Logged In: Show Login and My Orders -->
                            <a href="{{ route('login') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.login') }}</span>
                            </a>
                            <a href="{{ route('dashboard.orders') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gradient-to-r hover:from-purple-50 hover:to-purple-100 transition-all duration-200 group">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">{{ __('profile.my_orders') }}</span>
                            </a>
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
            <div class="relative" id="mobile-search-wrapper" style="z-index: 10000;">
                <input type="text" 
                       id="mobile-search-input"
                       placeholder="{{ __('nav.search_placeholder') }}" 
                       class="w-full px-4 py-2 pl-10 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm"
                       autocomplete="off">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button type="button" id="mobile-search-clear" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-purple-600 transition-colors hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div id="mobile-search-loading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                    <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <!-- Search Results Dropdown -->
                <div id="mobile-search-results" class="hidden bg-white border-2 border-purple-200 rounded-xl shadow-2xl overflow-hidden" style="z-index: 10000 !important;">
                    <div id="mobile-search-results-content" class="overflow-y-auto p-4" style="max-height: 300px;"></div>
                </div>
            </div>
            
            <!-- Menu Items -->
            <div class="space-y-2">
                <a href="{{ route('event.show', 'mobilelegends') }}"
                   class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-amber-50 to-purple-50 text-purple-700 border border-amber-200 rounded-lg transition-colors font-semibold">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-400 text-gray-950" aria-hidden="true">★</span>
                    <span>{{ __('nav.lucky_wheel') }}</span>
                </a>
                <a href="{{ route('home') }}" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                    {{ __('nav.home') }}
                </a>
                <a href="{{ route('dashboard.orders') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>{{ __('nav.my_orders') }}</span>
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
            
            <!-- Language Selector (mobile) -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">{{ __('language.title') }}</label>
                <div class="space-y-2 px-4">
                    @php
                        $currentLocale = app()->getLocale();
                    @endphp
                    <a href="{{ route('language.switch', 'en') }}" class="mobile-language-option w-full flex items-center justify-between px-4 py-3 bg-white border-2 {{ $currentLocale == 'en' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }} rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🇬🇧</span>
                            <div>
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">English</div>
                                <div class="text-xs text-gray-500">{{ __('language.en') }}</div>
                            </div>
                        </div>
                        <span class="text-xs font-medium {{ $currentLocale == 'en' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">EN</span>
                    </a>
                    <a href="{{ route('language.switch', 'ar') }}" class="mobile-language-option w-full flex items-center justify-between px-4 py-3 bg-white border-2 {{ $currentLocale == 'ar' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }} rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🇩🇿</span>
                            <div>
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">العربية</div>
                                <div class="text-xs text-gray-500">{{ __('language.ar') }}</div>
                            </div>
                        </div>
                        <span class="text-xs font-medium {{ $currentLocale == 'ar' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">AR</span>
                    </a>
                    <a href="{{ route('language.switch', 'fr') }}" class="mobile-language-option w-full flex items-center justify-between px-4 py-3 bg-white border-2 {{ $currentLocale == 'fr' ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }} rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">🇫🇷</span>
                            <div>
                                <div class="text-sm font-semibold text-gray-800 group-hover:text-purple-700">Français</div>
                                <div class="text-xs text-gray-500">{{ __('language.fr') }}</div>
                            </div>
                        </div>
                        <span class="text-xs font-medium {{ $currentLocale == 'fr' ? 'text-purple-600 bg-purple-100' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">FR</span>
                    </a>
                </div>
            </div>
            
            @auth
                <div class="border-t border-gray-200 pt-4 mt-4 px-4">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>{{ __('profile.logout') }}</span>
                        </button>
                    </form>
                </div>
            @endauth
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
        
        // Initialize currency from localStorage or default to DZD
        const savedCurrency = localStorage.getItem('diaszone_currency') || 'DZD';
        updateCurrencyDisplay(savedCurrency);
        
        // Handle currency selection
        const currencyMenu = document.getElementById('currency-dropdown-menu');
        if (currencyMenu) {
            currencyMenu.querySelectorAll('.currency-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const currency = option.getAttribute('data-currency');
                    localStorage.setItem('diaszone_currency', currency);
                    updateCurrencyDisplay(currency);
                    updateMobileCurrencyDisplay(currency);
                    currencyMenu.classList.add('hidden');
                    currencyMenu.style.display = 'none';
                    
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
            const currencyButton = document.getElementById('currency-dropdown-btn');
            if (currencyButton) {
                const currencySymbol = currencyButton.querySelector('.currency-symbol');
                if (currencySymbol) {
                    currencySymbol.textContent = currency;
                }
            }
            
            // Update badges
            const currencyMenuEl = document.getElementById('currency-dropdown-menu');
            if (currencyMenuEl) {
                currencyMenuEl.querySelectorAll('.currency-badge').forEach(badge => {
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
        
        // Close all dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Currency dropdown
            const currencyDropdown = document.querySelector('.currency-dropdown');
            const currencyMenuEl = document.getElementById('currency-dropdown-menu');
            if (currencyDropdown && currencyMenuEl && !currencyDropdown.contains(e.target)) {
                currencyMenuEl.classList.add('hidden');
                currencyMenuEl.style.display = 'none';
            }
            
            // Language dropdowns
            document.querySelectorAll('.language-dropdown').forEach(container => {
                const menu = container.querySelector('.language-dropdown-menu');
                if (menu && !container.contains(e.target)) {
                    menu.classList.add('hidden');
                    menu.style.display = 'none';
                }
            });
            
            // Profile dropdown
            const profileDropdownContainer = document.getElementById('profile-dropdown-container');
            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
            if (profileDropdownContainer && profileDropdownMenu && !profileDropdownContainer.contains(e.target)) {
                profileDropdownMenu.classList.add('hidden');
                profileDropdownMenu.style.display = 'none';
            }
        });
        
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
    
    // Navbar AJAX Search Functionality
    (function() {
        // Translations
        const searchTranslations = {
            topSeller: {!! json_encode(__('shop.top_seller')) !!},
            new: {!! json_encode(__('shop.new')) !!},
            giftCards: {!! json_encode(__('shop.gift_cards')) !!},
            noProducts: {!! json_encode(__('shop.no_products')) !!}
        };
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
        
        // Initialize search for an input
        function initSearch(searchInput, searchResults, searchResultsContent, searchClear, searchLoading, isMobile = false) {
            let searchTimeout = null;
            let currentSearchRequest = null;
            
            // Position the dropdown using fixed positioning
            function positionDropdown() {
                const rect = searchInput.getBoundingClientRect();
                searchResults.style.position = 'fixed';
                searchResults.style.top = (rect.bottom + 8) + 'px';
                searchResults.style.left = rect.left + 'px';
                searchResults.style.width = Math.min(rect.width, window.innerWidth - rect.left - 16) + 'px';
                searchResults.style.maxWidth = 'calc(100vw - 32px)';
            }
            
            // Show/hide clear button
            function updateClearButton() {
                if (searchInput.value.trim().length > 0) {
                    searchClear.classList.remove('hidden');
                } else {
                    searchClear.classList.add('hidden');
                    hideResults();
                }
            }
            
            // Clear search
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                updateClearButton();
                hideResults();
                window.location.href = '{{ route("shop") }}';
            });
            
            // Hide results dropdown
            function hideResults() {
                searchResults.classList.add('hidden');
                searchResultsContent.innerHTML = '';
            }
            
            // Show results dropdown
            function showResults() {
                positionDropdown();
                searchResults.classList.remove('hidden');
            }
            
            // Perform AJAX search
            function performSearch(query) {
                if (query.trim().length < 2) {
                    hideResults();
                    return;
                }
                
                // Cancel previous request if any
                if (currentSearchRequest) {
                    currentSearchRequest.abort();
                }
                
                // Show loading
                searchLoading.classList.remove('hidden');
                showResults();
                
                // Create new request
                const url = new URL('{{ route("api.search") }}', window.location.origin);
                url.searchParams.set('q', query);
                url.searchParams.set('limit', '8');
                
                currentSearchRequest = fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    currentSearchRequest = null;
                    searchLoading.classList.add('hidden');
                    
                    if (data.success && data.results.length > 0) {
                        renderResults(data.results);
                    } else {
                        renderNoResults();
                    }
                })
                .catch(error => {
                    currentSearchRequest = null;
                    searchLoading.classList.add('hidden');
                    if (error.name !== 'AbortError') {
                        console.error('Search error:', error);
                        renderNoResults();
                    }
                });
            }
            
            // Render search results
            function renderResults(results) {
                const imageBaseUrl = '{{ asset("") }}';
                let html = '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
                
                results.forEach(function(result) {
                    const imageSrc = result.image_path ? imageBaseUrl + result.image_path : '';
                    const imageTag = imageSrc 
                        ? `<img src="${imageSrc}" alt="${escapeHtml(result.name)}" style="width: 50px; height: 50px; object-fit: contain; border-radius: 8px; background: #ffffff; padding: 6px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); flex-shrink: 0;">`
                        : `<div style="width: 50px; height: 50px; background: linear-gradient(135deg, #9333ea 0%, #ec4899 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.25rem; flex-shrink: 0;">${result.name.charAt(0).toUpperCase()}</div>`;
                    
                    let badges = '';
                    if (result.is_topseller) {
                        badges += '<span style="font-size: 0.7rem; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-weight: 600; text-transform: uppercase; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f;">' + escapeHtml(searchTranslations.topSeller) + '</span>';
                    }
                    if (result.is_newproduct) {
                        badges += '<span style="font-size: 0.7rem; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-weight: 600; text-transform: uppercase; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; margin-left: 0.5rem;">' + escapeHtml(searchTranslations.new) + '</span>';
                    }
                    if (result.is_giftcard) {
                        badges += '<span style="font-size: 0.7rem; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-weight: 600; text-transform: uppercase; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; margin-left: 0.5rem;">' + escapeHtml(searchTranslations.giftCards) + '</span>';
                    }
                    
                    html += `
                        <a href="${result.route}" class="navbar-search-result-card" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 0.75rem; transition: all 0.2s ease; cursor: pointer; text-decoration: none; color: inherit;">
                            ${imageTag}
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; font-size: 0.9rem; color: #111827; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(result.name)}</div>
                                ${badges ? `<div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">${badges}</div>` : ''}
                            </div>
                        </a>
                    `;
                });
                
                html += '</div>';
                searchResultsContent.innerHTML = html;
                showResults();
            }
            
            // Render no results message
            function renderNoResults() {
                searchResultsContent.innerHTML = `
                    <div style="padding: 2rem; text-align: center; color: #6b7280;">
                        <p>${escapeHtml(searchTranslations.noProducts)}</p>
                    </div>
                `;
                showResults();
            }
            
            // Input event with debouncing
            searchInput.addEventListener('input', function() {
                updateClearButton();
                const query = this.value.trim();
                
                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                // Set new timeout for debouncing (300ms)
                searchTimeout = setTimeout(function() {
                    performSearch(query);
                }, 300);
            });
            
            // Focus event
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2) {
                    performSearch(this.value.trim());
                }
            });
            
            // Hide dropdown on scroll
            window.addEventListener('scroll', function() {
                if (!searchResults.classList.contains('hidden')) {
                    hideResults();
                }
            }, { passive: true });
            
            // Reposition on resize
            window.addEventListener('resize', function() {
                if (!searchResults.classList.contains('hidden')) {
                    positionDropdown();
                }
            }, { passive: true });
            
            // Hide results when clicking outside
            document.addEventListener('click', function(event) {
                const wrapper = searchInput.closest('[id$="-search-wrapper"]');
                if (!wrapper.contains(event.target) && !searchResults.contains(event.target)) {
                    hideResults();
                }
            });
            
            // Initialize clear button state
            updateClearButton();
        }
        
        // Initialize desktop search
        const navbarSearchInput = document.getElementById('navbar-search-input');
        const navbarSearchResults = document.getElementById('navbar-search-results');
        const navbarSearchResultsContent = document.getElementById('navbar-search-results-content');
        const navbarSearchClear = document.getElementById('navbar-search-clear');
        const navbarSearchLoading = document.getElementById('navbar-search-loading');
        
        if (navbarSearchInput) {
            initSearch(navbarSearchInput, navbarSearchResults, navbarSearchResultsContent, navbarSearchClear, navbarSearchLoading, false);
        }
        
        // Initialize mobile search
        const mobileSearchInput = document.getElementById('mobile-search-input');
        const mobileSearchResults = document.getElementById('mobile-search-results');
        const mobileSearchResultsContent = document.getElementById('mobile-search-results-content');
        const mobileSearchClear = document.getElementById('mobile-search-clear');
        const mobileSearchLoading = document.getElementById('mobile-search-loading');
        
        if (mobileSearchInput) {
            initSearch(mobileSearchInput, mobileSearchResults, mobileSearchResultsContent, mobileSearchClear, mobileSearchLoading, true);
        }
        
        // Add hover styles for result cards
        const style = document.createElement('style');
        style.textContent = `
            .navbar-search-result-card:hover {
                background-color: #f3f4f6;
                transform: translateX(4px);
            }
        `;
        document.head.appendChild(style);
    })();
</script>

