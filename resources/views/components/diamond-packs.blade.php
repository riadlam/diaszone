<div class="space-y-4" id="diamond-packs-wrapper">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 hidden lg:block">{{ $gameTitle ?? __('game.diamond_packs') }}</h2>
    
    <!-- Desktop: Grid Layout (hidden on mobile) -->
    <div class="hidden lg:block" id="desktop-grid-wrapper">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($packs as $index => $pack)
            @php
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
            <div class="diamond-pack-item-wrapper">
                  <input type="checkbox" 
                       name="diamond_pack[]" 
                       value="{{ $pack->id }}" 
                       class="hidden pack-checkbox"
                       data-pack-id="{{ $pack->id }}"
                       data-pack-quantity="{{ ($pack->special_quantity > 0) ? $pack->special_quantity : 1 }}"
                       data-pack-diamonds="{{ $pack->diamonds }}"
                       data-pack-bonus="{{ $pack->bonus_diamonds }}"
                       data-pack-price="{{ $pack->price }}"
                       data-pack-price-usd="{{ $pack->price_usd ?? $pack->price }}"
                       data-pack-price-dzd="{{ $pack->price_dzd }}"
                       data-pack-name="{{ $pack->name }}"
                       data-pack-membership-name="{{ $pack->membership_name }}"
                       data-pack-discount="{{ $pack->discount_percentage }}"
                       data-pack-currency="{{ $currencyName }}"
                       id="pack-checkbox-{{ $pack->id }}">
                
                <div class="SKU_type bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-purple-500 transition-all relative" data-pack-wrapper="{{ $pack->id }}">
                    <!-- Quantity Selector (visible when checked) -->
                    <div class="pack-quantity-control absolute top-2 right-2 hidden flex items-center gap-2 bg-purple-50 rounded-lg px-2 py-1 border border-purple-200 z-10">
                        <button type="button" class="quantity-decrease w-6 h-6 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-sm border border-purple-200 transition-colors" data-pack-id="{{ $pack->id }}" title="Decrease quantity">−</button>
                        <input type="number" 
                               class="quantity-input w-10 h-6 text-center text-xs font-semibold bg-transparent border-0 p-0 focus:outline-none" 
                               value="1" 
                               min="1" 
                               max="20" 
                               data-pack-id="{{ $pack->id }}"
                               readonly>
                        <button type="button" class="quantity-increase w-6 h-6 flex items-center justify-center rounded bg-white hover:bg-purple-100 text-purple-600 font-semibold text-sm border border-purple-200 transition-colors" data-pack-id="{{ $pack->id }}" title="Increase quantity">+</button>
                    </div>
                    
                    <label for="pack-checkbox-{{ $pack->id }}" class="cursor-pointer block">
                    <div class="flex items-start gap-4">
                        <!-- Image: Show diamond pack images for Mobile Legends, game thumbnail for other games (except Free Fire) -->
                        @if(($gameType ?? 'mobilelegends') === 'mobilelegends')
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg">
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
                                     class="w-full h-full object-contain"
                                     style="display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important;">
                            </div>
                        @elseif(($gameType ?? '') !== 'freefire' && !empty($gameImage ?? null))
                            <!-- Show game image thumbnail for games other than Mobile Legends and Free Fire -->
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-50 rounded-lg overflow-hidden">
                                <img src="{{ asset($gameImage) }}" 
                                     alt="{{ $gameTitle ?? '' }}" 
                                     class="w-full h-full object-cover rounded-lg"
                                     style="display: block !important; width: 100% !important; height: 100% !important; object-fit: cover !important;">
                            </div>
                        @else
                            <!-- Free Fire and games without images: Empty space to maintain layout -->
                            <div class="flex-shrink-0 w-12 h-12"></div>
                        @endif
                        
                        <!-- Pack Info -->
                        @php $packQuantity = ($pack->special_quantity > 0) ? $pack->special_quantity : 1; @endphp
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    @if(($gameType ?? 'mobilelegends') === 'honorofkings')
                                        @if($pack->diamonds == 0)
                                            @if($pack->price == 0.32 && $pack->sort_order == 130)
                                                {{ __('game.double_token_lucky_bag') }}
                                            @elseif($pack->price == 0.32 && $pack->sort_order == 140)
                                                {{ __('game.standard_purchase_rebate_pack') }}
                                            @elseif($pack->price == 0.32 && $pack->sort_order == 160)
                                                {{ __('game.honor_point_value_pack') }}
                                            @elseif($pack->price == 1.18)
                                                {{ __('game.premium_purchase_rebate_pack') }}
                                            @else
                                                {{ __('game.special_pack') }}
                                            @endif
                                        @elseif($pack->diamonds == 1 && $pack->price == 0.96)
                                            {{ __('game.weekly_card') }}
                                        @elseif($pack->diamonds == 2 && $pack->price == 2.99)
                                            {{ __('game.weekly_card_plus') }}
                                        @else
                                            {{ $pack->diamonds }} {{ $currencyName }}
                                        @endif
                                    @else
                                        @if(stripos($pack->name, 'Weekly Diamond Pass') !== false || stripos($pack->name, 'Event Topup') !== false)
                                            @if($packQuantity > 1 && stripos($pack->name ?? '', 'weekly') !== false)
                                                {{ $packQuantity }}x {{ __('game.weekly_diamond_pass') }}
                                            @else
                                                {{ __('game.weekly_diamond_pass') }}
                                            @endif
                                        @elseif(stripos($pack->name, 'Twilight Pass') !== false)
                                            {{ __('game.twilight_pass') }}
                                        @else
                                            @if($pack->diamonds == 0 && $pack->membership_name)
                                                {{ $pack->membership_name }}
                                            @else
                                                {{ $pack->diamonds }} {{ $currencyName }}
                                            @endif
                                        @endif
                                    @endif
                                </h3>
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded">{{ $pack->discount_percentage }}% {{ __('game.off') }}</span>
                                @endif
                            </div>
                            @if($pack->bonus_diamonds > 0)
                                <p class="text-xs text-gray-600 mb-2">+ {{ $pack->bonus_diamonds }} {{ __('game.bonus') }} {{ $currencyName }}</p>
                            @endif
                            <div class="flex items-center justify-between">
                                @php
                                    $priceUsd = $pack->price_usd ?? $pack->price;
                                    $priceDzd = $pack->price_dzd ?? $pack->price; // Fallback to price if price_dzd is null
                                    $discount = $pack->discount_percentage ?? 0;
                                    $finalPriceUsd = $priceUsd * (1 - $discount / 100);
                                    $finalPriceDzd = $priceDzd ? ($priceDzd * (1 - $discount / 100)) : 0;
                                @endphp
                                @if($pack->discount_percentage > 0)
                                    <span class="text-xs text-gray-400 line-through pack-original-price" data-price-usd="{{ $priceUsd }}" data-price-dzd="{{ $priceDzd ?? 0 }}" data-pack-quantity="{{ $packQuantity }}">{{ $priceDzd ? number_format($priceDzd * $packQuantity, 0) : '0' }} DZD</span>
                                @endif
                                <span class="text-sm font-bold text-purple-600 pack-final-price" data-price-usd="{{ $priceUsd }}" data-price-dzd="{{ $priceDzd ?? 0 }}" data-discount="{{ $discount }}" data-pack-quantity="{{ $packQuantity }}">{{ number_format($finalPriceDzd * ($packQuantity), 0) }} DZD</span>
                        </div>
                    </div>
                </div>
            </label>
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>

