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
        @if(empty($packs) || count($packs) === 0)
            <div class="text-center py-8">
                <p class="text-gray-500">{{ __('game.no_packs_available') }}</p>
            </div>
        @else
        @foreach($packs as $index => $pack)
            @php
                $discountAmount = ($pack->price * $pack->discount_percentage) / 100;
                $priceAfterDiscount = $pack->price - $discountAmount;
                // Image selection based on diamond quantity and game type
                $gameType = $gameType ?? 'mobilelegends';
                if ($gameType === 'freefire') {
                    // Free Fire diamond images
                    if ($pack->diamonds >= 5000) {
                        $imageName = 'freefirelaaargediamonds.webp';
                    } elseif ($pack->diamonds >= 2000) {
                        $imageName = 'bigfreefirediamonds.webp';
                    } elseif ($pack->diamonds >= 500) {
                        $imageName = 'diamondslargefreefire.webp';
                    } elseif ($pack->diamonds >= 100) {
                        $imageName = 'diamondsmidfreefire.webp';
                    } else {
                        $imageName = 'diamondssmallfreefire.webp';
                    }
                } elseif ($gameType === 'pubgmobile') {
                    // PUBG Mobile UC images (using Mobile Legends images as placeholder)
                    if ($pack->diamonds >= 2000) {
                        $imageName = 'diasbigbig.webp';
                    } elseif ($pack->diamonds >= 500) {
                        $imageName = 'diaslarge.webp';
                    } elseif ($pack->diamonds >= 100) {
                        $imageName = 'diasmid.webp';
                    } else {
                        $imageName = 'diaslow.webp';
                    }
                } else {
                    // Mobile Legends (default)
                    // Check for special passes first
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
                }
            @endphp
                @php $packQuantity = $pack->special_quantity ?? 1; @endphp
                <button type="button"
                    class="mobile-pack-item w-full bg-white border border-gray-200 rounded-2xl p-4 active:scale-[0.98] transition-all text-left shadow-sm hover:shadow-md hover:border-purple-300 active:bg-purple-50/30"
                    data-pack-id="{{ $pack->id }}"
                    data-pack-quantity="{{ $packQuantity }}"
                    data-pack-diamonds="{{ $pack->diamonds }}"
                    data-pack-bonus="{{ $pack->bonus_diamonds }}"
                    data-pack-price="{{ $pack->price }}"
                    data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                    data-pack-price-dzd="{{ $pack->price_dzd ?? ($pack->price * 260) }}"
                    data-pack-name="{{ $pack->name }}"
                    data-pack-discount="{{ $pack->discount_percentage }}">
                <div class="flex items-center gap-4">
                    <!-- Image (empty space for PUBG Mobile, Honor of Kings, Blood Strike) -->
                    @if(($gameType ?? 'mobilelegends') === 'pubgmobile' || ($gameType ?? 'mobilelegends') === 'bloodstrike')
                        <!-- PUBG Mobile / Blood Strike: Empty space to maintain layout -->
                        <div class="flex-shrink-0 w-16 h-16"></div>
                    @elseif(($gameType ?? 'mobilelegends') === 'honorofkings')
                        <!-- Honor of Kings: Images from honorofkings folder (empty for 0 token packs) -->
                        @if($pack->diamonds == 0)
                            <!-- Empty space for packs with 0 tokens -->
                            <div class="flex-shrink-0 w-16 h-16"></div>
                        @else
                            <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                                @php
                                    // Honor of Kings image selection based on token quantity
                                    // Available images: bigtoken.webp, laargetoken.webp, midtokne.webp, smalltoken.webp, weeklycard.webp, weeklycardplus.webp
                                    if ($pack->price == 2.99 && $pack->diamonds <= 2) {
                                        // Weekly Card Plus
                                        $imageName = 'honorofkings/weeklycardplus.webp';
                                    } elseif ($pack->price == 0.96 && $pack->diamonds <= 2) {
                                        // Weekly Card
                                        $imageName = 'honorofkings/weeklycard.webp';
                                    } elseif ($pack->diamonds >= 4000) {
                                        $imageName = 'honorofkings/bigtoken.webp';
                                    } elseif ($pack->diamonds >= 1200) {
                                        $imageName = 'honorofkings/laargetoken.webp';
                                    } elseif ($pack->diamonds >= 400) {
                                        $imageName = 'honorofkings/midtokne.webp';
                                    } elseif ($pack->diamonds >= 16) {
                                        $imageName = 'honorofkings/smalltoken.webp';
                                    } else {
                                        // Default for other special packs
                                        $imageName = 'honorofkings/weeklycard.webp';
                                    }
                                @endphp
                                <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                     alt="{{ $pack->diamonds }} Tokens" 
                                     class="w-12 h-12 object-contain"
                                     style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                            </div>
                        @endif
                    @else
                        <!-- Other Games: Diamond Image -->
                        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                            <img src="{{ url('storage/images_homepage/' . $imageName) }}" 
                                 alt="{{ $pack->diamonds }} {{ __('game.diamonds') }}" 
                                 class="w-12 h-12 object-contain"
                                 style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                        </div>
                    @endif
                    
                    <!-- Pack Info -->
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
                                        <span class="text-sm font-medium text-gray-600">{{ __('game.tokens') }}</span>
                                    @endif
                                @else
                                    @if(stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false)
                                        <h3 class="text-base font-bold text-gray-900">{{ ($packQuantity > 1 && stripos($pack->name ?? '', 'weekly') !== false) ? $packQuantity . 'x ' . __('game.weekly_diamond_pass') : __('game.weekly_diamond_pass') }}</h3>
                                    @elseif(stripos($pack->name, 'Twilight Pass') !== false)
                                        <h3 class="text-base font-bold text-gray-900">{{ __('game.twilight_pass') }}</h3>
                                    @else
                                        <h3 class="text-base font-bold text-gray-900">{{ number_format($pack->diamonds) }}</h3>
                                        <span class="text-sm font-medium text-gray-600">
                                            @if(($gameType ?? 'mobilelegends') === 'pubgmobile')
                                                {{ __('game.uc') }}
                                            @elseif(($gameType ?? 'mobilelegends') === 'honorofkings')
                                                {{ __('game.tokens') }}
                                            @elseif(($gameType ?? 'mobilelegends') === 'bloodstrike')
                                                {{ __('game.golds') }}
                                            @else
                                                {{ __('game.diamonds') }}
                                            @endif
                                        </span>
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
                                <p class="text-sm text-gray-600 font-medium">+ {{ number_format($pack->bonus_diamonds) }} {{ __('game.bonus') }}</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($pack->discount_percentage > 0)
                                @php
                                    $priceDzd = $pack->price_dzd ?? ($pack->price * 260);
                                @endphp
                                <span class="text-xs text-gray-400 line-through font-medium mobile-pack-original-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $priceDzd }}" data-pack-quantity="{{ $packQuantity }}">{{ number_format($priceDzd * ($packQuantity), 0) }} DZD</span>
                                @else
                                <span class="text-xs text-gray-500">{{ __('game.best_value') }}</span>
                                @endif
                                <div class="flex items-baseline gap-1">
                                    @php
                                        $priceDzd = $pack->price_dzd ?? ($pack->price * 260);
                                        $discountPercentage = $pack->discount_percentage ?? 0;
                                        $priceAfterDiscountDzd = $priceDzd * (1 - $discountPercentage / 100);
                                    @endphp
                                    <span class="text-lg font-bold text-purple-600 mobile-pack-final-price" data-price-usd="{{ $pack->price_usd ?? $pack->price }}" data-price-dzd="{{ $priceDzd }}" data-discount="{{ $discountPercentage }}" data-pack-quantity="{{ $packQuantity }}" data-pack-id="{{ $pack->id }}">{{ number_format($priceAfterDiscountDzd * ($packQuantity), 0) }} DZD</span>
                                </div>
                            </div>
                            <!-- Quantity Counter -->
                            <div class="flex items-center gap-1 border border-gray-300 rounded-lg" onclick="event.stopPropagation();">
                                <button type="button" class="mobile-quantity-btn-decrease px-2 py-1 text-gray-600 hover:text-purple-600 hover:bg-purple-50 transition-colors rounded-l-lg" data-pack-id="{{ $pack->id }}" onclick="event.stopPropagation(); updateMobilePackQuantity({{ $pack->id }}, -1);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input type="number" 
                                       class="mobile-pack-quantity-input w-8 text-center text-sm font-semibold border-0 focus:ring-0 focus:outline-none bg-transparent" 
                                       value="1" 
                                       min="1" 
                                       max="20" 
                                       data-pack-id="{{ $pack->id }}"
                                       data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                                       data-pack-price-dzd="{{ $priceDzd }}"
                                       data-pack-discount="{{ $discountPercentage }}"
                                       readonly
                                       onclick="event.stopPropagation(); this.select();">
                                <button type="button" class="mobile-quantity-btn-increase px-2 py-1 text-gray-600 hover:text-purple-600 hover:bg-purple-50 transition-colors rounded-r-lg" data-pack-id="{{ $pack->id }}" onclick="event.stopPropagation(); updateMobilePackQuantity({{ $pack->id }}, 1);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selection Indicator -->
                    <div class="flex-shrink-0">
                        <div class="mobile-pack-selection-badge hidden absolute top-2 right-2 w-6 h-6 bg-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
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
        @endif
    </div>
</div>

<!-- Bottom Sheet Overlay -->
<div id="bottom-sheet-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden" style="display: none; z-index: 9998;"></div>

