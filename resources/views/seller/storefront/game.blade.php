<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $gameName }} - {{ $seller->store_name ?? $seller->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        // Early fallback: define no-op global helpers so other scripts can safely call them
        // (they will be overwritten later by the real implementations in app.js)
        if (typeof window !== 'undefined') {
            if (typeof window.showValidationError !== 'function') {
                window.showValidationError = function (message) {
                    // minimal fallback: console and tiny inline UI
                    console.warn('showValidationError (fallback):', message);
                    try {
                        const existing = document.getElementById('nickname-validation-error');
                        if (existing) existing.remove();
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'nickname-validation-error';
                        errorDiv.textContent = message || 'Validation failed';
                        (document.getElementById('checkout-form') || document.body).appendChild(errorDiv);
                    } catch (e) { /* ignore */ }
                };
            }
            if (typeof window.showNicknameSuccess !== 'function') {
                window.showNicknameSuccess = function (nickname, cb) { console.info('showNicknameSuccess (fallback):', nickname); if (cb) cb(); };
            }
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .pack-card {
            transition: all 0.2s ease;
        }
        .pack-card:hover {
            transform: scale(1.02);
            border-color: #3b82f6;
        }
        .pack-card.selected {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.15);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        .pack-card.selected .pack-check {
            display: flex !important;
            background: #3b82f6;
            border-color: #3b82f6;
        }
        
        /* Cookie indicator */
        .cookie-loaded {
            animation: cookiePulse 0.5s ease;
        }
        
        @keyframes cookiePulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5); }
            70% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        /* Force-hide desktop offers on mobile in case preview/emulator quirks occur */
        @media screen and (max-width: 1023px) {
            .desktop-packs { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen">
    <!-- Header -->
    <header class="bg-slate-800 border-b border-slate-700">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('seller.store.home', ['username' => $seller->username]) }}" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">{{ substr($seller->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <h1 class="text-white font-bold">{{ $seller->store_name ?? $seller->name }}</h1>
                        <p class="text-gray-400 text-sm">{{ $gameName }} Top-Up</p>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Mobile-only store header: banner and circular logo (Facebook-like) -->
        <div class="sm:hidden mb-6">
            <div class="w-full h-40 bg-gradient-to-r from-gray-700 via-gray-800 to-gray-700 overflow-hidden rounded-xl">
                @if(!empty($seller->store_banner))
                    <img src="{{ asset('storage/' . $seller->store_banner) }}" alt="Banner" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-r from-indigo-700 via-indigo-900 to-slate-800 flex items-center justify-center">
                        <span class="text-gray-300">Store banner (mobile)</span>
                    </div>
                @endif
            </div>

            <div class="-mt-10 flex items-center gap-4 px-2">
                <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-slate-900 bg-slate-800 flex items-center justify-center">
                    @if($seller->store_logo)
                        <img src="{{ asset('storage/' . $seller->store_logo) }}" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <span class="text-white font-bold text-xl">{{ substr($seller->name, 0, 1) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-white text-lg font-bold">{{ $seller->store_name ?? $seller->name }}</h1>
                    @if($seller->store_description)
                        <p class="text-gray-400 text-sm mt-1">{{ $seller->store_description }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Packs Grid (desktop only) -->
            <!-- hide on small screens and show only for desktop (lg and up) -->
            <div class="desktop-packs hidden lg:block lg:col-span-2">
                <h2 class="text-xl font-bold text-white mb-4">Select a Package</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($packsWithPrices as $pack)
                        @php
                            // Determine pack image based on game type and diamond amount
                            if ($gameType === 'freefire') {
                                if ($pack['diamonds'] >= 5000) {
                                    $imageName = 'freefirelaaargediamonds.webp';
                                } elseif ($pack['diamonds'] >= 2000) {
                                    $imageName = 'bigfreefirediamonds.webp';
                                } elseif ($pack['diamonds'] >= 500) {
                                    $imageName = 'diamondslargefreefire.webp';
                                } elseif ($pack['diamonds'] >= 100) {
                                    $imageName = 'diamondsmidfreefire.webp';
                                } else {
                                    $imageName = 'diamondssmallfreefire.webp';
                                }
                            } elseif ($gameType === 'honorofkings') {
                                if ($pack['diamonds'] >= 4000) {
                                    $imageName = 'honorofkings/bigtoken.webp';
                                } elseif ($pack['diamonds'] >= 1200) {
                                    $imageName = 'honorofkings/laargetoken.webp';
                                } elseif ($pack['diamonds'] >= 400) {
                                    $imageName = 'honorofkings/midtokne.webp';
                                } elseif ($pack['diamonds'] >= 16) {
                                    $imageName = 'honorofkings/smalltoken.webp';
                                } else {
                                    $imageName = 'honorofkings/weeklycard.webp';
                                }
                            } elseif ($gameType === 'pubgmobile' || $gameType === 'bloodstrike') {
                                $imageName = null; // No image for PUBG/Blood Strike
                            } else {
                                // Mobile Legends (default)
                                if (stripos($pack['name'], 'Weekly Diamond Pass') !== false || stripos($pack['name'], 'Event Topup') !== false) {
                                    $imageName = 'weeklymlbb.webp';
                                } elseif (stripos($pack['name'], 'Twilight Pass') !== false) {
                                    $imageName = 'twlilightpass.jpg';
                                } elseif ($pack['diamonds'] >= 2000) {
                                    $imageName = 'diasbigbig.webp';
                                } elseif ($pack['diamonds'] >= 500) {
                                    $imageName = 'diaslarge.webp';
                                } elseif ($pack['diamonds'] >= 100) {
                                    $imageName = 'diasmid.webp';
                                } else {
                                    $imageName = 'diaslow.webp';
                                }
                            }
                            
                            // Currency label
                            $currencyLabel = match($gameType) {
                                'pubgmobile' => 'UC',
                                'honorofkings' => 'Tokens',
                                'bloodstrike' => 'Golds',
                                default => 'Diamonds'
                            };
                        @endphp
                        
                        <div class="pack-card bg-slate-800 rounded-xl p-4 border-2 border-slate-700 cursor-pointer flex items-center gap-4"
                             onclick="selectPack({{ $pack['id'] }}, '{{ addslashes($pack['name']) }}', {{ $pack['price_dzd'] }}, {{ $pack['diamonds'] }}, {{ $pack['bonus_diamonds'] }})"
                             data-pack-id="{{ $pack['id'] }}">
                            
                            <!-- Pack Image -->
                            @if($imageName)
                                <div class="flex-shrink-0 w-14 h-14 flex items-center justify-center bg-slate-700/50 rounded-lg">
                                    <img src="{{ asset('storage/images_homepage/' . $imageName) }}" 
                                         alt="{{ $pack['diamonds'] }} {{ $currencyLabel }}" 
                                         class="w-full h-full object-contain p-1">
                                </div>
                            @else
                                <div class="flex-shrink-0 w-14 h-14 flex items-center justify-center bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-lg">
                                    <span class="text-2xl">💎</span>
                                </div>
                            @endif
                            
                            <!-- Pack Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-bold text-cyan-400">
                                        {{ number_format($pack['diamonds']) }}
                                        @if($pack['bonus_diamonds'] > 0)
                                            <span class="text-green-400 text-sm font-semibold">+{{ $pack['bonus_diamonds'] }}</span>
                                        @endif
                                    </h3>
                                    <span class="text-gray-400 text-sm">{{ $currencyLabel }}</span>
                                </div>
                                <p class="text-gray-400 text-sm truncate">{{ $pack['name'] }}</p>
                                <p class="text-white font-bold mt-1">{{ (int)$pack['price_dzd'] }} DZD</p>
                            </div>
                            
                            <!-- Selection Indicator -->
                            <div class="flex-shrink-0 w-6 h-6 border-2 border-slate-600 rounded-full flex items-center justify-center pack-check hidden">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination for packs --}}
                @if(isset($packs) && $packs->total() > 0)
                    <div class="mt-6 flex items-center justify-between gap-4">
                        <div class="text-sm text-gray-300">
                            Showing <strong class="text-white">{{ $packs->firstItem() ?? 0 }}</strong>
                            to <strong class="text-white">{{ $packs->lastItem() ?? 0 }}</strong>
                            of <strong class="text-white">{{ $packs->total() }}</strong> packages
                        </div>
                        <div class="flex-shrink-0">
                            {{ $packs->appends(request()->query())->links() }}
                        </div>
                    </div>
                @else
                    <div class="mt-6 text-center text-gray-400">No packages available</div>
                @endif
            </div>

            {{-- On mobile we use a dropdown / bottom-sheet. Add the select button inside the order column (lg:hidden) and include the bottom-sheet component below. --}}
            
            <!-- Order Form -->
            <div class="lg:col-span-1">
                <div class="bg-slate-800 rounded-xl p-6 sticky top-4 border border-slate-700">
                    <!-- Mobile: Select Pack Button (shows bottom-sheet) -->
                    <div class="lg:hidden mb-4" id="mobile-select-pack-container">
                        <button id="mobile-select-pack-btn" type="button" class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg flex items-center justify-between">
                            <span id="mobile-selected-pack-text" class="text-left w-full pr-2">
                                <span class="block text-sm font-medium">Select a Package</span>
                                <span id="mobile-selected-pack-details" class="text-xs opacity-75 hidden"></span>
                            </span>
                            <svg class="w-5 h-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Order Details</h3>
                    
                    <form action="{{ route('seller.store.checkout', ['username' => $seller->username]) }}" method="POST" id="checkout-form">
                        @csrf
                        <input type="hidden" name="game_type" value="{{ $gameType }}">
                        <input type="hidden" name="pack_id" id="pack_id" value="">
                        <input type="hidden" name="payment_method" value="baridimob">
                        
                        <!-- Selected Pack Display -->
                        <div class="mb-4 p-4 bg-slate-700/50 rounded-lg border border-slate-600">
                            <p class="text-gray-400 text-sm mb-1">Selected Pack</p>
                            <div id="selected-pack-display" class="hidden">
                                <div class="flex items-center gap-2 mb-1">
                                    <span id="selected-pack-diamonds" class="text-xl font-bold text-cyan-400">0</span>
                                    <span id="selected-pack-bonus" class="text-green-400 font-semibold text-sm"></span>
                                    <span class="text-gray-400 text-sm">{{ $gameType === 'pubgmobile' ? 'UC' : ($gameType === 'honorofkings' ? 'Tokens' : ($gameType === 'bloodstrike' ? 'Golds' : 'Diamonds')) }}</span>
                                </div>
                                <p id="selected-pack-name" class="text-gray-300 text-sm"></p>
                                <p id="selected-pack-price" class="text-white font-bold text-lg mt-1">0 DZD</p>
                            </div>
                            <p id="no-pack-selected" class="text-gray-500">None selected</p>
                        </div>
                        
                        <!-- Player ID -->
                        <div class="mb-4">
                            <label class="block text-gray-300 text-sm mb-2">Player ID</label>
                            <input type="text" name="player_id" id="player_id" required
                                class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none transition"
                                placeholder="Enter your Player ID">
                        </div>
                        
                        @if($gameType === 'mobilelegends')
                            <!-- Zone ID for ML -->
                            <div class="mb-4">
                                <label class="block text-gray-300 text-sm mb-2">Zone ID</label>
                                <input type="text" name="zone_id" id="zone_id" required
                                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none transition"
                                    placeholder="Enter your Zone ID">
                            </div>
                        @elseif($gameType === 'bloodstrike')
                            <!-- Server for BS -->
                            <div class="mb-4">
                                <label class="block text-gray-300 text-sm mb-2">Server</label>
                                <select name="zone_id" id="zone_id"
                                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none transition">
                                    <option value="Global">Global</option>
                                </select>
                            </div>
                        @endif
                        
                        <div class="bg-blue-600/20 border border-blue-500/30 rounded-lg p-3 mb-4">
                            <p class="text-blue-300 text-sm">
                                💳 Payment via Baridimob (CIB/Edahabia)
                            </p>
                        </div>
                        
                        <button type="submit" id="checkout-btn" disabled
                            class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span id="btn-text">Proceed to Payment</span>
                            <svg id="btn-spinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>
                    
                    {{-- Use the shared DiasZone mobile bottom-sheet component for mobile offers (hidden on desktop) --}}
                    @include('components.mobile-bottom-sheet', ['packs' => $packs, 'gameType' => $gameType, 'gameTitle' => $gameName])

                    <!-- Cookie Notice -->
                    <div id="cookie-notice" class="hidden mt-4 p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <p class="text-blue-300 text-xs flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Your info was loaded from your last order</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-slate-800 border-t border-slate-700 mt-8">
        <div class="max-w-6xl mx-auto px-4 py-6 text-center">
            <p class="text-gray-400 text-sm">
                © {{ date('Y') }} {{ $seller->store_name ?? $seller->name }}. 
                Powered by <a href="{{ route('home') }}" class="text-blue-400 hover:text-blue-300">DiasZone</a>
            </p>
        </div>
    </footer>
    
    <script>
        // Fallback globals: If app.js hasn't loaded yet, provide minimal implementations
        // so inline storefront code won't crash while waiting for the main bundle.
        if (typeof window.showValidationError !== 'function') {
            window.showValidationError = function(message) {
                try {
                    // Minimal visible error area placed inside the checkout form if available
                    const existing = document.getElementById('nickname-validation-error');
                    if (existing) existing.remove();

                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'nickname-validation-error';
                    errorDiv.className = 'mt-4 p-4 bg-red-50 border-2 border-red-200 rounded-lg';
                    errorDiv.innerHTML = `<p class="text-sm font-semibold text-red-800 mb-1">Validation Failed</p><p class="text-sm text-red-700">${message}</p>`;

                    const target = document.getElementById('checkout-form') || document.getElementById('order-form') || document.body;
                    target.appendChild(errorDiv);
                    errorDiv.scrollIntoView({behavior: 'smooth', block: 'nearest'});

                    setTimeout(() => { if (errorDiv.parentNode) errorDiv.remove(); }, 8000);
                } catch (e) { console.error('Fallback showValidationError failed', e); }
            };
        }

        if (typeof window.showNicknameSuccess !== 'function') {
            window.showNicknameSuccess = function(nickname, callback) {
                try {
                    const existing = document.getElementById('nickname-success-popup');
                    if (existing) existing.remove();

                    const popup = document.createElement('div');
                    popup.id = 'nickname-success-popup';
                    popup.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
                    popup.innerHTML = `
                        <div class="bg-white rounded-xl shadow p-6 max-w-sm w-full mx-4 text-center">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Nickname Verified</h3>
                            <p class="text-xl font-semibold text-purple-600 mb-2">${nickname}</p>
                            <p class="text-sm text-gray-600">Continuing to checkout </p>
                        </div>`;
                    document.body.appendChild(popup);
                    setTimeout(() => { popup.remove(); if (callback) callback(); }, 1000);
                } catch (e) { console.error('Fallback showNicknameSuccess failed', e); }
            };
        }
        let selectedPackId = null;
        const sellerUsername = '{{ $seller->username }}';
        const gameType = '{{ $gameType }}';
        
        // Cookie helper functions
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
            return null;
        }
        
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setDate(expires.getDate() + days);
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
        }
        
        // Get cookie prefix for this seller/game combo
        function getCookiePrefix() {
            return `dz_${sellerUsername}_${gameType}_`;
        }
        
        // Load saved data from cookies
        function loadFromCookies() {
            const prefix = getCookiePrefix();
            const playerId = getCookie(`${prefix}player_id`);
            const zoneId = getCookie(`${prefix}zone_id`);
            const packId = getCookie(`${prefix}pack_id`);
            
            let loaded = false;
            
            // Populate player ID
            if (playerId) {
                const playerInput = document.getElementById('player_id');
                if (playerInput) {
                    playerInput.value = playerId;
                    playerInput.classList.add('cookie-loaded');
                    loaded = true;
                }
            }
            
            // Populate zone ID
            if (zoneId) {
                const zoneInput = document.getElementById('zone_id');
                if (zoneInput) {
                    zoneInput.value = zoneId;
                    zoneInput.classList.add('cookie-loaded');
                    loaded = true;
                }
            }
            
            // Only auto-select a saved pack when we're confident the user is on a
            // desktop-like environment (avoid auto selection in mobile previews/emulators).
            const isDesktopView = window.matchMedia('(min-width: 1024px) and (hover: hover) and (pointer: fine)').matches;

            if (packId && isDesktopView) {
                const packCard = document.querySelector(`[data-pack-id="${packId}"]`);
                if (packCard) {
                    packCard.click();
                    loaded = true;
                }
            }
            
            // Show notice if data was loaded
            if (loaded) {
                const notice = document.getElementById('cookie-notice');
                if (notice) {
                    notice.classList.remove('hidden');
                    // Hide after 5 seconds
                    setTimeout(() => {
                        notice.classList.add('hidden');
                    }, 5000);
                }
            }
        }
        
        // Save data to cookies
        function saveToCookies() {
            const prefix = getCookiePrefix();
            const playerId = document.getElementById('player_id')?.value || '';
            const zoneId = document.getElementById('zone_id')?.value || '';
            
            if (playerId) setCookie(`${prefix}player_id`, playerId, 30);
            if (zoneId) setCookie(`${prefix}zone_id`, zoneId, 30);
            if (selectedPackId) setCookie(`${prefix}pack_id`, selectedPackId, 30);
        }
        
        function selectPack(packId, packName, price, diamonds, bonus) {
            // Remove previous selection
            document.querySelectorAll('.pack-card').forEach(card => {
                card.classList.remove('selected');
                const check = card.querySelector('.pack-check');
                if (check) check.classList.add('hidden');
            });
            
            // Add selection to clicked card
            const selectedCard = document.querySelector(`[data-pack-id="${packId}"]`);
            selectedCard.classList.add('selected');
            const checkmark = selectedCard.querySelector('.pack-check');
            if (checkmark) checkmark.classList.remove('hidden');
            
            // Update hidden input
            document.getElementById('pack_id').value = packId;
            
            // Update display
            document.getElementById('no-pack-selected').classList.add('hidden');
            document.getElementById('selected-pack-display').classList.remove('hidden');
            document.getElementById('selected-pack-diamonds').textContent = diamonds.toLocaleString();
            document.getElementById('selected-pack-bonus').textContent = bonus > 0 ? '+' + bonus : '';
            document.getElementById('selected-pack-name').textContent = packName;
            document.getElementById('selected-pack-price').textContent = Math.floor(price) + ' DZD';
            
            // Enable checkout button
            document.getElementById('checkout-btn').disabled = false;
            
            selectedPackId = packId;
        }
        
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            try {
                if (!selectedPackId) {
                    e.preventDefault();
                    alert('Please select a package first');
                    return;
                }

                // Save to cookies
                saveToCookies();

                // If Mobile Legends, perform nickname validation & confirmation before submitting
                if (gameType === 'mobilelegends') {
                    e.preventDefault();

                    const userId = document.getElementById('player_id')?.value?.trim() || '';
                    const zoneId = document.getElementById('zone_id')?.value?.trim() || '';

                    if (!userId || !zoneId) {
                        alert('Please enter both User ID and Zone ID');
                        return;
                    }

                    // Show temporary loading state
                    const checkoutBtn = document.getElementById('checkout-btn');
                    if (checkoutBtn) {
                        checkoutBtn.disabled = true;
                        document.getElementById('btn-text').textContent = 'Validating...';
                        document.getElementById('btn-spinner').classList.remove('hidden');
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    fetch('/api/validate-nickname', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ user_id: userId, zone_id: zoneId })
                    })
                    .then(async (r) => {
                        // Parse JSON body if available and attach status
                        let parsed = {};
                        try { parsed = await r.json(); } catch (e) { parsed = {}; }
                        return { ok: r.ok, status: r.status, body: parsed };
                    })
                    .then(({ok, status, body}) => {
                        const data = body;
                        if (!ok) {
                            // 400 responses likely indicate invalid user/zone — show message to user
                            const message = data && data.message ? data.message : 'Nickname validation failed. Please check your User ID and Zone ID.';
                            if (checkoutBtn) {
                                checkoutBtn.disabled = false;
                                document.getElementById('btn-text').textContent = 'Proceed to Payment';
                                document.getElementById('btn-spinner').classList.add('hidden');
                            }
                            showValidationError(message);
                            return;
                        }
                        // Revert loading if something goes wrong later
                        if (!data || data.result !== true || !data.data) {
                            if (checkoutBtn) {
                                checkoutBtn.disabled = false;
                                document.getElementById('btn-text').textContent = 'Proceed to Payment';
                                document.getElementById('btn-spinner').classList.add('hidden');
                            }

                            // Show validation error message near the form
                            const message = (data && data.message) ? data.message : 'Failed to validate nickname. Please check your User ID and Zone ID.';
                            showValidationError(message);
                            return;
                        }

                        const nickname = data.data;

                        // Build a full-screen confirmation overlay
                        // Remove any existing overlays
                        const existing = document.getElementById('ml-nickname-confirm-overlay');
                        if (existing) existing.remove();

                        const overlay = document.createElement('div');
                        overlay.id = 'ml-nickname-confirm-overlay';
                        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 p-4';
                        overlay.innerHTML = `
                            <div class=\"w-full max-w-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 rounded-2xl p-8 border border-slate-600 text-center shadow-2xl\">
                                <h2 class=\"text-2xl font-bold text-white mb-3\">Confirm Nickname</h2>
                                <p class=\"text-sm text-slate-300 mb-6\">We found this nickname for your Mobile Legends account. Please confirm it is correct before we proceed to checkout.</p>
                                <div class=\"bg-slate-900/60 border border-slate-700 rounded-xl p-6 mb-6\">
                                            <p class=\"text-xs text-slate-400 mb-2\">Detected nickname</p>
                                            <p class=\"text-3xl font-extrabold text-purple-400 break-words\">${nickname}</p>
                                        </div>
                                        <p class=\"text-sm text-slate-400 mb-4\">This will be displayed for 2 seconds before continuing to checkout.</p>
                                        <p class=\"text-xs text-slate-400 mt-2\">If this nickname is incorrect you can double-check your ID and Zone ID and try again.</p>
                            </div>
                        `;

                        document.body.appendChild(overlay);

                        // Auto-continue after 2 seconds (show nickname briefly)
                        setTimeout(() => {
                            // Add hidden input for nickname so server can validate / show it later
                            let nicknameInput = document.getElementById('input_nickname');
                            if (!nicknameInput) {
                                nicknameInput = document.createElement('input');
                                nicknameInput.type = 'hidden';
                                nicknameInput.name = 'nickname';
                                nicknameInput.id = 'input_nickname';
                                document.getElementById('checkout-form').appendChild(nicknameInput);
                            }
                            nicknameInput.value = nickname;

                            // Remove overlay then submit (small delay to ensure overlay fade visible)
                            overlay.remove();
                            document.getElementById('checkout-form').submit();
                        }, 2000);
                    })
                    .catch(err => {
                        console.error('Nickname validation error', err);
                        if (checkoutBtn) {
                            checkoutBtn.disabled = false;
                            document.getElementById('btn-text').textContent = 'Proceed to Payment';
                            document.getElementById('btn-spinner').classList.add('hidden');
                        }
                        showValidationError('Error validating nickname. Please try again.');
                    });

                    // We're handling submission async — return early
                    return;
                }

                // Non-ML games: show loading state and allow form to submit
                // Show loading state
                document.getElementById('checkout-btn').disabled = true;
                document.getElementById('btn-text').textContent = 'Processing...';
                document.getElementById('btn-spinner').classList.remove('hidden');

            } catch (error) {
                console.error('Checkout submit handler error', error);
            }
        });
        
        // Load saved data on page load and wire mobile dropdown behavior
        document.addEventListener('DOMContentLoaded', function() {
            loadFromCookies();

            // Mobile bottom-sheet controls (if present)
            const mobileSelectBtn = document.getElementById('mobile-select-pack-btn');
            const bottomSheet = document.getElementById('mobile-pack-bottom-sheet');
            const bottomOverlay = document.getElementById('bottom-sheet-overlay');
            const closeSheetBtn = document.getElementById('close-bottom-sheet-btn');

            function openBottomSheet() {
                if (!bottomSheet) return;
                bottomSheet.style.display = 'flex';
                setTimeout(() => bottomSheet.style.transform = 'translateY(0)', 20);
                if (bottomOverlay) bottomOverlay.style.display = 'block';
            }

            function closeBottomSheet() {
                if (!bottomSheet) return;
                bottomSheet.style.transform = 'translateY(100%)';
                if (bottomOverlay) bottomOverlay.style.display = 'none';
                setTimeout(() => bottomSheet.style.display = 'none', 220);
            }

            if (mobileSelectBtn) mobileSelectBtn.addEventListener('click', openBottomSheet);
            if (closeSheetBtn) closeSheetBtn.addEventListener('click', closeBottomSheet);
            if (bottomOverlay) bottomOverlay.addEventListener('click', closeBottomSheet);

            // Wire up mobile bottom-sheet list items
            document.querySelectorAll('.mobile-pack-item').forEach(el => {
                el.addEventListener('click', function() {
                    const id = this.getAttribute('data-pack-id');
                    const name = this.getAttribute('data-pack-name');
                    const price = parseFloat(this.getAttribute('data-pack-price-dzd') || '0');
                    const diamonds = parseInt(this.getAttribute('data-pack-diamonds') || '0');
                    const bonus = parseInt(this.getAttribute('data-pack-bonus') || '0');

                    selectPack(id, name, price, diamonds, bonus);

                    // Update mobile selected text if present
                    const mobileText = document.getElementById('mobile-selected-pack-text');
                    const mobileDetails = document.getElementById('mobile-selected-pack-details');
                    if (mobileText && mobileDetails) {
                        mobileText.querySelector('span')?.classList.add('text-sm');
                        mobileDetails.classList.remove('hidden');
                        mobileDetails.textContent = `${Math.floor(price).toLocaleString()} DZD`;
                    }

                    closeBottomSheet();
                });
            });
        });
    </script>
</body>
</html>