<script>
// Translation variables for JavaScript
window.GameTranslations = {
    weeklyDiamondPass: '{{ __('game.weekly_diamond_pass') }}',
    twilightPass: '{{ __('game.twilight_pass') }}',
    bonus: '{{ __('game.bonus') }}',
    diamonds: '{{ __('game.diamonds') }}',
    tokens: '{{ __('game.tokens') }}',
    golds: '{{ __('game.golds') }}',
    uc: '{{ __('game.uc') }}',
    diaszoneCredit: '{{ __('game.diaszone_credit') }}',
};

// Currency price update function
window.updatePricesOnPage = function() {
    const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
    
    // Update all pack prices
    document.querySelectorAll('.pack-final-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const discount = parseFloat(element.getAttribute('data-discount')) || 0;
        const quantity = parseInt(element.getAttribute('data-pack-quantity') || 1, 10);
        
        let price = (currency === 'DZD' ? priceDzd : priceUsd) * quantity;
        if (discount > 0) {
            const discountAmount = (price * discount) / 100;
            price = price - discountAmount;
        }
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    // Update original prices (strikethrough)
    document.querySelectorAll('.pack-original-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const quantity = parseInt(element.getAttribute('data-pack-quantity') || 1, 10);
        
        const price = (currency === 'DZD' ? priceDzd : priceUsd) * quantity;
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    // Update mobile selected pack text (mobile-selected-pack-text)
    const mobileSelectedPackText = document.getElementById('mobile-selected-pack-text');
    if (mobileSelectedPackText) {
            const packCard = document.querySelector('input[name="diamond_pack"]:checked, input.pack-checkbox:checked, input.mobile-pack-checkbox:checked');
        if (packCard) {
            const packPriceUsd = parseFloat(packCard.getAttribute('data-pack-price-usd')) || 0;
            const packPriceDzd = parseFloat(packCard.getAttribute('data-pack-price-dzd')) || 0;
            const packQuantity = parseInt(packCard.getAttribute('data-pack-quantity') || 1, 10);
            const packDiscount = parseFloat(packCard.getAttribute('data-pack-discount')) || 0;
            const packDiamonds = packCard.getAttribute('data-pack-diamonds');
            const packBonus = packCard.getAttribute('data-pack-bonus');
                const packNameData = packCard.getAttribute('data-pack-name') || '';
                const packCurrency = packCard.getAttribute('data-pack-currency') || 'Diamonds';
            
            let price = currency === 'DZD' ? packPriceDzd : packPriceUsd;
            if (packDiscount > 0) {
                const discountAmount = (price * packDiscount) / 100;
                price = price - discountAmount;
            }
            // Multiply by quantity (e.g., 3x weekly pass)
            price = price * packQuantity;
            
            let packDisplayName = '';
            const weeklyPassText = '{{ __('game.weekly_diamond_pass') }}';
            const twilightPassText = '{{ __('game.twilight_pass') }}';
            const bonusText = '{{ __('game.bonus') }}';
            
            if (packNameData && (packNameData.includes('Weekly Diamond Pass') || packNameData.includes('Event Topup'))) {
                packDisplayName = packQuantity > 1 ? `${packQuantity}x ${weeklyPassText}` : weeklyPassText;
            } else if (packNameData && packNameData.includes('Twilight Pass')) {
                packDisplayName = twilightPassText;
            } else {
                // Check if diamonds is 0 and membership_name exists
                const packMembershipName = packCard.getAttribute('data-pack-membership-name');
                if (parseInt(packDiamonds) === 0 && packMembershipName) {
                    packDisplayName = packMembershipName;
                } else {
                    // Use currency from data attribute (already extracted above)
                    const bonusTextFinal = parseInt(packBonus) > 0 ? ` + ${parseInt(packBonus).toLocaleString()} ${bonusText} ${packCurrency}` : '';
                    packDisplayName = `${parseInt(packDiamonds).toLocaleString()} ${packCurrency}${bonusTextFinal}`;
                }
            }
            
            const priceText = currency === 'DZD' 
                ? `${Math.round(price).toLocaleString()} DZD`
                : `$${parseFloat(price).toFixed(2)} USD`;
            
            mobileSelectedPackText.innerHTML = `
                <span class="block text-sm font-semibold">${packDisplayName}</span>
                <span class="text-xs text-white/90 font-medium">${priceText}</span>
            `;
        }
    }
    
    // Update mobile bottom sheet prices
    document.querySelectorAll('.mobile-pack-original-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const quantity = parseInt(element.getAttribute('data-pack-quantity') || 1, 10);
        
        const price = (currency === 'DZD' ? priceDzd : priceUsd) * quantity;
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
    
    document.querySelectorAll('.mobile-pack-final-price').forEach(element => {
        const priceUsd = parseFloat(element.getAttribute('data-price-usd')) || 0;
        const priceDzd = parseFloat(element.getAttribute('data-price-dzd')) || 0;
        const discount = parseFloat(element.getAttribute('data-discount')) || 0;
        
            let price = currency === 'DZD' ? priceDzd : priceUsd;
            // Multiply displayed price by pack quantity if available
            const quantity = parseInt(element.getAttribute('data-pack-quantity') || 1, 10);
            price = price * quantity;
        if (discount > 0) {
            const discountAmount = (price * discount) / 100;
            price = price - discountAmount;
        }
        
        if (currency === 'DZD') {
            element.textContent = Math.round(price).toLocaleString() + ' DZD';
        } else {
            element.textContent = '$' + parseFloat(price).toFixed(2) + ' USD';
        }
    });
};

// Listen for currency changes
window.addEventListener('currencyChanged', function(e) {
    if (typeof updatePricesOnPage === 'function') {
        updatePricesOnPage();
    }
});

// Update prices on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof updatePricesOnPage === 'function') {
        updatePricesOnPage();
    }
});

