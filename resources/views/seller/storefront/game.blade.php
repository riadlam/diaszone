<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $gameName }} - {{ $seller->store_name ?? $seller->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Packs Grid -->
            <div class="lg:col-span-2">
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
            
            <!-- Order Form -->
            <div class="lg:col-span-1">
                <div class="bg-slate-800 rounded-xl p-6 sticky top-4 border border-slate-700">
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
            
            // Select saved pack
            if (packId) {
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
            if (!selectedPackId) {
                e.preventDefault();
                alert('Please select a package first');
                return;
            }
            
            // Save to cookies
            saveToCookies();
            
            // Show loading state
            document.getElementById('checkout-btn').disabled = true;
            document.getElementById('btn-text').textContent = 'Processing...';
            document.getElementById('btn-spinner').classList.remove('hidden');
        });
        
        // Load saved data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFromCookies();
        });
    </script>
</body>
</html>
