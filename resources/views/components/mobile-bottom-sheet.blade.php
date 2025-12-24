<!-- Mobile Bottom Sheet -->
<div id="mobile-pack-bottom-sheet" class="fixed left-0 right-0 bottom-0 w-full bg-white shadow-2xl transition-transform duration-300 ease-out" style="height: 85vh; max-height: 85vh; border-top-left-radius: 24px; border-top-right-radius: 24px; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15); display: none; flex-direction: column; z-index: 9999 !important; background-color: #ffffff !important; opacity: 1 !important; visibility: visible !important; pointer-events: auto !important; position: fixed !important; width: 100% !important; left: 0 !important; right: 0 !important; bottom: 0 !important; transform: translateY(100%) !important;">
    <!-- Drag Handle Indicator -->
    <div class="flex justify-center pt-3 pb-2 flex-shrink-0">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
    </div>
    
    <!-- Bottom Sheet Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0 bg-white" style="background-color: #ffffff !important; z-index: 1;">
        <div>
            <h3 class="text-xl font-bold text-gray-900" style="color: #111827 !important;">{{ __('game.select_topup_amount') }}</h3>
            <p class="text-xs text-gray-500 mt-0.5" style="color: #6b7280 !important;">{{ str_replace(':game', strtolower($gameTitle ?? __('game.diamonds')), __('game.choose_pack')) }}</p>
        </div>
        <button id="close-bottom-sheet-btn" class="p-2 -mr-2 text-gray-400 hover:text-gray-600 active:bg-gray-100 rounded-full transition-colors" style="z-index: 2;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Bottom Sheet Content (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3" style="overflow-y: auto !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; background-color: #ffffff !important; min-height: 200px;">
        @php
            // Check if packs are grouped by region (for Steam Gift Cards)
            $isGroupedByRegion = !empty($packs) && $packs->isNotEmpty() && $packs->first() instanceof \Illuminate\Support\Collection;
            $regionNames = [
                'free' => 'Global',
                'us' => 'United States',
                'br' => 'Brazil',
                'cn' => 'China',
                'eu' => 'Europe',
                'gb' => 'United Kingdom',
                'ae' => 'United Arab Emirates',
                'hk' => 'Hong Kong',
                'tw' => 'Taiwan',
                'vn' => 'Vietnam',
                'th' => 'Thailand',
                'ph' => 'Philippines',
                'sg' => 'Singapore',
                'id' => 'Indonesia',
                'in' => 'India',
                'kw' => 'Kuwait',
                'qa' => 'Qatar',
                'sa' => 'Saudi Arabia',
                'za' => 'South Africa',
                'ua' => 'Ukraine',
                'tr' => 'Turkey',
                'cr' => 'Costa Rica',
                'pe' => 'Peru',
                'uy' => 'Uruguay',
            ];
        @endphp
        
        @if(empty($packs) || count($packs) === 0)
            <div class="text-center py-8">
                <p class="text-gray-500">{{ __('game.no_packs_available') }}</p>
            </div>
        @elseif($isGroupedByRegion)
            {{-- Grouped by region (Steam Gift Cards) --}}
            @foreach($packs as $regionCode => $regionPacks)
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-3 border-b border-purple-500 pb-2">
                        {{ $regionNames[$regionCode] ?? ucfirst(str_replace('_', ' ', $regionCode)) }}
                    </h3>
                    @foreach($regionPacks as $index => $pack)
            @php
                $discountAmount = ($pack->price * $pack->discount_percentage) / 100;
                $priceAfterDiscount = $pack->price - $discountAmount;
                $packQuantity = ($pack->special_quantity > 0) ? $pack->special_quantity : 1;
                
                // Extract currency name from pack name
                $currencyName = 'Diamonds'; // default
                $packNameLower = strtolower($pack->name ?? '');
                
                // Common currency keywords to look for in pack name
                if (preg_match('/\b(tokens?|diamonds?|coins?|crystals?|golds?|bonds?|points?|flowers?|uc|conquer points?)\b/i', $packNameLower, $matches)) {
                    $currencyName = ucfirst(rtrim($matches[1], 's')); // Remove plural 's' and capitalize
                    // Handle special cases
                    if (stripos($currencyName, 'token') !== false) $currencyName = 'Tokens';
                    elseif (stripos($currencyName, 'diamond') !== false) $currencyName = 'Diamonds';
                    elseif (stripos($currencyName, 'coin') !== false) $currencyName = 'Coins';
                    elseif (stripos($currencyName, 'crystal') !== false) $currencyName = 'Crystals';
                    elseif (stripos($currencyName, 'gold') !== false) $currencyName = 'Gold';
                    elseif (stripos($currencyName, 'bond') !== false) $currencyName = 'Bonds';
                    elseif (stripos($currencyName, 'point') !== false) $currencyName = 'Points';
                    elseif (stripos($currencyName, 'flower') !== false) $currencyName = 'Flowers';
                    elseif (stripos($currencyName, 'uc') !== false) $currencyName = 'UC';
                }
                
                // Fallback to game type defaults for legacy games
                if ($currencyName === 'Diamonds') {
                    $gameType = $gameType ?? 'mobilelegends';
                    if ($gameType === 'pubgmobile') {
                        $currencyName = 'UC';
                    } elseif ($gameType === 'honorofkings') {
                        $currencyName = 'Tokens';
                    } elseif ($gameType === 'bloodstrike') {
                        $currencyName = 'Golds';
                    }
                }
            @endphp
                @php $packQuantity = $pack->special_quantity ?? 1; @endphp
                <div class="mobile-pack-item-wrapper relative">
                    <input type="checkbox" 
                           class="hidden mobile-pack-checkbox" 
                           id="mobile-pack-checkbox-{{ $pack->id }}"
                    data-pack-id="{{ $pack->id }}"
                    data-pack-quantity="{{ $packQuantity }}"
                    data-pack-diamonds="{{ $pack->diamonds }}"
                    data-pack-bonus="{{ $pack->bonus_diamonds }}"
                    data-pack-price="{{ $pack->price }}"
                    data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                    data-pack-price-dzd="{{ $pack->price_dzd }}"
                    data-pack-name="{{ $pack->name }}"
                    data-pack-membership-name="{{ $pack->membership_name }}"
                    data-pack-discount="{{ $pack->discount_percentage }}"
                    data-pack-currency="{{ $currencyName }}">
                    
                    <!-- Quantity Selector (visible when checked) -->
                    <div class="mobile-pack-quantity-control absolute top-2 right-2 hidden flex items-center gap-1.5 bg-purple-50 rounded-lg px-1.5 py-0.5 border border-purple-200 z-10">
                        <button type="button" class="mobile-quantity-decrease w-5 h-5 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-xs border border-purple-200 transition-colors" data-pack-id="{{ $pack->id }}" title="Decrease" onclick="event.stopPropagation(); event.preventDefault();">−</button>
                        <input type="number" 
                               class="mobile-quantity-input w-8 h-5 text-center text-xs font-semibold bg-transparent border-0 p-0 focus:outline-none" 
                               value="1" 
                               min="1" 
                               max="20" 
                               data-pack-id="{{ $pack->id }}"
                               readonly
                               onclick="event.stopPropagation();">
                        <button type="button" class="mobile-quantity-increase w-5 h-5 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-xs border border-purple-200 transition-colors" data-pack-id="{{ $pack->id }}" title="Increase" onclick="event.stopPropagation(); event.preventDefault();">+</button>
                    </div>
                    
                    <label for="mobile-pack-checkbox-{{ $pack->id }}" 
                           class="mobile-pack-item w-full bg-white border border-gray-200 rounded-2xl p-4 active:scale-[0.98] transition-all text-left shadow-sm hover:shadow-md hover:border-purple-300 active:bg-purple-50/30 cursor-pointer block"
                           data-pack-wrapper="{{ $pack->id }}"
                           onclick="event.stopPropagation();">
                <div class="flex items-center gap-4">
                    <!-- Image: Only show for Mobile Legends -->
                    @if(($gameType ?? 'mobilelegends') === 'mobilelegends')
                        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                            @php
                                // Mobile Legends images only
                                if (stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false) {
                                    $imageName = 'weeklymlbb.webp';
                                } elseif (stripos($pack->name, 'Twilight Pass') !== false) {
                                    $imageName = 'twlilightpass.jpg';
                                } else {
                                    // Regular diamond packs
                                    $imageName = 'diaslow.webp';
                                    if ($pack->diamonds >= 2000) {
                                        $imageName = 'diasbigbig.webp';
                                    } elseif ($pack->diamonds >= 500) {
                                        $imageName = 'diaslarge.webp';
                                    } elseif ($pack->diamonds >= 100) {
                                        $imageName = 'diasmid.webp';
                                    }
                                }
                            @endphp
                            <img src="{{ url('storage_public/images_homepage/' . $imageName) }}" 
                                 alt="{{ $pack->diamonds }} {{ __('game.diamonds') }}" 
                                 class="w-12 h-12 object-contain"
                                 style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                        </div>
                    @elseif(($gameType ?? '') !== 'freefire' && !empty($gameImage ?? null))
                        <!-- Show game image thumbnail for games other than Mobile Legends and Free Fire -->
                        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-gray-50 rounded-lg overflow-hidden">
                            <img src="{{ asset($gameImage) }}" 
                                 alt="{{ $gameTitle ?? '' }}" 
                                 class="w-full h-full object-cover rounded-lg"
                                 style="display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important;">
                        </div>
                    @else
                        <!-- Free Fire and games without images: Empty space to maintain layout -->
                        <div class="flex-shrink-0 w-16 h-16"></div>
                    @endif
                    
                    <!-- Pack Info -->
                    @php 
                        $packQuantity = ($pack->special_quantity > 0) ? $pack->special_quantity : 1;
                        
                        // Extract currency name from pack name (e.g., "60 + 6 Bonds" -> "Bonds")
                        $currencyName = 'Diamonds'; // default
                        $packNameLower = strtolower($pack->name ?? '');
                        
                        // Common currency keywords to look for in pack name
                        if (preg_match('/\b(tokens?|diamonds?|coins?|crystals?|golds?|bonds?|points?|flowers?|uc|conquer points?)\b/i', $packNameLower, $matches)) {
                            $currencyName = ucfirst(rtrim($matches[1], 's')); // Remove plural 's' and capitalize
                            // Handle special cases
                            if (stripos($currencyName, 'token') !== false) $currencyName = 'Tokens';
                            elseif (stripos($currencyName, 'diamond') !== false) $currencyName = 'Diamonds';
                            elseif (stripos($currencyName, 'coin') !== false) $currencyName = 'Coins';
                            elseif (stripos($currencyName, 'crystal') !== false) $currencyName = 'Crystals';
                            elseif (stripos($currencyName, 'gold') !== false) $currencyName = 'Gold';
                            elseif (stripos($currencyName, 'bond') !== false) $currencyName = 'Bonds';
                            elseif (stripos($currencyName, 'point') !== false) $currencyName = 'Points';
                            elseif (stripos($currencyName, 'flower') !== false) $currencyName = 'Flowers';
                            elseif (stripos($currencyName, 'uc') !== false) $currencyName = 'UC';
                        }
                        
                        // Fallback to game type defaults for legacy games
                        if ($currencyName === 'Diamonds') {
                            if (($gameType ?? 'mobilelegends') === 'pubgmobile') {
                                $currencyName = 'UC';
                            } elseif (($gameType ?? 'mobilelegends') === 'honorofkings') {
                                $currencyName = 'Tokens';
                            } elseif (($gameType ?? 'mobilelegends') === 'bloodstrike') {
                                $currencyName = 'Golds';
                            }
                        }
                    @endphp
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                @if(($gameType ?? 'mobilelegends') === 'honorofkings')
                                    @if($pack->diamonds == 0)
                                        @if($pack->price == 0.32 && $pack->sort_order == 130)
                                            <h3 class="text-base font-bold text-gray-900">{{ __('game.double_token_lucky_bag') }}</h3>
                                        @elseif($pack->price == 0.32 && $pack->sort_order == 140)
                                            <h3 class="text-base font-bold text-gray-900">{{ __('game.standard_purchase_rebate_pack') }}</h3>
                                        @elseif($pack->price == 0.32 && $pack->sort_order == 160)
                                            <h3 class="text-base font-bold text-gray-900">{{ __('game.honor_point_value_pack') }}</h3>
                                        @elseif($pack->price == 1.18)
                                            <h3 class="text-base font-bold text-gray-900">{{ __('game.premium_purchase_rebate_pack') }}</h3>
                                        @else
                                            <h3 class="text-base font-bold text-gray-900">{{ __('game.special_pack') }}</h3>
                                        @endif
                                    @elseif($pack->diamonds == 1 && $pack->price == 0.96)
                                        <h3 class="text-base font-bold text-gray-900">{{ __('game.weekly_card') }}</h3>
                                    @elseif($pack->diamonds == 2 && $pack->price == 2.99)
                                        <h3 class="text-base font-bold text-gray-900">{{ __('game.weekly_card_plus') }}</h3>
                                    @else
                                        <h3 class="text-base font-bold text-gray-900">{{ number_format($pack->diamonds) }}</h3>
                                        <span class="text-sm font-medium text-gray-600">{{ $currencyName }}</span>
                                    @endif
                                @else
                                    @if(stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false)
                                        <h3 class="text-base font-bold text-gray-900">{{ ($packQuantity > 1 && stripos($pack->name ?? '', 'weekly') !== false) ? $packQuantity . 'x ' . __('game.weekly_diamond_pass') : __('game.weekly_diamond_pass') }}</h3>
                                    @elseif(stripos($pack->name, 'Twilight Pass') !== false)
                                        <h3 class="text-base font-bold text-gray-900">{{ __('game.twilight_pass') }}</h3>
                                    @else
                                        @if($pack->diamonds == 0 && $pack->membership_name)
                                            <h3 class="text-base font-bold text-gray-900">{{ $pack->membership_name }}</h3>
                                        @else
                                            <h3 class="text-base font-bold text-gray-900">{{ number_format($pack->diamonds) }}</h3>
                                            <span class="text-sm font-medium text-gray-600">{{ $currencyName }}</span>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            @if($pack->discount_percentage > 0)
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-purple-600 to-pink-600 px-2.5 py-1 rounded-full shadow-sm">{{ $pack->discount_percentage }}% {{ __('game.off') }}</span>
                            @endif
                        </div>
                        @if($pack->bonus_diamonds > 0)
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">+ {{ number_format($pack->bonus_diamonds) }} {{ __('game.bonus') }} {{ $currencyName }}</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                                @if($pack->discount_percentage > 0)
                                @php
                                    $priceDzd = $pack->price_dzd ?? $pack->price; // Fallback to price if price_dzd is null
                                @endphp
                                <span class="text-xs text-gray-400 line-through font-medium mobile-pack-original-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $priceDzd }}" data-pack-quantity="{{ $packQuantity }}">{{ $priceDzd ? number_format($priceDzd * ($packQuantity), 0) : '0' }} DZD</span>
                                @else
                                <span class="text-xs text-gray-500">{{ __('game.best_value') }}</span>
                                @endif
                            <div class="flex items-baseline gap-1">
                                @php
                                    $priceDzd = $pack->price_dzd ?? $pack->price; // Fallback to price if price_dzd is null
                                    $discountPercentage = $pack->discount_percentage ?? 0;
                                    $priceAfterDiscountDzd = $priceDzd ? ($priceDzd * (1 - $discountPercentage / 100)) : 0;
                                @endphp
                                <span class="text-lg font-bold text-purple-600 mobile-pack-final-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $priceDzd }}" data-discount="{{ $discountPercentage }}" data-pack-quantity="{{ $packQuantity }}">{{ number_format($priceAfterDiscountDzd * ($packQuantity), 0) }} DZD</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selection Indicator -->
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors mobile-pack-indicator">
                            <svg class="w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                    </label>
                </div>
                    @endforeach
                    </div>
                </div>
            @endforeach
        @else
            {{-- Normal packs (not grouped) --}}
            @foreach($packs as $index => $pack)
                @php
                    $discountAmount = ($pack->price * $pack->discount_percentage) / 100;
                    $priceAfterDiscount = $pack->price - $discountAmount;
                    $packQuantity = ($pack->special_quantity > 0) ? $pack->special_quantity : 1;
                    
                    // Extract currency name from pack name
                    $currencyName = 'Diamonds'; // default
                    $packNameLower = strtolower($pack->name ?? '');
                    
                    // Common currency keywords to look for in pack name
                    if (preg_match('/\b(tokens?|diamonds?|coins?|crystals?|golds?|bonds?|points?|flowers?|uc|conquer points?)\b/i', $packNameLower, $matches)) {
                        $currencyName = ucfirst(rtrim($matches[1], 's')); // Remove plural 's' and capitalize
                        // Handle special cases
                        if (stripos($currencyName, 'token') !== false) $currencyName = 'Tokens';
                        elseif (stripos($currencyName, 'diamond') !== false) $currencyName = 'Diamonds';
                        elseif (stripos($currencyName, 'coin') !== false) $currencyName = 'Coins';
                        elseif (stripos($currencyName, 'crystal') !== false) $currencyName = 'Crystals';
                        elseif (stripos($currencyName, 'gold') !== false) $currencyName = 'Gold';
                        elseif (stripos($currencyName, 'bond') !== false) $currencyName = 'Bonds';
                        elseif (stripos($currencyName, 'point') !== false) $currencyName = 'Points';
                        elseif (stripos($currencyName, 'flower') !== false) $currencyName = 'Flowers';
                        elseif (stripos($currencyName, 'uc') !== false) $currencyName = 'UC';
                    }
                    
                    // Fallback to game type defaults
                    if ($currencyName === 'Diamonds') {
                        if (($gameType ?? 'mobilelegends') === 'pubgmobile') {
                            $currencyName = 'UC';
                        } elseif (($gameType ?? 'mobilelegends') === 'honorofkings') {
                            $currencyName = 'Tokens';
                        } elseif (($gameType ?? 'mobilelegends') === 'bloodstrike') {
                            $currencyName = 'Golds';
                        }
                    }
                @endphp
                <div class="mobile-pack-item" data-pack-id="{{ $pack->id }}">
                    <input type="checkbox" 
                         name="diamond_pack[]" 
                         value="{{ $pack->id }}" 
                         class="hidden mobile-pack-checkbox"
                         data-pack-id="{{ $pack->id }}"
                         data-pack-quantity="{{ $packQuantity }}"
                         data-pack-diamonds="{{ $pack->diamonds }}"
                         data-pack-bonus="{{ $pack->bonus_diamonds }}"
                         data-pack-price="{{ $pack->price }}"
                         data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                         data-pack-price-dzd="{{ $pack->price_dzd ?? $pack->price }}"
                         data-pack-name="{{ $pack->name }}"
                         data-pack-membership-name="{{ $pack->membership_name }}"
                         data-pack-discount="{{ $pack->discount_percentage }}"
                         data-pack-currency="{{ $currencyName }}"
                         id="mobile-pack-checkbox-{{ $pack->id }}">
                    
                    <label for="mobile-pack-checkbox-{{ $pack->id }}" class="flex items-center justify-between p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-purple-500 transition-all cursor-pointer">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-semibold text-gray-900">
                                    @if($pack->diamonds == 0 && $pack->membership_name)
                                        {{ $pack->membership_name }}
                                    @else
                                        @if(stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false)
                                            {{ __('game.weekly_diamond_pass') }}
                                        @elseif(stripos($pack->name, 'Twilight Pass') !== false)
                                            {{ __('game.twilight_pass') }}
                                        @else
                                            {{ $pack->diamonds }} {{ $currencyName }}
                                        @endif
                                    @endif
                                </h4>
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded">{{ $pack->discount_percentage }}% {{ __('game.off') }}</span>
                                @endif
                            </div>
                            @if($pack->bonus_diamonds > 0)
                                <div class="flex items-center text-xs text-purple-600 mb-1">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600 font-medium">+ {{ number_format($pack->bonus_diamonds) }} {{ __('game.bonus') }} {{ $currencyName }}</p>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                @if($pack->discount_percentage > 0)
                                    @php
                                        $priceDzd = $pack->price_dzd ?? $pack->price; // Fallback to price if price_dzd is null
                                    @endphp
                                    <span class="text-xs text-gray-400 line-through font-medium mobile-pack-original-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $priceDzd }}" data-pack-quantity="{{ $packQuantity }}">{{ $priceDzd ? number_format($priceDzd * ($packQuantity), 0) : '0' }} DZD</span>
                                @else
                                    <span class="text-xs text-gray-500">{{ __('game.best_value') }}</span>
                                @endif
                                <span class="text-sm font-bold text-purple-600 mobile-pack-final-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $pack->price_dzd ?? $pack->price }}" data-discount="{{ $pack->discount_percentage }}" data-pack-quantity="{{ $packQuantity }}">{{ number_format($priceAfterDiscount * ($packQuantity), 2) }} DZD</span>
                            </div>
                        </div>
                        <!-- Selection Indicator -->
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center transition-colors mobile-pack-indicator">
                                <svg class="w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Bottom Sheet Overlay -->
<div id="bottom-sheet-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden" style="display: none; z-index: 9998;"></div>

<!-- Floating Close Button (only visible when bottom sheet is open) -->
<button id="mobile-bottom-sheet-floating-close" 
        class="fixed bottom-24 right-4 w-12 h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-full shadow-lg flex items-center justify-center z-[10000] hidden lg:hidden transition-all hover:scale-110 active:scale-95"
        style="display: none;"
        title="Close">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
</button>