// Ensure desktop grid visibility (runs on all screens)
(function() {
    'use strict';
    
    function ensureDesktopGrid() {
        const desktopGrid = document.getElementById('desktop-grid-wrapper');
        if (desktopGrid) {
            // Let Tailwind classes handle it - just ensure no inline display style interferes
            if (window.innerWidth >= 1024) {
                desktopGrid.style.display = '';
            } else {
                desktopGrid.style.display = 'none';
            }
        }
    }
    
    window.addEventListener('load', ensureDesktopGrid);
    window.addEventListener('resize', ensureDesktopGrid);
    // Run immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureDesktopGrid);
    } else {
        ensureDesktopGrid();
    }
})();

// Mobile-only bottom sheet logic - completely separate from desktop
(function() {
    'use strict';
    
    // Check if we're on mobile (only run if mobile elements exist and screen is mobile)
    function isMobile() {
        const mobileBtn = document.getElementById('mobile-select-pack-btn');
        if (!mobileBtn) return false;
        // Check if mobile button is visible (not hidden by lg:hidden)
        const styles = window.getComputedStyle(mobileBtn);
        return styles.display !== 'none';
    }
    
    // Wait for DOM to be ready
    function initBottomSheet() {
        // Double check we're still on mobile
        if (!isMobile()) {
            return;
        }
        
        const selectPackBtn = document.getElementById('mobile-select-pack-btn');
        const closeBottomSheetBtn = document.getElementById('close-bottom-sheet-btn');
        const bottomSheet = document.getElementById('mobile-pack-bottom-sheet');
        const bottomSheetOverlay = document.getElementById('bottom-sheet-overlay');
        const mobilePackItems = document.querySelectorAll('.mobile-pack-item');
        const selectedPackText = document.getElementById('mobile-selected-pack-text');
        
        if (!selectPackBtn || !bottomSheet) {
            console.log('Bottom sheet elements not found, retrying...', {
                selectPackBtn: !!selectPackBtn,
                bottomSheet: !!bottomSheet
            });
            // Try again after a short delay in case elements load late
            setTimeout(initBottomSheet, 100);
            return;
        }
        
        console.log('Bottom sheet initialized successfully');
        
        // Ensure bottom sheet starts closed
        if (bottomSheet) {
            bottomSheet.style.transform = 'translateY(100%)';
            bottomSheet.style.display = 'none'; // Keep it hidden until button is clicked
            bottomSheet.style.zIndex = '9999';
            bottomSheet.style.backgroundColor = '#ffffff';
            bottomSheet.style.opacity = '1';
            bottomSheet.style.visibility = 'visible';
        }
        
        // Ensure scrollable content can scroll
        const scrollableContent = bottomSheet ? bottomSheet.querySelector('.overflow-y-auto') : null;
        if (scrollableContent) {
            // Prevent event propagation that might block scrolling
            scrollableContent.addEventListener('touchmove', function(e) {
                // Allow scrolling within the content area
                e.stopPropagation();
            }, { passive: true });
            
            scrollableContent.addEventListener('wheel', function(e) {
                // Allow wheel scrolling
                e.stopPropagation();
            }, { passive: true });
        }
        if (bottomSheetOverlay) {
            bottomSheetOverlay.style.display = 'none';
            bottomSheetOverlay.classList.add('hidden');
        }
        
        // Open bottom sheet
        function openBottomSheet() {
            console.log('Opening bottom sheet...', {
                bottomSheet: !!bottomSheet,
                bottomSheetOverlay: !!bottomSheetOverlay,
                bottomSheetElement: bottomSheet
            });
            
            if (!bottomSheet) {
                console.error('Bottom sheet element not found!');
                return;
            }
            
            // Show overlay first
            if (bottomSheetOverlay) {
                bottomSheetOverlay.style.display = 'block';
                bottomSheetOverlay.style.zIndex = '9998';
            }
            
            // Show floating close button
            const floatingCloseBtn = document.getElementById('mobile-bottom-sheet-floating-close');
            if (floatingCloseBtn) {
                floatingCloseBtn.style.display = 'flex';
                floatingCloseBtn.style.zIndex = '10000';
                bottomSheetOverlay.classList.remove('hidden');
            }
            
            // Force display to flex and remove any hidden classes
            bottomSheet.style.display = 'flex';
            bottomSheet.style.zIndex = '9999';
            bottomSheet.style.backgroundColor = '#ffffff';
            bottomSheet.style.opacity = '1';
            bottomSheet.style.visibility = 'visible';
            bottomSheet.style.pointerEvents = 'auto';
            bottomSheet.style.position = 'fixed';
            bottomSheet.style.width = '100%';
            bottomSheet.style.left = '0';
            bottomSheet.style.right = '0';
            bottomSheet.style.bottom = '0';
            // Remove any classes that might hide it
            bottomSheet.classList.remove('hidden', 'lg:hidden');
            
            // Use requestAnimationFrame to ensure the display change is applied before transform
            requestAnimationFrame(() => {
                // Force visibility with multiple methods BEFORE transform
                bottomSheet.style.display = 'flex';
                bottomSheet.style.visibility = 'visible';
                bottomSheet.style.opacity = '1';
                bottomSheet.style.pointerEvents = 'auto';
                
                // Small delay to ensure display is applied
                setTimeout(() => {
                    // Force transform to 0 using !important via setProperty
                    bottomSheet.style.setProperty('transform', 'translateY(0)', 'important');
                    bottomSheet.style.setProperty('-webkit-transform', 'translateY(0)', 'important');
                    
                    // Also remove the translate-y-full class if it exists
                    bottomSheet.classList.remove('translate-y-full');
                    
                    // Check computed styles
                    const computedStyle = window.getComputedStyle(bottomSheet);
                    const rect = bottomSheet.getBoundingClientRect();
                    console.log('Bottom sheet opened', {
                        display: bottomSheet.style.display,
                        computedDisplay: computedStyle.display,
                        transform: bottomSheet.style.transform,
                        computedTransform: computedStyle.transform,
                        zIndex: bottomSheet.style.zIndex,
                        computedZIndex: computedStyle.zIndex,
                        backgroundColor: bottomSheet.style.backgroundColor,
                        computedBackgroundColor: computedStyle.backgroundColor,
                        visibility: bottomSheet.style.visibility,
                        computedVisibility: computedStyle.visibility,
                        opacity: bottomSheet.style.opacity,
                        computedOpacity: computedStyle.opacity,
                        height: computedStyle.height,
                        width: computedStyle.width,
                        top: computedStyle.top,
                        bottom: computedStyle.bottom,
                        left: computedStyle.left,
                        right: computedStyle.right,
                        position: computedStyle.position,
                        rect: rect,
                        viewportHeight: window.innerHeight,
                        isVisible: rect.top < window.innerHeight && rect.bottom > 0
                    });
                    
                    // If still not visible or transform is wrong, force correct position
                    if (rect.top >= window.innerHeight || rect.bottom <= 0 || computedStyle.transform.includes('737')) {
                        console.warn('Bottom sheet transform issue detected, forcing correct position...', {
                            computedTransform: computedStyle.transform,
                            rectTop: rect.top,
                            viewportHeight: window.innerHeight
                        });
                        // Force the transform to be exactly 0px
                        bottomSheet.style.setProperty('transform', 'translateY(0px)', 'important');
                        bottomSheet.style.setProperty('-webkit-transform', 'translateY(0px)', 'important');
                        // Also ensure bottom is 0
                        bottomSheet.style.setProperty('bottom', '0', 'important');
                        bottomSheet.style.setProperty('top', 'auto', 'important');
                        // Force a reflow
                        void bottomSheet.offsetHeight;
                        // Check again
                        const newRect = bottomSheet.getBoundingClientRect();
                        const newComputed = window.getComputedStyle(bottomSheet);
                        console.log('After fix attempt', {
                            newTransform: newComputed.transform,
                            newRect: newRect,
                            isNowVisible: newRect.top < window.innerHeight && newRect.bottom > 0
                        });
                    }
                    
                    // Check if content is visible
                    const content = bottomSheet.querySelector('.overflow-y-auto');
                    if (content) {
                        console.log('Bottom sheet content found', {
                            contentHeight: content.offsetHeight,
                            contentScrollHeight: content.scrollHeight,
                            contentDisplay: window.getComputedStyle(content).display,
                            packItems: document.querySelectorAll('.mobile-pack-item').length,
                            contentRect: content.getBoundingClientRect()
                        });
                    } else {
                        console.error('Bottom sheet content not found!');
                    }
                }, 10);
            });
            
            // Prevent body scroll (but allow bottom sheet to scroll)
            const scrollY = window.scrollY;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
            document.body.style.top = `-${scrollY}px`;
            
            // Ensure scrollable area can scroll
            const scrollableContent = bottomSheet.querySelector('.overflow-y-auto');
            if (scrollableContent) {
                scrollableContent.style.overflowY = 'auto';
                scrollableContent.style.webkitOverflowScrolling = 'touch';
            } else {
                console.warn('Scrollable content not found in bottom sheet');
            }
        }
        
        // Close bottom sheet
        function closeBottomSheet() {
            if (bottomSheet) {
                bottomSheet.style.transform = 'translateY(100%)';
                // Hide after transition completes
                setTimeout(() => {
                    if (bottomSheet.style.transform === 'translateY(100%)' || bottomSheet.style.transform.includes('100%')) {
                        bottomSheet.style.display = 'none';
                    }
                }, 300); // Match transition duration
            }
            if (bottomSheetOverlay) {
                bottomSheetOverlay.style.display = 'none';
                bottomSheetOverlay.classList.add('hidden');
            }
            // Hide floating close button
            const floatingCloseBtn = document.getElementById('mobile-bottom-sheet-floating-close');
            if (floatingCloseBtn) {
                floatingCloseBtn.style.display = 'none';
            }
            // Restore body scroll
            const scrollY = document.body.style.top;
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
            document.body.style.top = '';
            if (scrollY) {
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
        }
        
        // Event listeners
        if (selectPackBtn) {
            selectPackBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Select Pack button clicked');
                if (typeof openBottomSheet === 'function') {
                openBottomSheet();
                } else {
                    // Fallback if function not defined yet
                    if (bottomSheet) {
                        bottomSheet.style.display = 'flex';
                        bottomSheetOverlay.style.display = 'block';
                        const floatingCloseBtn = document.getElementById('mobile-bottom-sheet-floating-close');
                        if (floatingCloseBtn) {
                            floatingCloseBtn.style.display = 'flex';
                        }
                        requestAnimationFrame(() => {
                            bottomSheet.style.setProperty('transform', 'translateY(0)', 'important');
                        });
                        document.body.style.overflow = 'hidden';
                    }
                }
            });
        } else {
            console.error('Select Pack button not found!');
        }
        
        if (closeBottomSheetBtn) {
            closeBottomSheetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeBottomSheet();
            });
        }
        
        // Floating close button handler
        const floatingCloseBtn = document.getElementById('mobile-bottom-sheet-floating-close');
        if (floatingCloseBtn) {
            // Remove inline onclick handler and use event listener
            floatingCloseBtn.removeAttribute('onclick');
            floatingCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof closeBottomSheet === 'function') {
                    closeBottomSheet();
                }
            });
        }
        
        if (bottomSheetOverlay) {
            bottomSheetOverlay.addEventListener('click', function(e) {
                // Only close if clicking directly on overlay, not if event came from bottom sheet
                if (e.target === bottomSheetOverlay) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeBottomSheet();
                }
            });
            
            // Prevent overlay from blocking touch events on bottom sheet
            bottomSheetOverlay.addEventListener('touchmove', function(e) {
                // If touch is on bottom sheet, don't prevent default
                const touch = e.touches[0];
                if (touch && bottomSheet) {
                    const rect = bottomSheet.getBoundingClientRect();
                    if (touch.clientY >= rect.top) {
                        e.stopPropagation();
                    }
                }
            }, { passive: true });
        }
        
        // OLD CODE REMOVED: Mobile pack items now use checkboxes for multi-select
        // The checkbox change handlers handle selection, and the sheet stays open for multiple selections
        // Users can close it manually via the close button or overlay
    }
    
    // Initialize when DOM is ready - try multiple times to catch late-loading elements
    function startInit() {
        if (isMobile()) {
            initBottomSheet();
        } else {
            // If not mobile yet, try again after a delay
            setTimeout(startInit, 200);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInit);
    } else {
        startInit();
    }
    
    // Also try after delays in case elements load late (button is in different file)
    setTimeout(startInit, 200);
    setTimeout(startInit, 500);
    setTimeout(startInit, 1000);
})();

