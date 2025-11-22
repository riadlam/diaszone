<!-- Mobile Bottom Sheet -->
<div id="mobile-pack-bottom-sheet" class="fixed left-0 right-0 bottom-0 w-full bg-white shadow-2xl z-50 transform translate-y-full transition-transform duration-300 ease-out lg:hidden" style="height: 85vh; border-top-left-radius: 24px; border-top-right-radius: 24px; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15); display: none !important; flex-direction: column;">
    <!-- Drag Handle Indicator -->
    <div class="flex justify-center pt-3 pb-2 flex-shrink-0">
        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
    </div>
    
    <!-- Bottom Sheet Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0 bg-white">
        <div>
            <h3 class="text-xl font-bold text-gray-900">Select Top-Up Amount</h3>
            <p class="text-xs text-gray-500 mt-0.5">Choose your {{ strtolower($gameTitle ?? 'diamond') }} pack</p>
        </div>
        <button id="close-bottom-sheet-btn" class="p-2 -mr-2 text-gray-400 hover:text-gray-600 active:bg-gray-100 rounded-full transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    <!-- Bottom Sheet Content (Scrollable) -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3" style="overflow-y: auto !important; -webkit-overflow-scrolling: touch; overscroll-behavior: contain;">
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
            <button type="button"
                    class="mobile-pack-item w-full bg-white border border-gray-200 rounded-2xl p-4 active:scale-[0.98] transition-all text-left shadow-sm hover:shadow-md hover:border-purple-300 active:bg-purple-50/30"
                    data-pack-id="{{ $pack->id }}"
                    data-pack-diamonds="{{ $pack->diamonds }}"
                    data-pack-bonus="{{ $pack->bonus_diamonds }}"
                    data-pack-price="{{ $pack->price }}"
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
                                 alt="{{ $pack->diamonds }} Diamonds" 
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
                                            <h3 class="text-base font-bold text-gray-900">Double Token Lucky Bag</h3>
                                        @elseif($pack->price == 0.32 && $pack->sort_order == 140)
                                            <h3 class="text-base font-bold text-gray-900">Standard Purchase Rebate Pack</h3>
                                        @elseif($pack->price == 0.32 && $pack->sort_order == 160)
                                            <h3 class="text-base font-bold text-gray-900">Honor Point Value Pack</h3>
                                        @elseif($pack->price == 1.18)
                                            <h3 class="text-base font-bold text-gray-900">Premium Purchase Rebate Pack</h3>
                                        @else
                                            <h3 class="text-base font-bold text-gray-900">Special Pack</h3>
                                        @endif
                                    @elseif($pack->diamonds == 1 && $pack->price == 0.96)
                                        <h3 class="text-base font-bold text-gray-900">Weekly Card</h3>
                                    @elseif($pack->diamonds == 2 && $pack->price == 2.99)
                                        <h3 class="text-base font-bold text-gray-900">Weekly Card Plus</h3>
                                    @else
                                        <h3 class="text-base font-bold text-gray-900">{{ number_format($pack->diamonds) }}</h3>
                                        <span class="text-sm font-medium text-gray-600">Tokens</span>
                                    @endif
                                @else
                                    <h3 class="text-base font-bold text-gray-900">{{ number_format($pack->diamonds) }}</h3>
                                    <span class="text-sm font-medium text-gray-600">{{ ($gameType ?? 'mobilelegends') === 'pubgmobile' ? 'UC' : (($gameType ?? 'mobilelegends') === 'honorofkings' ? 'Tokens' : (($gameType ?? 'mobilelegends') === 'bloodstrike' ? 'Golds' : 'Diamonds')) }}</span>
                                @endif
                            </div>
                            @if($pack->discount_percentage > 0)
                                <span class="text-xs font-bold text-white bg-gradient-to-r from-purple-600 to-pink-600 px-2.5 py-1 rounded-full shadow-sm">{{ $pack->discount_percentage }}% OFF</span>
                            @endif
                        </div>
                        @if($pack->bonus_diamonds > 0)
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                                </svg>
                                <p class="text-sm text-gray-600 font-medium">+ {{ number_format($pack->bonus_diamonds) }} Bonus</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            @if($pack->discount_percentage > 0)
                                <span class="text-xs text-gray-400 line-through font-medium">US$ {{ number_format($pack->price, 2) }}</span>
                            @else
                                <span class="text-xs text-gray-500">Best Value</span>
                            @endif
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-bold text-purple-600">US$ {{ number_format($priceAfterDiscount, 2) }}</span>
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
            </button>
        @endforeach
    </div>
</div>

<!-- Bottom Sheet Overlay -->
<div id="bottom-sheet-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" style="display: none;"></div>