// Multi-offer selection: Checkbox and Quantity Management
(function() {
    'use strict';
    
    function initMultiOfferSelection() {
        // Wait for CartManager to be available
        if (typeof window.CartManager === 'undefined') {
            setTimeout(initMultiOfferSelection, 100);
            return;
        }
        
        // Desktop: Handle checkbox changes
        const desktopCheckboxes = document.querySelectorAll('.pack-checkbox');
        desktopCheckboxes.forEach(checkbox => {
            // Load existing cart state
            const cart = window.CartManager.getCart();
            const packId = parseInt(checkbox.dataset.packId);
            const cartItem = cart.find(item => item.pack_id == packId);
            
            if (cartItem) {
                checkbox.checked = true;
                // Show quantity control
                const wrapper = checkbox.closest('.diamond-pack-item-wrapper');
                if (wrapper) {
                    const quantityControl = wrapper.querySelector('.pack-quantity-control');
                    const quantityInput = wrapper.querySelector('.quantity-input');
                    if (quantityControl) quantityControl.classList.remove('hidden');
                    if (quantityInput) quantityInput.value = cartItem.quantity || 1;
                }
            }
            
            checkbox.addEventListener('change', function() {
                const packId = parseInt(this.dataset.packId);
                const wrapper = this.closest('.diamond-pack-item-wrapper');
                const quantityControl = wrapper?.querySelector('.pack-quantity-control');
                const quantityInput = wrapper?.querySelector('.quantity-input');
                const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
                
                if (this.checked) {
                    // Add to cart
                    if (quantityControl) quantityControl.classList.remove('hidden');
                    
                    const packData = {
                        pack_id: packId,
                        // Game-specific IDs will be set from order form
                    };
                    
                    window.CartManager.addToCart(packData, quantity);
                    
                    // Update border to show selected
                    const packCard = wrapper?.querySelector('.SKU_type');
                    if (packCard) {
                        packCard.classList.remove('border-gray-200');
                        packCard.classList.add('border-purple-500', 'bg-purple-50/30');
                    }
                    
                    // Update order form summary
                    if (typeof updateOrderForm === 'function') {
                        updateOrderForm();
                    }
                } else {
                    // Remove from cart
                    const cart = window.CartManager.getCart();
                    const cartItem = cart.find(item => item.pack_id == packId);
                    if (cartItem) {
                        window.CartManager.removeFromCart(cartItem.id);
                    }
                    
                    if (quantityControl) quantityControl.classList.add('hidden');
                    
                    // Update border to show unselected
                    const packCard = wrapper?.querySelector('.SKU_type');
                    if (packCard) {
                        packCard.classList.remove('border-purple-500', 'bg-purple-50/30');
                        packCard.classList.add('border-gray-200');
                    }
                    
                    // Update order form summary
                    if (typeof updateOrderForm === 'function') {
                        updateOrderForm();
                    }
                }
            });
        });
        
        // Desktop: Handle quantity +/- buttons
        document.querySelectorAll('.quantity-increase, .quantity-decrease').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const packId = parseInt(this.dataset.packId);
                const wrapper = document.querySelector(`[data-pack-wrapper="${packId}"]`);
                const quantityInput = wrapper?.querySelector('.quantity-input');
                const checkbox = document.querySelector(`#pack-checkbox-${packId}`);
                
                if (!quantityInput || !checkbox || !checkbox.checked) return;
                
                let currentQty = parseInt(quantityInput.value) || 1;
                if (this.classList.contains('quantity-increase')) {
                    currentQty = Math.min(20, currentQty + 1);
                } else {
                    currentQty = Math.max(1, currentQty - 1);
                }
                
                quantityInput.value = currentQty;
                
                // Update cart
                const cart = window.CartManager.getCart();
                const cartItem = cart.find(item => item.pack_id == packId);
                if (cartItem) {
                    window.CartManager.updateQuantity(cartItem.id, currentQty);
                }
                
                // Update order form summary
                if (typeof updateOrderForm === 'function') {
                    updateOrderForm();
                }
            });
        });
        
        // Mobile: Handle checkbox changes
        const mobileCheckboxes = document.querySelectorAll('.mobile-pack-checkbox');
        mobileCheckboxes.forEach(checkbox => {
            // Load existing cart state
            const cart = window.CartManager.getCart();
            const packId = parseInt(checkbox.dataset.packId);
            const cartItem = cart.find(item => item.pack_id == packId);
            
            if (cartItem) {
                checkbox.checked = true;
                // Show quantity control and selection indicator
                const wrapper = checkbox.closest('.mobile-pack-item-wrapper');
                if (wrapper) {
                    const quantityControl = wrapper.querySelector('.mobile-pack-quantity-control');
                    const quantityInput = wrapper.querySelector('.mobile-quantity-input');
                    const packItem = wrapper.querySelector('.mobile-pack-item');
                    const indicator = packItem?.querySelector('.mobile-pack-indicator');
                    const checkIcon = packItem?.querySelector('.mobile-pack-indicator svg');
                    
                    if (quantityControl) quantityControl.classList.remove('hidden');
                    if (quantityInput) quantityInput.value = cartItem.quantity || 1;
                    if (packItem) {
                        packItem.classList.remove('border-gray-200');
                        packItem.classList.add('border-purple-500', 'bg-purple-50/50');
                    }
                if (indicator) {
                    indicator.classList.remove('border-gray-300');
                    indicator.classList.add('bg-purple-600', 'border-purple-600');
                }
                    if (checkIcon) checkIcon.classList.remove('hidden');
                }
            }
            
            checkbox.addEventListener('change', function() {
                const packId = parseInt(this.dataset.packId);
                const wrapper = this.closest('.mobile-pack-item-wrapper');
                const quantityControl = wrapper?.querySelector('.mobile-pack-quantity-control');
                const quantityInput = wrapper?.querySelector('.mobile-quantity-input');
                const packItem = wrapper?.querySelector('.mobile-pack-item');
                const indicator = packItem?.querySelector('.mobile-pack-indicator');
                const checkIcon = packItem?.querySelector('.mobile-pack-indicator svg');
                const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
                
                if (this.checked) {
                    // Add to cart
                    if (quantityControl) quantityControl.classList.remove('hidden');
                    if (packItem) {
                        packItem.classList.remove('border-gray-200');
                        packItem.classList.add('border-purple-500', 'bg-purple-50/50');
                    }
                    if (indicator) {
                        indicator.classList.remove('border-gray-300');
                        indicator.classList.add('bg-purple-600', 'border-purple-600');
                    }
                    if (checkIcon) checkIcon.classList.remove('hidden');
                    
                    const packData = {
                        pack_id: packId,
                    };
                    
                    window.CartManager.addToCart(packData, quantity);
                    
                    // Update order form summary
                    if (typeof updateOrderForm === 'function') {
                        updateOrderForm();
                    }
                    if (typeof updateMobileButtonText === 'function') {
                        updateMobileButtonText();
                    }
                } else {
                    // Remove from cart
                    const cart = window.CartManager.getCart();
                    const cartItem = cart.find(item => item.pack_id == packId);
                    if (cartItem) {
                        window.CartManager.removeFromCart(cartItem.id);
                    }
                    
                    if (quantityControl) quantityControl.classList.add('hidden');
                    if (packItem) {
                        packItem.classList.remove('border-purple-500', 'bg-purple-50/50');
                        packItem.classList.add('border-gray-200');
                    }
                    if (indicator) {
                        indicator.classList.remove('bg-purple-600', 'border-purple-600');
                        indicator.classList.add('border-gray-300');
                    }
                    if (checkIcon) checkIcon.classList.add('hidden');
                }
                
                // Update order form summary and mobile button text
                if (typeof updateOrderForm === 'function') {
                    updateOrderForm();
                }
                if (typeof updateMobileButtonText === 'function') {
                    updateMobileButtonText();
                }
            });
        });
        
        // Mobile: Handle quantity +/- buttons (fixed selector)
        function attachMobileQuantityHandlers() {
            document.querySelectorAll('.mobile-quantity-increase, .mobile-quantity-decrease').forEach(btn => {
                // Remove any existing listeners by cloning
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    
                    const packId = parseInt(this.dataset.packId);
                    if (!packId) return;
                    
                    // Find the checkbox and wrapper more reliably
                    const checkbox = document.querySelector(`#mobile-pack-checkbox-${packId}`);
                    if (!checkbox || !checkbox.checked) return;
                    
                    const wrapper = checkbox.closest('.mobile-pack-item-wrapper');
                    if (!wrapper) return;
                    
                    const quantityInput = wrapper.querySelector(`.mobile-quantity-input[data-pack-id="${packId}"]`);
                    if (!quantityInput) return;
                    
                    let currentQty = parseInt(quantityInput.value) || 1;
                    if (this.classList.contains('mobile-quantity-increase')) {
                        currentQty = Math.min(20, currentQty + 1);
                    } else {
                        currentQty = Math.max(1, currentQty - 1);
                    }
                    
                    quantityInput.value = currentQty;
                    
                    // Update cart
                    const cart = window.CartManager.getCart();
                    const cartItem = cart.find(item => item.pack_id == packId);
                    if (cartItem) {
                        window.CartManager.updateQuantity(cartItem.id, currentQty);
                    }
                    
                    // Update order form summary
                    if (typeof updateOrderForm === 'function') {
                        updateOrderForm();
                    }
                
                // Update mobile button text
                    if (typeof updateMobileButtonText === 'function') {
                        updateMobileButtonText();
                    }
                });
            });
        }
        
        // Attach handlers after a short delay to ensure DOM is ready
        setTimeout(attachMobileQuantityHandlers, 200);
        
        // Mobile bottom sheet: Update button text based on selected items
        function updateMobileButtonText() {
            const selectedPackText = document.getElementById('mobile-selected-pack-text');
            if (!selectedPackText) return;
            
            const cart = window.CartManager.getCart();
            const checkedBoxes = document.querySelectorAll('.mobile-pack-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                selectedPackText.innerHTML = '<span class="block text-sm font-semibold">{{ __('game.select_topup_amount') }}</span>';
                return;
            }
            
            // Show count if multiple items selected
            if (checkedBoxes.length > 1) {
                selectedPackText.innerHTML = `<span class="block text-sm font-semibold">${checkedBoxes.length} {{ __('game.packs_selected') }}</span>`;
            } else {
                // Show single pack info (similar to old logic but using checkbox data)
                const checkbox = checkedBoxes[0];
                const packPriceUsd = parseFloat(checkbox.dataset.packPriceUsd) || 0;
                const packPriceDzd = parseFloat(checkbox.dataset.packPriceDzd) || 0;
                const packDiscount = parseFloat(checkbox.dataset.packDiscount) || 0;
                const packQuantity = parseInt(checkbox.dataset.packQuantity) || 1;
                const packDiamonds = parseInt(checkbox.dataset.packDiamonds) || 0;
                const packBonus = parseInt(checkbox.dataset.packBonus) || 0;
                
                const currency = window.CurrencyManager ? window.CurrencyManager.getCurrency() : (localStorage.getItem('diaszone_currency') || 'DZD');
                let price = currency === 'DZD' ? parseFloat(packPriceDzd) : parseFloat(packPriceUsd);
                const discountAmount = (price * parseFloat(packDiscount)) / 100;
                let priceAfterDiscount = price - discountAmount;
                priceAfterDiscount = priceAfterDiscount * packQuantity;
                
                    let packDisplayName = '';
                const packNameData = checkbox.dataset.packName || '';
                const weeklyPassText = '{{ __('game.weekly_diamond_pass') }}';
                const twilightPassText = '{{ __('game.twilight_pass') }}';
                const bonusText = '{{ __('game.bonus') }}';
                
                if (packNameData && (packNameData.includes('Weekly Diamond Pass') || packNameData.includes('Event Topup'))) {
                    packDisplayName = packQuantity > 1 ? `${packQuantity}x ${weeklyPassText}` : weeklyPassText;
                } else if (packNameData && packNameData.includes('Twilight Pass')) {
                    packDisplayName = twilightPassText;
                    } else {
                        // Check if diamonds is 0 and membership_name exists
                        const packMembershipName = checkbox.dataset.packMembershipName;
                        if (parseInt(packDiamonds) === 0 && packMembershipName) {
                            packDisplayName = packMembershipName;
                        } else {
                            const gameType = '{{ $gameType ?? "mobilelegends" }}';
                            const currencyText = gameType === 'pubgmobile' ? '{{ __('game.uc') }}' : (gameType === 'honorofkings' ? '{{ __('game.tokens') }}' : (gameType === 'bloodstrike' ? '{{ __('game.golds') }}' : '{{ __('game.diamonds') }}'));
                            const bonusTextFinal = parseInt(packBonus) > 0 ? ` + ${parseInt(packBonus).toLocaleString()} ${bonusText}` : '';
                            packDisplayName = `${parseInt(packDiamonds).toLocaleString()} ${currencyText}${bonusTextFinal}`;
                        }
                    }
                    
                    const priceText = currency === 'DZD' 
                        ? `${Math.round(priceAfterDiscount).toLocaleString()} DZD`
                        : `$${priceAfterDiscount.toFixed(2)} USD`;
                    
                    selectedPackText.innerHTML = `
                        <span class="block text-sm font-semibold">${packDisplayName}</span>
                        <span class="text-xs text-white/90 font-medium">${priceText}</span>
                    `;
                }
        }
        
        // Update mobile button text when checkboxes change
        const mobileCheckboxesForButton = document.querySelectorAll('.mobile-pack-checkbox');
        mobileCheckboxesForButton.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateMobileButtonText();
                // Update CartManager when checkbox changes (handled by main handler, but ensure button text updates)
            });
        });
        
        // Quantity handlers are attached separately in attachMobileQuantityHandlers()
        
        updateMobileButtonText(); // Initial update
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMultiOfferSelection);
    } else {
        initMultiOfferSelection();
    }
})();
</script>

